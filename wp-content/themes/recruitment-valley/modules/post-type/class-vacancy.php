<?php

namespace PostType;

use Constant\Message;
use DateTimeImmutable;
use Vacancy\Vacancy as VacancyModel;
use constant\NotificationConstant;
use Global\NotificationService;
use Global\OptionController;

class Vacancy extends RegisterCPT
{
    private $_message;
    private $_notification;
    private $_notificationConstant;
    public $wpdb;

    public function __construct()
    {
        add_action('init', [$this, 'RegisterVacancyCPT']);
        add_action('set_object_terms', [$this, 'setExpiredDate'], 10, 5);
        add_action('add_meta_boxes', [$this, 'addVacancyMetaboxes'], 10, 2);
        add_filter('manage_vacancy_posts_columns', [$this, 'vacancyColoumn'], 10, 1);
        add_action('manage_vacancy_posts_custom_column', [$this, 'vacancyCustomColoumn'], 10, 2);
        $this->_message = new Message();

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->_notification = new NotificationService();
        $this->_notificationConstant = new NotificationConstant();
    }

    public function RegisterVacancyCPT()
    {
        $title = __('Vacancies', THEME_DOMAIN);
        $slug = 'vacancy';
        $args = [
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);

        $taxonomies = [
            [
                "name" => "sector",
                "arguments" => [
                    'label' => __("Sector", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "role",
                "arguments" => [
                    'label' => __("Role", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "type",
                "arguments" => [
                    'label' => __("Type", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "education",
                "arguments" => [
                    'label' => __("Education", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "working-hours",
                "arguments" => [
                    'label' => __("Working Hours", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "status",
                "arguments" => [
                    'label' => __("Status", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "location",
                "arguments" => [
                    'label' => __("Location", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "experiences",
                "arguments" => [
                    'label' => __("Working Experience", THEME_DOMAIN),
                ]
            ]
        ];

        foreach ($taxonomies as $key => $taxonomy) {
            $this->taxonomy($slug, $taxonomy["name"], $taxonomy["arguments"]);
        }
    }

    public function setExpiredDate($object_id, $terms = [], $tt_ids = [], $taxonomy = '', $append = true, $old_tt_ids = [])
    {
        $this->wpdb->query("START TRANSACTION");

        $post = get_post($object_id, 'object');
        if ($post->post_type == 'vacancy') {
            error_log('class-vacancy - method : setExpiredDate - Before try to update the expired date.');
            error_log('class-vacancy - method : setExpiredDate - post : ' . $object_id . ' - terms : ' . json_encode($terms));

            try {
                $openTerm = get_term_by('slug', 'open', 'status', 'OBJECT');
                $declineTerm = get_term_by('slug', 'declined', 'status', 'OBJECT');
                $vacancyModel = new VacancyModel($object_id);

                if ($taxonomy === 'status' && in_array($openTerm->term_id, $terms)) {
                    /** Do only if vacancy is free */
                    if (!get_field('is_paid', $object_id, true)) {
                        /** Only set expired date if vacancy is free and not imported vacancy */
                        if ($vacancyModel->checkImported()) {
                            /** Get imported expired Date */
                            $vacancyExpiredDate = $vacancyModel->getExpiredAt('Y-m-d H:i:s');

                            /** Update options "job_expires" */
                            $optionController       = new OptionController();
                            $updateOptionJobExpires = $optionController->updateExpiredOptions($object_id, $vacancyExpiredDate, 'class-vacancy.php', 'setExpiredDate');
                        } else {
                            /** Update the expired date */
                            $today = new DateTimeImmutable("now");
                            $vacancyExpiredDate = $today->modify("+30 days")->format("Y-m-d H:i:s");

                            $setExpired = $vacancyModel->setProp($vacancyModel->acf_expired_at, $vacancyExpiredDate);

                            /** If success update vacancy expired date */
                            if ($setExpired) {
                                /** Update options "job_expires" */
                                $optionController       = new OptionController();
                                $updateOptionJobExpires = $optionController->updateExpiredOptions($object_id, $vacancyExpiredDate, 'class-vacancy.php', 'setExpiredDate');
                            }
                        }

                        /** Set Approval status */
                        $vacancyModel->setApprovedStatus('admin-approved');
                        $vacancyModel->setApprovedBy(get_current_user_id());
                        $vacancyModel->setApprovedAt('now');

                        /** Create notification : vacancy is approved */
                        $this->_notification->write($this->_notificationConstant::VACANCY_PUBLISHED, $vacancyModel->getAuthor(), [
                            'id'    => $object_id,
                            'slug'  => $vacancyModel->getSlug(),
                            'title' => $vacancyModel->getTitle()
                        ]);
                    }
                }

                if ($taxonomy === 'status' && in_array($declineTerm->term_id, $terms)) {
                    /** Set Approval status */
                    $vacancyModel->setApprovedStatus('admin-approved');
                    $vacancyModel->setApprovedBy(get_current_user_id());
                    $vacancyModel->setApprovedAt('now');

                    /** Create notification : vacancy is approved */
                    $this->_notification->write($this->_notificationConstant::VACANCY_REJECTED, $vacancyModel->getAuthor(), [
                        'id'    => $object_id,
                        'slug'  => $vacancyModel->getSlug(),
                        'title' => $vacancyModel->getTitle()
                    ]);
                }

                $this->wpdb->query("COMMIT");
            } catch (\WP_Error $err) {
                $this->wpdb->query("ROLLBACK");
                error_log($err->get_error_message());
            } catch (\Exception $e) {
                $this->wpdb->query("ROLLBACK");
                error_log($e->getMessage());
            } catch (\Throwable $th) {
                $this->wpdb->query("ROLLBACK");
                error_log($th->getMessage());
            }
        }
    }

    public function addVacancyMetaboxes($post_type, $post)
    {
        add_meta_box(
            'vacancies_metaboxes',
            'Vacancies Approved At',
            [$this, 'vacancyApprovedAtRenderMetabox'],
            'vacancy',
            'advanced',
            'default',
            ['post_id' => $post->ID, 'meta' => []]
        );

        $vacancyModel = new VacancyModel($post->ID);
        if ($vacancyModel->getImportedAt()) {
            add_meta_box(
                'imported_vacancies_metaboxes',
                'Vacancies Imported At',
                [$this, 'vacancyImportedAtRenderMetabox'],
                'vacancy',
                'advanced',
                'default',
                ['post_id' => $post->ID, 'meta' => []]
            );
        }
    }

    public function vacancyApprovedAtRenderMetabox($post, $callback_arguments = [])
    {
        $vacancy = new VacancyModel($post->ID);
        echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        echo '<div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">';
        echo '<label style="display; block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">Vacancy Approved At</label>';
        echo '<input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" type="text" id="rss-url-endpoint" readonly disabled value="' . $vacancy->getApprovedAt('d F Y H:i:s') . '"/>';
        echo '</div>';
        echo '</div>';
    }

    public function vacancyImportedAtRenderMetabox($post, $callback_arguments = [])
    {
        $vacancy = new VacancyModel($post->ID);
        echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        echo '<div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">';
        echo '<label style="display; block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">Vacancy Imported At</label>';
        echo '<input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" type="text" id="rss-url-endpoint" readonly disabled value="' . $vacancy->getImportedAt('d F Y H:i:s') . '"/>';
        echo '</div>';
        echo '</div>';
    }

    public function vacancyColoumn($coloumn)
    {
        unset($coloumn['date']);
        unset($coloumn['author']);
        $coloumn['status']      = __('Status');
        $coloumn['expired']     = __('Expired Date');
        $coloumn['approvedat']  = __('Approval Date');
        $coloumn['author']      = __('Author');
        $coloumn['date']        = __('Submitted On');

        return $coloumn;
    }

    public function vacancyCustomColoumn($coloumn, $post_id)
    {
        $vacancyModel = new VacancyModel($post_id);

        switch ($coloumn) {
            case 'status':
                $status = $vacancyModel->getStatus();
                if ($status['slug'] == 'open') {
                    echo '<span style="color: green; font-weight: bold;">' . $status['name'] . '<span>';
                } else if ($status['slug'] == 'close') {
                    echo '<span style="color: red; font-weight: bold;">' . $status['name'] . '<span>';
                } else if ($status['slug'] == 'declined') {
                    echo '<span style="color: orange; font-weight: bold;">' . $status['name'] . '<span>';
                } else {
                    echo '<span style="color: black; font-weight: bold;">' . $status['name'] . '<span>';
                }
                break;
            case 'expired':
                if ($vacancyModel->checkImported()) {
                    if ($vacancyModel->getImportedSource() == 'jobfeed') {
                        echo '-';
                    } else {
                        echo $vacancyModel->getExpiredAt('d M Y');
                    }
                } else {
                    echo $vacancyModel->getExpiredAt('d M Y');
                }
                break;
            case 'approvedat':
                echo $vacancyModel->getApprovedAt('d M Y H:i:s');
                break;
        }
    }
}
new Vacancy();
