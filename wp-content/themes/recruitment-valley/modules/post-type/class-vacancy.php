<?php

namespace PostType;

use Constant\Message;
use DateTimeImmutable;
use Vacancy\Vacancy as VacancyModel;
use constant\NotificationConstant;
use DateTime;
use Global\NotificationService;
use Global\OptionController;
use Helper\Maphelper;
use Model\Term;
use WP_Error;

class Vacancy extends RegisterCPT
{
    private $_message;
    private $_notification;
    private $_notificationConstant;
    public $wpdb;

    public const vacancy_post_type_slug = 'vacancy';
    private const status_slug_open          = 'open';
    private const status_slug_processing    = 'processing';

    public function __construct()
    {
        add_action('init', [$this, 'RegisterVacancyCPT']);
        add_action('save_post', [$this, 'vacancySubmitHandle'], 10, 3);
        add_action('set_object_terms', [$this, 'setExpiredDate'], 10, 5);
        add_action('add_meta_boxes', [$this, 'addVacancyMetaboxes'], 10, 2);
        add_filter('manage_vacancy_posts_columns', [$this, 'vacancyColoumn'], 10, 1);
        add_action('manage_vacancy_posts_custom_column', [$this, 'vacancyCustomColoumn'], 10, 2);
        add_filter('manage_edit-vacancy_sortable_columns', [$this, 'vacancyCustomColoumnSortable'], 10, 1);
        add_action('restrict_manage_posts', [$this, 'vacancyCustomFilterRender'], 10, 2);
        add_filter('parse_query', [$this, 'vacancyCustomFilterHandle'], 10, 1);
        // add_action('pre_get_posts', [$this, 'vacancyCustomFilterHandle'], 10, 1);
        add_filter('post_row_actions', [$this, 'vacancyQuickAction'], 10, 2);
        add_action('edit_form_after_title', [$this, 'vacancyPreviewOnEdit'], 10, 1);

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->_message = new Message();
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
        $post = get_post($object_id, 'object');
        if ($post->post_type == 'vacancy') {
            $this->wpdb->query("START TRANSACTION");

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
                            if ($vacancyModel->getImportedSource() == 'flexfeed') {
                                /** Get imported expired Date */
                                $vacancyExpiredDate = $vacancyModel->getExpiredAt('Y-m-d H:i:s');

                                /** Update options "job_expires" */
                                $optionController       = new OptionController();
                                $updateOptionJobExpires = $optionController->updateExpiredOptions($object_id, $vacancyExpiredDate, 'class-vacancy.php', 'setExpiredDate');
                                error_log("[IMPORT][flexfeed][{$object_id}] Set expired date option : is updated : " . $updateOptionJobExpires . " - updated data : " . json_encode(get_option("job_expires")) . " - Logged By Vacancy::setExpiredDate");
                            }
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
                                error_log("[SUBMITED][{$object_id}] Set expired date option : is updated : " . $updateOptionJobExpires . " - updated data : " . json_encode(get_option('job_expires')) . " - Logged By Vacancy::setExpiredDate");
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
        echo '<label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">Vacancy Approved At</label>';
        echo '<input style="width: 100%; border: 1px solid rgba(209, 213, 219, 1); padding: 0.375rem 0.5rem; font-size: 1rem; line-height: 1.5rem; font-weight: 400;" type="text" id="rss-url-endpoint" readonly disabled value="' . $vacancy->getApprovedAt('d F Y H:i:s') . '"/>';
        echo '</div>';
        echo '</div>';
    }

    public function vacancyImportedAtRenderMetabox($post, $callback_arguments = [])
    {
        $vacancy = new VacancyModel($post->ID);
        echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';
        echo '<div class="cs-flex cs-flex-col cs-flex-nowrap cs-items-start cs-gap-2">';
        echo '<label style="display: block; font-weight: bold; color: rgba(0, 0, 0, 1);" for="rss-url-endpoint">Vacancy Imported At</label>';
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
        $coloumn['importedat']  = __('Imported At'); // Feedback 13 Dec 2023
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
                if (array_key_exists('slug', $status) && $status['slug'] == 'open') {
                    echo '<span style="color: green; font-weight: bold;">' . $status['name'] . '<span>';
                } else if (array_key_exists('slug', $status) && $status['slug'] == 'close') {
                    echo '<span style="color: red; font-weight: bold;">' . $status['name'] . '<span>';
                } else if (array_key_exists('slug', $status) && $status['slug'] == 'declined') {
                    echo '<span style="color: orange; font-weight: bold;">' . $status['name'] . '<span>';
                } else {
                    echo '<span style="color: black; font-weight: bold;">' . $status['name'] . '<span>';
                }
                break;
            case 'expired':
                if ($vacancyModel->checkImported()) {
                    if ($vacancyModel->getImportedSource() == 'jobfeed') {
                        if ($vacancyModel->checkIfJobfeedExpired()) {
                            echo $vacancyModel->getExpiredAt('d M Y');
                        } else {
                            echo '-';
                        }
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
            case 'importedat':
                echo $vacancyModel->getImportedAt('d M Y H:i:s');
                break;
        }
    }

    public function vacancyCustomColoumnSortable($columns)
    {
        $columns['importedat'] = 'importedat';
        return $columns;
    }

    public function vacancySubmitHandle($post_id, $post, $update)
    {
        if ($post->post_type == 'vacancy') {
            $this->wpdb->query('START TRANSACTION');
            try {
                $vacancyModel = new VacancyModel($post_id);
                $vacancyData = [];

                /** Calculate Vacancy City Coordiante */
                // Check if city is not empty,
                $vacancyData['city'] = $vacancyModel->getCity();
                $vacancyData['city_latitude'] = $vacancyModel->getCityLongLat('latitude');
                $vacancyData['city_longitude'] = $vacancyModel->getCityLongLat('longitude');
                error_log(json_encode($vacancyData));

                if ($vacancyData['city']) {
                    if (!$vacancyData['city_longitude'] || empty($vacancyData['city_longitude']) || !$vacancyData['city_longitude'] || empty($vacancyData['city_longitude'])) {
                        /** Calculate Coordinate */
                        $vacancyModel->setCityLongLat($vacancyData['city']);
                        $vacancyData['city_latitude'] = $vacancyModel->getCityLongLat('latitude');
                        $vacancyData['city_longitude'] = $vacancyModel->getCityLongLat('longitude');
                    }
                }

                /** Vacancy Placement Address Coordinatee */
                $vacancyData['placement_address'] = $vacancyModel->getPlacementAddress();

                /** Check if placement address is not empty */
                if (isset($vacancyData['placement_address']) && !empty($vacancyData['placement_address'])) {
                    $vacancyData['placement_address_latitude']   = $vacancyModel->getPlacementAddressLatitude();
                    $vacancyData['placement_address_longitude']  = $vacancyModel->getPlacementAddressLongitude();
                    if (!$vacancyData['placement_address_latitude'] || !$vacancyData['placement_address_longitude']) {
                        /** Calculate Coordinate */
                        $vacancyModel->setPlacementAddressLongitude($vacancyData['placement_address']);

                        $vacancyData['placement_address_latitude']   = $vacancyModel->getPlacementAddressLatitude();
                        $vacancyData['placement_address_longitude']  = $vacancyModel->getPlacementAddressLongitude();
                    }
                }

                /** Set Distance */
                error_log('the distance : ' . json_encode($vacancyModel->getDistance()));
                if (!$vacancyModel->getDistance()) {
                    error_log($post_id . ' - no distance');
                    if ($vacancyData['city_latitude'] && $vacancyData['city_longitude'] && $vacancyData['placement_address_latitude'] && $vacancyData['placement_address_longitude']) {
                        error_log($post_id . ' - setCoordinateDistance');
                        $setCD = $vacancyModel->setCoordinateDistance([
                            'lat' => $vacancyData['city_latitude'],
                            'long' => $vacancyData['city_longitude']
                        ], [
                            'lat' => $vacancyData['placement_address_latitude'],
                            'long' => $vacancyData['placement_address_longitude']
                        ]);
                        error_log($post_id . ' - setCoordinateDistance. ' . json_encode($setCD));
                    } else if ($vacancyData['city'] && $vacancyData['placement_address']) {
                        error_log($post_id . ' - setDistance');
                        $setCD = $vacancyModel->setDistance($vacancyData["placement_city"], $vacancyData["placement_city"] . " " . $vacancyData["placement_address"]);
                        error_log($post_id . ' - setDistance. ' . json_encode($setCD));
                    }
                }

                /** Custom Company Address Coordinate */
                /** Check if is_for_another_company */
                if ($vacancyModel->checkIsForAnotherCompany()) {
                    /** Check if not use_existing_company */
                    if (!$vacancyModel->checkUseExistingCompany()) {
                        $companyData['address_longitude'] = $vacancyModel->getCustomCompanyCoordinate('longitude');
                        $companyData['address_latitude'] = $vacancyModel->getCustomCompanyCoordinate('latitude');
                        $companyData['address'] = $vacancyModel->getCustomCompanyAddress();

                        if (!$companyData['address_longitude'] || !$companyData['address_latitude']) {
                            if ($companyData['address']) {
                                $coordinat = Maphelper::generateLongLat($companyData['address']);
                                $vacancyModel->setCustomCompanyLatitude($coordinat["lat"]);
                                $vacancyModel->setCustomCompanyLongitude($coordinat["long"]);
                            }
                        }
                    }
                }

                $this->wpdb->query('COMMIT');

                /** Set status
                 * IF free vacancy :
                 *   - if new vacancy & if no status : set to processing.
                 * IF paid vacancy :
                 *   - if new vacancy & if no status : set to open.
                 *
                 * IF UPDATE then skip.
                 *  */
                if (!$update || !wp_is_post_revision($post)) {
                    if ($vacancyModel->getIsPaid()) {
                        /** if vacancy has no status, set to open */
                        $vacancyStatus = $vacancyModel->getStatus();
                        if (isset($vacancyStatus) && is_array($vacancyStatus)) {
                            if (!empty($vacancyStatus['name'])) {
                                if ($vacancyStatus['name'] !== 'open') {
                                    $test = $vacancyModel->setStatus('open');
                                }
                            } else {
                                $test = $vacancyModel->setStatus('open');
                            }
                        } else {
                            $vacancyModel->setStatus('open');
                        }
                    } else {
                        $vacancyStatus = $vacancyModel->getStatus();

                        if (isset($vacancyStatus) && is_array($vacancyStatus)) {
                            if (!empty($vacancyStatus['name'])) {
                                if ($vacancyStatus['name'] !== 'processing') {
                                    $vacancyModel->setStatus('processing');
                                }
                            } else {
                                $vacancyModel->setStatus('processing');
                            }
                        } else {
                            $vacancyModel->setStatus('processing');
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->wpdb->query('ROLLBACK');
                error_log($e->getMessage());
            }
        }
    }

    public function vacancyCustomFilterRender($post_type, $which)
    {
        if (is_admin()) {
            if ($post_type == 'vacancy' && $which == 'top') {
                try {
                    /** Get status terms */
                    $terms = get_terms([
                        'taxonomy' => 'status',
                        'hide_emtpy' => false
                    ]);

                    if ($terms instanceof \WP_Error) {
                        throw new \Exception($terms->get_error_message());
                    }
                    // print('<pre>' . print_r($terms, true) . '</pre>');

                    $filterStatusOption = [];
                    if (is_array($terms)) {
                        foreach ($terms as $term) {
                            $filterStatusOption[$term->slug] = $term->name;
                        }
                    }

                    /** Set filter status */
                    echo '<select name="filterVacancyByStatus">';
                    echo '<option value="">' . __('All Status ', THEME_DOMAIN) . '</option>';

                    $selectedStatusFilter = isset($_GET['filterVacancyByStatus']) ? $_GET['filterVacancyByStatus'] : '';
                    foreach ($filterStatusOption as $value => $label) {
                        printf('<option value="%s"%s>%s</option>', $value, $value == $selectedStatusFilter ? ' selected="selected"' : '', $label);
                    }
                    echo '</select>';

                    /** Set filter expired */
                    $filterExpiredOption = [
                        'expired'   => 'Expired Vacancy',
                        'almost'    => 'Almost Expired (7 days)',
                        'not-expired' => 'Not Expired'
                    ];

                    echo '<select name="filterVacancyByExpired">';
                    echo '<option value="">' . __('All Data ', THEME_DOMAIN) . '</option>';

                    $selectedExpiredFilter = isset($_GET['filterVacancyByExpired']) ? $_GET['filterVacancyByExpired'] : '';
                    foreach ($filterExpiredOption as $value => $label) {
                        printf('<option value="%s"%s>%s</option>', $value, $value == $selectedExpiredFilter ? ' selected="selected"' : '', $label);
                    }
                    echo '</select>';

                    /** Set filter Source */
                    $filterSourceOption = [
                        'paid'      => 'Paid Job',
                        'free'      => 'Free Job',
                        'flexfeed'  => 'Brightwave / Flexparency',
                        'jobfeed'   => 'Textkernel Jobfeed'
                    ];

                    echo '<select name="filterVacancyBySource">';
                    echo '<option value="">' . __('All Data ', THEME_DOMAIN) . '</option>';

                    $selectedSourceFilter = isset($_GET['filterVacancyBySource']) ? $_GET['filterVacancyBySource'] : '';
                    foreach ($filterSourceOption as $value => $label) {
                        printf('<option value="%s"%s>%s</option>', $value, $value == $selectedSourceFilter ? ' selected="selected"' : '', $label);
                    }
                    echo '</select>';
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
            }
        }
    }

    public function vacancyCustomFilterHandle($query)
    {
        global $pagenow;
        if (is_admin()) {
            if ($query->get('post_type') == 'vacancy' && $pagenow == 'edit.php') {
                /** Handle filter by status */
                if (isset($_GET['filterVacancyByStatus'])) {
                    if (!empty($_GET['filterVacancyByStatus'])) {
                        // } else {
                        $query->set('tax_query', [
                            'relation' => 'AND',
                            [
                                'taxonomy' => 'status',
                                'field'    => 'slug',
                                'terms'    => $_GET['filterVacancyByStatus'],
                                'compare'  => 'IN'
                            ]
                        ]);
                    }
                }

                $metaQuery = [
                    'relation' => 'AND'
                ];
                $setNewMeta = false;

                /** Handle filter by expired */
                if (isset($_GET['filterVacancyByExpired'])) {
                    if (!empty($_GET['filterVacancyByExpired'])) {
                        switch ($_GET['filterVacancyByExpired']) {
                            case 'expired':
                                $dateLimit = date("Y-m-d H:i:s");
                                $compare = '<=';
                                break;
                            case 'almost':
                                $today          = new DateTimeImmutable();
                                $weekLaterDate  = $today->modify('-7 days');
                                $dateLimit      = [$today->format('Y-m-d'), $weekLaterDate->format('Y-m-d')];
                                $compare        = 'BETWEEN';
                                break;
                            case 'not-expired':
                                $dateLimit = date("Y-m-d H:i:s");
                                $compare = '>=';
                                break;
                        }

                        /** Changes 17 Nov 2023 */
                        // $query->set('meta_query', [
                        //     'relation' => 'AND',
                        //     [
                        //         'key'       => 'expired_at',
                        //         'value'     => $dateLimit,
                        //         'compare'   => $compare,
                        //         'type'      => "DATE"
                        //     ]
                        // ]);
                        $setNewMeta     = true;
                        $metaQuery[]    = [
                            'key'       => 'expired_at',
                            'value'     => $dateLimit,
                            'compare'   => $compare,
                            'type'      => "DATE"
                        ];
                    }
                }

                /** Handle filter by source */
                if (isset($_GET['filterVacancyBySource'])) {
                    if (!empty($_GET['filterVacancyBySource'])) {
                        $addMetaQuery = [];
                        switch ($_GET['filterVacancyBySource']) {
                            case 'paid':
                                $addMetaQuery = [
                                    'relation'  => 'AND',
                                    [
                                        'key'   => 'is_paid',
                                        'value' => 1,
                                        'compare'   => '='
                                    ],
                                    [
                                        'relation'  => 'OR',
                                        [
                                            'relation'  => 'AND',
                                            [
                                                'key'   => 'rv_vacancy_source',
                                                'value' => 'flexfeed',
                                                'compare'   => '!='
                                            ],
                                            [
                                                'key'   => 'rv_vacancy_source',
                                                'value' => 'jobfeed',
                                                'compare'   => '!='
                                            ],
                                        ],
                                        [
                                            'relation' => 'OR',
                                            [
                                                'key'   => 'rv_vacancy_imported_at',
                                                'value' => 0,
                                                'compare'   => '='
                                            ],
                                            [
                                                'key'   => 'rv_vacancy_imported_at',
                                                'value' => NULL,
                                                'compare'   => '='
                                            ],
                                            [
                                                'key'   => 'rv_vacancy_imported_at',
                                                'value' => NULL,
                                                'compare'   => 'NOT EXISTS'
                                            ]
                                        ]
                                    ]
                                ];
                                break;
                            case 'free':
                                $addMetaQuery = [
                                    'relation' => 'AND',
                                    [
                                        'relation' => 'OR',
                                        [
                                            'key'   => 'is_paid',
                                            'value' => 0,
                                            'compare'   => '='
                                        ],
                                        // [
                                        //     'key'   => 'is_paid',
                                        //     'value' => NULL,
                                        //     'compare'   => '='
                                        // ],
                                        // [
                                        //     'key'   => 'is_paid',
                                        //     'value' => "",
                                        //     'compare'   => '='
                                        // ],
                                        [
                                            'key'   => 'is_paid',
                                            'value' => NULL,
                                            'compare'   => 'NOT EXISTS'
                                        ]
                                    ],
                                    [
                                        'relation' => 'OR',
                                        [
                                            'key'   => 'rv_vacancy_imported_at',
                                            'value' => 0,
                                            'compare'   => '='
                                        ],
                                        [
                                            'key'   => 'rv_vacancy_imported_at',
                                            'value' => NULL,
                                            'compare'   => '='
                                        ],
                                        [
                                            'key'   => 'rv_vacancy_imported_at',
                                            'value' => NULL,
                                            'compare'   => 'NOT EXISTS'
                                        ]
                                    ]
                                ];
                                break;
                            case 'flexfeed':
                                $addMetaQuery = [
                                    'key'   => 'rv_vacancy_source',
                                    'value' => 'flexfeed',
                                    'compare'   => '='
                                ];
                                break;
                            case 'jobfeed':
                                $addMetaQuery = [
                                    'key'   => 'rv_vacancy_source',
                                    'value' => 'jobfeed',
                                    'compare'   => '='
                                ];
                        }

                        $setNewMeta     = true;
                        $metaQuery[] = $addMetaQuery;
                    }
                }

                /** Set meta_query */
                if ($setNewMeta) {
                    $query->set('meta_query', $metaQuery);
                }

                /** set order */
                $orderBy = $query->get('orderby');
                if ($orderBy == 'importedat') {
                    $query->set('meta_key', 'rv_vacancy_imported_at');
                    $query->set('orderby', 'meta_value');
                    $query->set('meta_type', 'DATE');
                }
            }
        }
    }

    public function vacancyQuickAction($actions, $post)
    {
        if ($post->post_type == self::vacancy_post_type_slug) {
            $vacancyModel = new VacancyModel($post->ID);
            // print('<pre>' . print_r($vacancyModel->vacancy, true) . '</pre>');
            if ($vacancyModel->vacancy) {
                if (!$vacancyModel->vacancy instanceof WP_Error) {
                    $status = $vacancyModel->getStatus();
                    if ($status) {
                        if (isset($status['slug'])) {
                            if ($status['slug'] == self::status_slug_open) {
                                $actions['preview'] = '<a href="' . FRONTEND_VACANCY_PATH . '/' . $vacancyModel->vacancy->post_name . '">Show Vacancy</a>';
                            } else if ($status['slug'] == self::status_slug_processing) {
                                $actions['preview'] = '<a href="' . FRONTEND_VACANCY_PATH . '/' . $vacancyModel->vacancy->post_name . '">Preview</a>';
                            }
                        }
                    }
                }
            }
        }

        return $actions;
    }

    public function vacancyPreviewOnEdit($post)
    {
        if ($post->post_type === self::vacancy_post_type_slug) {
            $vacancyModel = new VacancyModel($post->ID);

            $output = '';

            if ($vacancyModel->vacancy) {
                if (!$vacancyModel->vacancy instanceof WP_Error) {
                    $status = $vacancyModel->getStatus();
                    if ($status) {
                        if (isset($status['slug'])) {
                            if ($status['slug'] == self::status_slug_open) {
                                $output .= '<div style="margin-top: 10px;">';
                                $output .= '<a class="button button-primary" href="' . FRONTEND_VACANCY_PATH . '/' . $vacancyModel->vacancy->post_name . '">Show Vacancy</a>';
                                $output .= '</div>';
                            } else if ($status['slug'] == self::status_slug_processing) {
                                $output .= '<div style="margin-top: 10px;">';
                                $output .= '<a class="button button-primary" href="' . FRONTEND_VACANCY_PATH . '/' . $vacancyModel->vacancy->post_name . '">Preview as client</a>';
                                $output .= '</div>';
                            }
                        }
                    }
                }
            }

            echo $output;
        }
    }
}

new Vacancy();
