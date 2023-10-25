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

                        /** Create notification : vacancy is approved */
                        $this->_notification->write($this->_notificationConstant::VACANCY_PUBLISHED, $vacancyModel->getAuthor(), [
                            'id'    => $object_id,
                            'slug'  => $vacancyModel->getSlug(),
                            'title' => $vacancyModel->getTitle()
                        ]);
                    }
                }

                if ($taxonomy === 'status' && in_array($declineTerm->term_id, $terms)) {
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
}
new Vacancy();
