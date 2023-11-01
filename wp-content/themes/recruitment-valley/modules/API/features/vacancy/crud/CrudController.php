<?php

namespace Vacancy;

use Constant\Message;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use JWTHelper;
use Model\Company;
use Model\ModelHelper;
use WP_Post;
use WP_Query;
use Global\NotificationService;
use constant\NotificationConstant;
use DateTime;

class VacancyCrudController
{
    private $_posttype = 'vacancy';
    private $_message;
    private $_notification;
    private $_notificationConstant;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->_message = new Message;
        $this->_notification = new NotificationService();
        $this->_notificationConstant = new NotificationConstant();
    }

    public function show()
    {
    }

    /** CHANGE STARTS HERE */

    /**
     * getAllByLocations
     *
     * @param  mixed $data
     * @param  mixed $params
     * @return array
     */
    public function getAllByLocations($data, $params): array
    {
        // get locations city
        $placementCity = $params['placementAddress'];
        $apiKey = 'AIzaSyCSDEm4LesoBQq9i9W0tjwHWICln6Qsom8';

        $apiUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($placementCity) . "&key=" . $apiKey;
        $response = file_get_contents($apiUrl);
        $data = json_decode($response, true);

        // standarization city name

        // get data by city

        // give by radius
        return [];
    }

    public function getAll($request)
    {
        $vacancy = new Vacancy;
        $filters = [
            'page' => isset($request['page']) ? intval($request['page']) : 1,
            'search' => $request['search'] ?? null,
            'city' => $request['city'] ?? null,
            'salaryStart' => isset($request['salaryStart']) ? intval($request['salaryStart']) : 0,
            'salaryEnd' => isset($request['salaryEnd']) ? intval($request['salaryEnd']) : null,
            'postPerPage' => $request['perPage'] ?? 10,
            'orderBy' => isset($request['orderBy']) ? $request['orderBy'] : false,
            'order' => isset($request['sort']) ? $request['sort'] : false,
            "radius" => isset($request['radius']) ? $request['radius'] : false,
        ];

        $taxonomyFilters = [
            'education'     => isset($request['education']) && $request['education'] !== "" ? explode(',', $request['education']) : NULL,
            'role'          => isset($request['role']) && $request['role'] !== "" ? explode(',', $request['role']) : NULL,
            'sector'        => isset($request['sector']) && $request['sector'] !== "" ? explode(',', $request['sector']) : NULL,
            'working-hours' => isset($request['hoursPerWeek']) && $request['hoursPerWeek'] !== "" ? explode(',', $request['hoursPerWeek']) : NULL,
            'type'          => isset($request['employmentType']) && $request['employmentType'] !== "" ? explode(',', $request['employmentType']) : NULL,
            'location'      => isset($request['location']) && $request['location'] !== "" ? explode(',', $request['location']) : NULL,
            'experiences'   => isset($request['experiences']) && $request['experiences'] !== "" ? explode(',', $request['experiences']) : NULL,
        ];

        $offset = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

        $args = [
            "post_type" => $this->_posttype,
            "posts_per_page" => $filters['postPerPage'],
            "offset" => $offset,
            "order" => "ASC",
            "post_status" => "publish",
            "meta_query" => [
                "relation" => "AND",
                [
                    'key' => 'expired_at',
                    'value' => date("Y-m-d H:i:s"),
                    'compare' => '>',
                    'type' => "DATE"
                ],
            ],
            'tax_query' => []
        ];

        /** Sort */
        if ($filters['orderBy']) {
            $args['orderby'] = $filters['orderBy'];
            $args['order'] = $filters['order'];
        }

        /** Set tax query */
        // only display open job status
        $args['tax_query'] = [
            "relation" => 'AND',
            [
                'taxonomy' => 'status',
                'field'     => 'slug',
                'terms'     => 'open',
                'compare'  => 'IN'
            ]
        ];

        foreach ($taxonomyFilters as $key => $value) {
            if ($value && $value !== null && !empty($value)) {
                $args['tax_query'][1]['relation'] = 'OR';

                if ($value !== null && $value !== "" && !empty($value)) {
                    array_push($args['tax_query'][1], [
                        'taxonomy' => $key,
                        'field'     => 'term_id',
                        'terms'     => $value,
                        'compare'  => 'IN'
                    ]);
                }
            }
        }

        /** Set meta query */
        // If salaryStart and salaryEnd exist + more than 0
        if (array_key_exists('salaryStart', $request) || array_key_exists('salaryEnd', $request)) {
            if ($filters['salaryStart'] >= 0 && $filters['salaryStart'] >= 0 && $filters['salaryEnd'] !== null && $filters['salaryEnd'] > 0) {
                if (!array_key_exists('meta_query', $args)) {
                    $args['meta_query'] = [
                        "relation" => 'OR'
                    ];
                }

                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => 'salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ],
                    [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                ]);
                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => 'salary_start',
                        'value' => [$filters['salaryStart'], $filters['salaryEnd']],
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    ],
                    [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                ]);
            } else if ($filters['salaryStart'] >= 0 || $filters['salaryEnd'] >= 0) { // if only one of them is filled
                if (!array_key_exists('meta_query', $args)) {
                    $args['meta_query'] = [
                        "relation" => 'AND'
                    ];
                }

                if ($filters['salaryStart'] >= 0 && $filters['salaryEnd'] === null) { // if start is filled but other is empty
                    array_push($args['meta_query'], [
                        'key' => 'salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => 'salary_end',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ]);
                } else if ($filters['salaryEnd'] !== null) { // vice versa
                    array_push($args['meta_query'], [
                        'key' => 'salary_start',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                }
            }
        }

        // filter by city
        if ($filters["city"]) {
            array_push($args['meta_query'], [
                'key' => 'placement_city',
                'value' => $filters['city'],
                'compare' => '=',
            ]);
        }

        if ($filters["radius"]) {
            array_push($args['meta_query'], [
                'key' => 'distance_from_city',
                'value' => $filters['radius'],
                'compare' => '<',
                "type" => "numeric",
            ]);
        }

        /** Filter Vacancy Ids
         * Only how vacancy id in params.
         * DO NOT Filter if params IS empty or NOT present
         */
        if (array_key_exists('vacancyId', $request) || isset($request['vacancyId'])) {
            if (!empty($request['vacancyId'])) {
                if (is_array($request['vacancyId'])) {
                    $args['post__in'] = $request['vacancyId'];
                } else {
                    $args['post__in'] = explode(',', $request['vacancyId']);
                }
            }
        }

        /** Search */
        if (array_key_exists('search', $filters) && $filters['search'] !== '' && isset($filters['search'])) {
            $args['s'] = $filters['search'];
            $args['search_columns'] = ['post_title'];
        }

        // echo '<pre>';
        // var_dump($args);
        // echo '</pre>';die;

        add_filter('posts_search', [$this, 'filterVacancySearch'], 10, 2);
        $vacancies = new WP_Query($args);
        apply_filters('posts_search', $vacancies->request, $vacancies);
        remove_filter('posts_search', [$this, 'filterVacancySearch'], 10, 2);

        return [
            'message' => $this->_message->get('vacancy.get_all'),
            'data'    => $vacancies->posts,
            'meta'    => [
                'currentPage'   => isset($filters['page']) ? intval($filters['page']) : 1,
                'totalPage'     => $vacancies->max_num_pages,
                'totalResult'   => $vacancies->found_posts
            ],
            'status'  => 200
        ];
    }

    public function get($request)
    {
        $vacancy = new Vacancy;

        $vacancySlug = $request['vacancy_slug'];

        $vacancy = get_page_by_path($vacancySlug, OBJECT, 'vacancy');

        if ($vacancy instanceof WP_Post) {
            return [
                "status" => 200,
                "message" => $this->_message->get("vacancy.get_all"),
                "data" => $vacancy
            ];
        } else {
            return [
                "status" => 404,
                "message" => $this->_message->get("vacancy.not_found"),
                // "data" => []
            ];
        }
    }

    public function createFree($request)
    {
        $payload = [
            "title" => $request["name"],
            "country" => $request['country'], // Added Line
            "city" => $request["city"],
            "placementAddress" => $request["placementAddress"],
            "description" => $request["description"],
            "salary_start" => $request["salaryStart"],
            "salary_end" => $request["salaryEnd"],
            "external_url" => $request["externalUrl"],
            "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
            "is_paid" => false,
            "user_id" => $request["user_id"],
            "placementAddressLongitude" => $request["longitude"],
            "placementAddressLatitude" => $request["latitude"],
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "experiences" => $request["experiences"] ?? [], // Added Line
                "status" => [31] // set free job become pending category
            ],
            "countryCode" => $request["countryCode"]
        ];

        $this->wpdb->query("START TRANSACTION");
        try {
            $vacancyModel = new Vacancy;
            $vacancyModel->storePost($payload);
            $vacancyModel->setTaxonomy($payload["taxonomy"]);
            $vacancyModel->setProp($vacancyModel->acf_placement_city, $payload["city"]);
            $vacancyModel->setProp($vacancyModel->acf_description, $payload["description"]);
            $vacancyModel->setProp($vacancyModel->acf_is_paid, $payload["is_paid"]);
            $vacancyModel->setProp($vacancyModel->acf_salary_start, $payload["salary_start"]);
            $vacancyModel->setProp($vacancyModel->acf_salary_end, $payload["salary_end"]);
            $vacancyModel->setProp($vacancyModel->acf_apply_from_this_platform, $payload["apply_from_this_platform"]);
            $vacancyModel->setProp($vacancyModel->acf_expired_at, ''); // Added Line
            $vacancyModel->setProp($vacancyModel->acf_placement_address, $payload["placementAddress"]);
            $vacancyModel->setProp($vacancyModel->acf_country_code, $payload["countryCode"]);

            $vacancyModel->setProp($vacancyModel->acf_country, $payload['country']); // Added line

            if ($payload["apply_from_this_platform"]) {
                $vacancyModel->setProp($vacancyModel->acf_external_url, $payload["external_url"]);
            }

            // $expiredAt = new DateTimeImmutable();
            // $expiredAt = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");

            $vacancyModel->setCityLongLat($payload["city"]);
            $vacancyModel->setPlacementAddressLatitude($payload["placementAddressLatitude"]);
            $vacancyModel->setPlacementAddressLongitude($payload["placementAddressLongitude"]);
            // $vacancyModel->setAddressLongLat($payload["placementAddress"]);
            $vacancyModel->setDistance($payload["city"], $payload["city"] . " " . $payload["placementAddress"]);

            $vacancyModel->setStatus('processing');
            // $vacancyModel->setProp("expired_at", $expiredAt);
            $this->wpdb->query("COMMIT");

            /** Create notification */
            $this->_notification->write($this->_notificationConstant::VACANCY_SUBMITTED, $request['user_id'], [
                'id'    => $vacancyModel->vacancy_id,
                'slug'  => $vacancyModel->getSlug(),
                'title' => $vacancyModel->getTitle()
            ]);

            return [
                "status" => 201,
                "message" => $this->_message->get("vacancy.create.free.success"),
            ];
        } catch (\Throwable $th) {
            $this->wpdb->query("ROLLBACK");
            return [
                "status" => 500,
                "message" => $th->getMessage(),
            ];
        } catch (\Exception $e) {
            $this->wpdb->query("ROLLBACK");
            return [
                "status" => 500,
                "message" => $e->getMessage(),
            ];
        }
    }

    public function createPaid($request)
    {
        $payload = [
            "title" => $request["name"],
            "city" => $request["city"],
            "placementAddress" => $request["placementAddress"],
            "description" => $request["description"],
            "term" => $request["terms"],
            "salary_start" => $request["salaryStart"],
            "salary_end" => $request["salaryEnd"],
            "external_url" => $request["externalUrl"],
            "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
            "is_paid" => true,
            "user_id" => $request["user_id"],
            "application_process_title" => $request["applicationProcedureTitle"],
            "application_process_description" => $request["applicationProcedureText"],
            "video_url" => $request["video"],
            "facebook_url" => $request["facebook"],
            "linkedin_url" => $request["linkedin"],
            "instagram_url" => $request["instagram"],
            "twitter_url" => $request["twitter"],
            "reviews" => $request["review"],
            "placementAddressLongitude" => $request["longitude"],
            "placementAddressLatitude" => $request["latitude"],
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "experiences" => $request["experiences"] ?? [], // Added Line
                "status" => [32] // set free job become pending category
            ],
            "application_process_step" => $request["applicationProcedureSteps"],
            'rv_vacancy_country' => $request['country'], // Added Line
            'rv_vacancy_country_code' => $request['countryCode'] // Added Line
        ];

        global $wpdb;
        try {
            $wpdb->query('START TRANSACTION');
            $vacancyModel = new Vacancy;

            // paid job validation

            /** Anggit's syntax */
            // $company = new Company($payload["user_id"]);
            // $companyCredit = $company->getCredit();
            // $paidJobPrice = 1;
            // if ($companyCredit < $paidJobPrice) return ["status" => 402, "message" => $this->_message->get('company.profile.insufficient_credit')];
            /** Anggit's syntax end here */

            /** Changes start here */
            $company = new Company($payload["user_id"]);

            // Check if user is on unlimited and check if unlimited package is expired or not
            $isUnlimited = $this->_checkUserUnlimitedPackage($payload["user_id"]);
            if (!$isUnlimited) {
                $companyCredit = $company->getCredit();
                $paidJobPrice = 1;
                if ($companyCredit < $paidJobPrice) return ["status" => 402, "message" => $this->_message->get('company.profile.insufficient_credit')];
            }

            //end job validation

            $vacancyModel->storePost($payload);
            $vacancyModel->setTaxonomy($payload["taxonomy"]);

            foreach ($payload as $acf_field => $value) {
                if ($acf_field !== "taxonomy") {
                    $vacancyModel->setProp($acf_field, $value, is_array($value));
                }
            }

            $vacancyID = $vacancyModel->setProp($vacancyModel->acf_placement_city, $payload["city"]);
            $vacancyModel->setProp($vacancyModel->acf_placement_address, $payload["placementAddress"]);

            $expiredAt = new DateTimeImmutable();
            $expiredAt = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");

            $vacancyModel->setStatus('open');
            $vacancyModel->setProp("expired_at", $expiredAt);

            /** Added syntax for gallery */
            $galleries = ModelHelper::handle_uploads('galleryJob', 'vacancy/' . $vacancyID);
            if (array_key_exists('galleryCompany', $request)) {
                if (is_array($request['galleryCompany'])) {
                    $galleryIds = array_map(function ($gallery) {
                        return explode('-', $gallery)[0];
                    }, $request['galleryCompany']);
                }
            }

            if (isset($_FILES['video']['name'])) {
                $video = ModelHelper::handle_upload('video');
                $vacancyModel->setVideoUrl($video["video"]["url"]);
            } else {
                $vacancyModel->setVideoUrl($payload["video_url"]);
            }

            $vacancyGallery = $galleryIds ?? [];

            if ($galleries) {
                foreach ($galleries as $key => $gallery) {
                    $vacancyGallery[] = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                }
            }

            $vacancyModel->setProp($vacancyModel->acf_gallery, $vacancyGallery, false);

            $vacancyModel->setCityLongLat($payload["city"]);
            // $vacancyModel->setAddressLongLat($payload["placementAddress"]);
            $vacancyModel->setPlacementAddressLatitude($payload["placementAddressLatitude"]);
            $vacancyModel->setPlacementAddressLongitude($payload["placementAddressLongitude"]);
            $vacancyModel->setDistance($payload["city"], $payload["city"] . " " . $payload["placementAddress"]);

            $this->add_expired_date_to_option([
                'post_id' => $vacancyModel->vacancy_id,
                'expired_at' => $expiredAt
            ]);

            /** Anggit's syntax */
            // charge credit
            // $companyCredit -= $paidJobPrice;
            // $company->setCredit($companyCredit);

            /** Changes for unlimited package :
             * check user_meta if company is on unlimited package
             */
            if (!$isUnlimited) {
                // charge credit
                $companyCredit -= $paidJobPrice;
                $company->setCredit($companyCredit);
            }

            /** Changes End Here */

            $wpdb->query('COMMIT');

            /** Create notification */
            $this->_notification->write($this->_notificationConstant::VACANCY_PUBLISHED, $request['user_id'], [
                'id'    => $vacancyModel->vacancy_id,
                'slug'  => $vacancyModel->getSlug(),
                'title' => $vacancyModel->getTitle()
            ]);

            return [
                "status" => 201,
                "data" => [
                    "slug" => $vacancyModel->getSlug(),
                ],
                "message" => $this->_message->get("vacancy.create.paid.success"),
            ];
        } catch (\Throwable $th) {
            $wpdb->query('ROLLBACK');
            return [
                "status" => 500,
                // "message" => $this->_message->get("vacancy.create.paid.fail"),
                "message" => $th->getMessage(),

            ];
        } catch (\WP_Error $e) {
            $wpdb->query('ROLLBACK');
            return [
                "status" => 500,
                "message" => $e->get_error_message(),
                // "message" => $this->_message->get("vacancy.create.paid.fail"),
            ];
        }
    }

    /**
     * Filter wp_query search function
     * change the sql query when search when posts_search hook is run
     *
     * @param [mixed] $search
     * @param [object] $query
     * @return void
     */
    public function filterVacancySearch($search,  $query)
    {
        global $wpdb;

        if ($query->is_search && $query->get('post_type') == $this->_posttype) {
            $searchKeyword = $query->get('s');

            if (!empty($searchKeyword)) {
                if (is_string($searchKeyword)) {
                    $arrayOfKeyword = explode(' ', $searchKeyword);
                    $regexPattern = '';
                    for ($i = 0; $i < count($arrayOfKeyword); $i++) {
                        $regexPattern .= $arrayOfKeyword[$i];
                        if ($i < (count($arrayOfKeyword) - 1)) {
                            $regexPattern .= '|';
                        }
                    }
                    $search = "AND $wpdb->posts.post_title REGEXP '(" . esc_sql($regexPattern) . ")'";
                    // $search = "AND $wpdb->posts.post_title LIKE '%" . esc_sql($regexPattern) . "%'";
                }
            }
        }

        return $search;
    }

    public function update($request)
    {
        $vacancy_id = $request["vacancy_id"];
        $vacancyModel = new Vacancy($vacancy_id);

        $vacancyIsPaid = $vacancyModel->getIsPaid();

        $payload = $this->createVacancyPayload($vacancyIsPaid, $request);

        $vacancyModel->setTaxonomy($payload["taxonomy"]);

        foreach ($payload as $acf_field => $value) {
            if ($acf_field !== "taxonomy") {
                $vacancyModel->setProp($acf_field, $value, is_array($value));
            }
        }

        return [
            "status" => 200,
            "message" => $vacancyIsPaid ? $this->_message->get("vacancy.update.paid.success") : $this->_message->get("vacancy.update.free.success")
        ];
    }

    public function updateFree($request)
    {
        $vacancy_id = $request["vacancy_id"];

        /** Anggit's syntax start here */
        // $vacancyModel = new Vacancy($vacancy_id);

        // $payload = $this->createFreeVacancyPayload($request);

        // $vacancyModel->setTaxonomy($payload["taxonomy"]);

        // foreach ($payload as $acf_field => $value) {
        //     if ($acf_field !== "taxonomy") {
        //         $vacancyModel->setProp($acf_field, $value, is_array($value));
        //     }
        // }

        // /** Changes start here */
        // $vacancyModel->setCityLongLat($payload["placement_city"]);
        // $vacancyModel->setAddressLongLat($payload["placement_address"]);
        // $vacancyModel->setDistance($payload["placement_city"], $payload["placement_city"] . " " . $payload["placement_address"]);

        // return [
        //     "status" => 200,
        //     "message" => $this->_message->get("vacancy.update.free.success")
        // ];

        /** Changes start here */
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');
            $vacancyModel = new Vacancy($vacancy_id);

            /** Added line start here */
            if ($vacancyModel->getStatus()['slug'] == 'processing') {
                throw new Exception($this->_message->get("vacancy.update.free.on_process"));
            }

            $fromRejected = false;
            if ($vacancyModel->getStatus()['slug'] == 'declined') {
                $fromRejected = true;
            }
            /** Added line end here */

            $payload = $this->createFreeVacancyPayload($request);

            $vacancyModel->setTaxonomy($payload["taxonomy"]);

            foreach ($payload as $acf_field => $value) {
                if ($acf_field !== "taxonomy") {
                    $vacancyModel->setProp($acf_field, $value, is_array($value));
                }
            }

            /** Changes start here */
            if ($fromRejected) {
                $vacancyModel->setStatus('processing');
            }

            $vacancyModel->setCityLongLat($payload["placement_city"]);
            // $vacancyModel->setAddressLongLat($payload["placement_address"]);
            $vacancyModel->setPlacementAddressLatitude($payload["placementAddressLatitude"]);
            $vacancyModel->setPlacementAddressLongitude($payload["placementAddressLongitude"]);
            $vacancyModel->setDistance($payload["placement_city"], $payload["placement_city"] . " " . $payload["placement_address"]);

            $wpdb->query('COMMIT');

            /** Create notification if current status is rejected */
            if ($fromRejected) {
                $this->_notification->write($this->_notificationConstant::VACANCY_SUBMITTED, $request['user_id'], [
                    'id'    => $vacancyModel->vacancy_id,
                    'slug'  => $vacancyModel->getSlug(),
                    'title' => $vacancyModel->getTitle()
                ]);
            }
        } catch (\Throwable $th) {
            $wpdb->query('ROLLBACK');

            return [
                "message" => $this->_message->get("system.overall_failed"),
                "errors" => $th->getMessage(),
                "status" => 400,
            ];
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');

            return [
                'message' => $this->_message->get("vacancy.update.free.fail"),
                'errors' => $e->getMessage(),
                'status' => 400
            ];
        }

        return [
            "status" => 200,
            "message" => $this->_message->get("vacancy.update.free.success")
        ];
    }

    public function updatePaid($request)
    {
        $vacancy_id = $request["vacancy_id"];
        global $wpdb;

        try {
            $wpdb->query('START TRANSACTION');

            $vacancyModel = new Vacancy($vacancy_id);
            $payload = $this->createPaidVacancyPayload($request);

            $vacancyModel->setTaxonomy($payload["taxonomy"]);

            foreach ($payload as $acf_field => $value) {
                if ($acf_field !== "taxonomy") {
                    $vacancyModel->setProp($acf_field, $value, is_array($value));
                }
            }

            $galleries = ModelHelper::handle_uploads('galleryJob', 'vacancy/' . $vacancy_id);
            if (array_key_exists('galleryCompany', $request)) {
                if (is_array($request['galleryCompany'])) {
                    $galleryIds = array_map(function ($gallery) {
                        return explode('-', $gallery)[0];
                    }, $request['galleryCompany']);
                }
            }

            if (isset($_FILES['video']['name'])) {
                $video = ModelHelper::handle_upload('video');
                $vacancyModel->setVideoUrl($video["video"]["url"]);
            } else {
                $vacancyModel->setVideoUrl($payload["video_url"]);
            }

            $vacancyGallery = $galleryIds ?? [];

            if ($galleries) {
                foreach ($galleries as $key => $gallery) {
                    $vacancyGallery[] = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                }
            }

            $vacancyModel->setProp($vacancyModel->acf_gallery, $vacancyGallery, false);


            if ($galleries) {
                foreach ($galleries as $key => $gallery) {
                    $vacancyGallery[] = wp_insert_attachment($gallery['attachment'], $gallery['file']);
                }
            }

            /** Changes start here */
            $vacancyModel->setCityLongLat($payload["placement_city"]);
            // $vacancyModel->setAddressLongLat($payload["placement_address"]);
            $vacancyModel->setPlacementAddressLatitude($payload["placementAddressLatitude"]);
            $vacancyModel->setPlacementAddressLongitude($payload["placementAddressLongitude"]);
            $vacancyModel->setDistance($payload["placement_city"], $payload["placement_city"] . " " . $payload["placement_address"]);

            $wpdb->query('COMMIT');

            return [
                "status" => 200,
                "data" => [
                    "slug" => $vacancyModel->getSlug(),
                ],
                "message" => $this->_message->get("vacancy.update.paid.success")
            ];
        } catch (\Throwable $th) {
            $wpdb->query('ROLLBACK');
            return [
                "status" => 400,
                "message" => $this->_message->get("system.overall_failed")
            ];
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return [
                "status" => 400,
                "message" => $this->_message->get("system.overall_failed")
            ];
        }
    }

    public function trash($request)
    {
        $vacancy = new Vacancy($request["vacancy_id"]);

        if ($request["user_id"] != $vacancy->getAuthor()) {
            return [
                "status" => 400,
                "message" => $this->_message->get("vacancy.trash.not_authorized")
            ];
        }

        $trashed = $vacancy->trash();

        if (is_wp_error($trashed)) {
            return [
                "status" => 400,
                "message" => $this->_message->get("vacancy.trash.fail")
            ];
        }

        return [
            "status" => 200,
            "message" => $this->_message->get("vacancy.trash.success")
        ];
    }

    /**
     * createVacancyPayload
     * map payload base on free job or paid job
     *
     * @param  mixed $isPaid
     * @param  mixed $request
     * @return array
     */
    private function createVacancyPayload($isPaid, $request)
    {
        $payload = [];

        if ($isPaid) {
            $payload = [
                "title" => $request["name"],
                "description" => $request["description"],
                "term" => $request["terms"],
                "salary_start" => $request["salaryStart"],
                "salary_end" => $request["salaryEnd"],
                "external_url" => $request["externalUrl"],
                "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
                "user_id" => $request["user_id"],
                "application_process_title" => $request["applicationProcedureTitle"],
                "application_process_description" => $request["applicationProcedureText"],
                "video_url" => $request["video"],
                "facebook_url" => $request["facebook"],
                "linkedin_url" => $request["linkedin"],
                "instagram_url" => $request["instagram"],
                "twitter_url" => $request["twitter"],
                "reviews" => $request["review"],
                "taxonomy" => [
                    "sector" => $request["sector"],
                    "role" => $request["role"],
                    "working-hours" => $request["workingHours"],
                    "location" => $request["location"],
                    "education" => $request["education"],
                    "type" => $request["employmentType"],
                    "experiences" => $request["experiences"] ?? [], // Added Line
                    "status" => [32] // set free job become pending category
                ],
                "application_process_step" => $request["applicationProcedureSteps"],
                'rv_vacancy_country_code' => $request['countryCode'], // Added Line
                "placementAddressLongitude" => $request["longitude"],
                "placementAddressLatitude" => $request["latitude"],
            ];
        }

        if (!$isPaid) {
            $payload = [
                "title" => $request["name"],
                "description" => $request["description"],
                "salary_start" => $request["salaryStart"],
                "salary_end" => $request["salaryEnd"],
                "external_url" => $request["externalUrl"],
                "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
                "user_id" => $request["user_id"],
                "placementAddressLongitude" => $request["longitude"],
                "placementAddressLatitude" => $request["latitude"],
                "taxonomy" => [
                    "sector" => $request["sector"],
                    "role" => $request["role"],
                    "working-hours" => $request["workingHours"],
                    "location" => $request["location"],
                    "education" => $request["education"],
                    "type" => $request["employmentType"],
                    "experiences" => $request["experiences"] ?? [], // Added Line
                    "status" => [31] // set free job become pending category
                ],
            ];
        }

        return $payload;
    }

    public function createFreeVacancyPayload($request)
    {
        $payload = [
            // "title" => $request["name"],
            "placement_city" => $request["city"],
            "placement_address" => $request["placementAddress"],
            "description" => $request["description"],
            "salary_start" => $request["salaryStart"],
            "salary_end" => $request["salaryEnd"],
            "external_url" => $request["externalUrl"],
            "placementAddressLongitude" => $request["longitude"],
            "placementAddressLatitude" => $request["latitude"],
            // "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false, // Changed Below
            "apply_from_this_platform" => (isset($request["externalUrl"]) && $request["externalUrl"] !== '' ? false : true),
            "user_id" => $request["user_id"],
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "experiences" => $request["experiences"] ?? [], // Added Line
            ],
            'rv_vacancy_country' => $request['country'],
            'rv_vacancy_country_code' => $request['countryCode'] // Added Line
        ];

        return $payload;
    }

    public function createPaidVacancyPayload($request)
    {
        $payload = [
            // "title" => $request["name"],
            "placement_city" => $request["city"],
            "description" => $request["description"],
            "term" => $request["terms"],
            "salary_start" => $request["salaryStart"],
            "salary_end" => $request["salaryEnd"],
            "external_url" => $request["externalUrl"],
            // "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false, // Changed Below
            "apply_from_this_platform" => (isset($request["externalUrl"]) && $request["externalUrl"] !== '' ? false : true),
            "user_id" => $request["user_id"],
            "application_process_title" => $request["applicationProcedureTitle"],
            "application_process_description" => $request["applicationProcedureText"],
            "video_url" => $request["video"],
            "facebook_url" => $request["facebook"],
            "linkedin_url" => $request["linkedin"],
            "instagram_url" => $request["instagram"],
            "twitter_url" => $request["twitter"],
            "reviews" => $request["review"],
            "placement_address" => $request["placementAddress"],
            "placementAddressLongitude" => $request["longitude"],
            "placementAddressLatitude" => $request["latitude"],
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "experiences" => $request["experiences"] ?? [], // Added Line
            ],
            "application_process_step" => $request["applicationProcedureSteps"],
            'rv_vacancy_country' => $request['country'],
            'rv_vacancy_country_code' => $request['countryCode'] // Added Line
        ];

        return $payload;
    }

    /**
     * repost
     *
     * @param  mixed $request
     * @return array
     */
    public function repost($request): array
    {
        // get vacancy id by request
        $vacancy_id = $request['vacancy_id'];

        // get user id by request
        $user_id = $request['user_id'];

        // setup vacancy class
        $vacancy = new Vacancy($request["vacancy_id"]);

        // check id author
        $author = (int) $vacancy->getAuthor();

        // return 400 if author and user id not match
        if ($author !== $user_id) return ["status" => 400, "message" => $this->_message->get('company.vacancy.repost_no_permission')];

        // return 400 if post id not found
        if (get_post_status($vacancy_id) === false)   return ["status" => 400, "message" => "invalid post"];

        // total credit
        // TODO : get total current credit
        $company = new Company($user_id);
        $job_credit = $company->getCredit();

        // TODO : get credit per 1 time repost
        $credit_per_post = 1;

        // return 402 if credit is insufficient
        if ($job_credit < $credit_per_post) return ["status" => 402, "message" => $this->_message->get("company.profile.insufficient_credit")];

        // get status vacancies
        $status = $vacancy->getStatus();
        $status_lower = strtolower($status['name']);

        // return 400 if status post is not Close
        // if ($status_lower !== 'close') return ["status" => 402, "message" => "You cannot repost a job with id {$vacancy_id}, because the job is still in the {$status['name']} state."]; // Changed Below
        if ($status_lower !== 'close') return ["status" => 402, "message" => $this->_message->get('company.vacancy.repost_can_not')];

        // remove old term status
        $taxonomy = 'status';
        wp_remove_object_terms($vacancy_id,  $status['term_id'], $taxonomy);

        // add new term status
        $term_name = "Open";
        wp_set_object_terms($vacancy_id, $term_name, $taxonomy, true);

        // set expired date +1 month
        $date_expired = new DateTimeImmutable();
        $date_expired = $date_expired->modify("+30 days")->format("Y-m-d H:i:s");
        update_field('expired_at', $date_expired, $vacancy_id);

        // add expired at date and post into option to make it works in cron
        $this->add_expired_date_to_option(["post_id" => $vacancy_id, "expired_at" => $date_expired]);

        // reduce credit
        $job_credit -= $credit_per_post;

        // TODO : update total credit
        $company->setCredit($job_credit);

        // return status 200
        return [
            "status" => 402,
            "message" => $this->_message->get('company.vacancy.repost_success')
        ];
    }

    public function add_expired_date_to_option($jobExpiredAt)
    {
        $jobExpireDate = get_option("job_expires") ? maybe_unserialize(get_option("job_expires")) : [];

        array_push($jobExpireDate, $jobExpiredAt);

        update_option("job_expires", maybe_serialize($jobExpireDate));
    }

    /**
     * check if user on unlimited package function
     *
     * @param int|string $userID
     * @return mixed (array|bool)
     */
    private function _checkUserUnlimitedPackage($userID)
    {
        $company = new Company($userID);
        $onUnlimited = $company->checkUnlimited() ?? false;
        if ($onUnlimited) {
            $checkExpiredDate = $company->getUnlimitedExpired();
            if ($checkExpiredDate) {
                $expiredDateTimestamp = strtotime($checkExpiredDate);
                if ($expiredDateTimestamp >= time()) {
                    return [
                        'onUnlimited' => $company->checkUnlimited(),
                        'unlimitedExpiredDate' => $checkExpiredDate
                    ];
                }

                return false;
            }

            return false;
        } else {
            return false;
        }
    }

    /**
     * Aproval last 24 hour function
     *
     * If admin not approve in last 24 hour, system will auto-approve.
     * It will be recommended to change the hour range to be dynamic,
     * so end-user can change the range themself.
     *
     * @return void
     */
    public function checkVacancyApprovalInLastHours()
    {
        error_log('check approval in last 24 hour');
        try {
            $vacancy = new Vacancy();

            /** Set time limit 24 hour ago */
            $now = new DateTimeImmutable('now');
            $timeLimit = $now->modify('-24 hours')->format('Y-m-d H:i:s');

            /** Get imported vacancy that imported >= 24 hour ago and still waiting approval */
            $importedVacancies = $vacancy->getVacancies([], [], [
                "meta_query" => [
                    "relation" => 'AND',
                    [
                        'key'       => 'rv_vacancy_is_imported',
                        'value'     => 1,
                        'compare'   => '=',
                    ], [
                        [
                            'key'       => 'rv_vacancy_imported_approval_status',
                            'value'     => 'waiting',
                            'compare'   => '=',
                        ],
                        [
                            'key'       => 'rv_vacancy_imported_at',
                            'value'     => $timeLimit,
                            'compare'   => '<=',
                            'type'      => 'Date',
                        ]
                    ]
                ]
            ]);

            if ($importedVacancies && $importedVacancies->found_posts > 0) {
                /** Check if vacancy not yet expired, compare by start of today */
                foreach ($importedVacancies->posts as $vacancy) {
                    $vacancyModel   = new Vacancy($vacancy->ID);
                    $approvalStatus = $vacancyModel->getApprovedStatus($vacancy->ID);

                    $expiredAt      = $vacancyModel->getExpiredAt('Y-m-d H:i:s');
                    if ($expiredAt) {
                        $expiredAt = new DateTime($expiredAt);
                    } else { // If expired didn't exist then set to rejected and continue the loop
                        $vacancyModel->setApprovedStatus('rejected');
                        // $vacancyModel->setApprovedBy(null);
                        $vacancyModel->setStatus('close');
                        continue;
                    }

                    /** Check if expired, set status to reject and close
                     * If not, set status to approve and open
                     */
                    if ($expiredAt < $now) {
                        $vacancyModel->setApprovedStatus('rejected');
                        $vacancyModel->setApprovedBy(null);
                        $vacancyModel->setStatus('close');
                    } else {
                        if ($approvalStatus['value'] == 'waiting') {
                            $vacancyModel->setApprovedStatus('system-approved');
                            // $vacancyModel->setApprovedBy(null);
                            $vacancyModel->setStatus('open');
                            $vacancyModel->setApprovedAt('now');
                        }
                    }
                }
            }
        } catch (\WP_Error $err) {
            error_log($err->get_error_message());
        } catch (\Exception $e) {
            error_log($e->getMessage());
        } catch (\Throwable $th) {
            error_log($th->getMessage());
        }
    }

    /**
     * GET Vacancy by company ID function
     *
     * @param Mixed $companyID
     * @param integer $limit
     * @return array
     */
    public function getVacancyByCompany(Mixed $companyID, Int $limit = -1, String $result = 'posts', $filter = [])
    {
        try {
            $vacancyModel   = new Vacancy();

            if (is_array($companyID)) {
                $filters = [
                    'author'    => $companyID,
                ];
            } else {
                $filters['author'] = [$companyID];
            }

            if (!empty($filter)) {
                if (array_key_exists('with_expired', $filter) && !$filter['with_expired']) {
                    if (isset($filters['meta'])) {
                        array_push($filters['meta'], [
                            'key' => 'expired_at',
                            'value' => date("Y-m-d H:i:s"),
                            'compare' => '>',
                            'type' => "DATE"
                        ]);
                    } else {
                        $filters['meta'] = [
                            "relation" => "AND",
                            [
                                'key' => 'expired_at',
                                'value' => date("Y-m-d H:i:s"),
                                'compare' => '>',
                                'type' => "DATE"
                            ],
                        ];
                    }
                }

                if (array_key_exists('with_rejected', $filter) && !$filter['with_rejected']) {
                    if (isset($filters['taxonomy'])) {
                        array_push($filters['taxonomy'], [
                            'taxonomy' => 'status',
                            'field'    => 'slug',
                            'terms'    => 'open',
                            'compare'  => 'IN'
                        ]);
                    } else {
                        $filters['taxonomy'] = [
                            "relation" => "AND",
                            [
                                'taxonomy' => 'status',
                                'field'    => 'slug',
                                'terms'    => 'open',
                                'compare'  => 'IN'
                            ],
                        ];
                    }
                }
            }

            $vacancies      = $vacancyModel->getVacancies($filters, []);
            switch (strtolower($result)) {
                case 'options':
                case 'option-value':
                    $optionValue    = [];
                    if ($vacancies && $vacancies->found_posts > 0) {
                        foreach ($vacancies->posts as $post) {
                            $optionValue[$post->ID] = $post->post_title;
                        }
                    }

                    return $optionValue;
                    break;
                case 'count':
                    return $vacancies->found_posts;
                    break;
                default:
                    return $vacancies->posts;
                    break;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
