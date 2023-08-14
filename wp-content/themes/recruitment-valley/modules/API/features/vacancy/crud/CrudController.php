<?php

namespace Vacancy;

use Constant\Message;
use DateTimeImmutable;
use WP_Post;
use WP_Query;

class VacancyCrudController
{
    private $_posttype = 'vacancy';
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
        add_filter('posts_search', [$this, 'filterVacancySearch'], 10, 2);
    }

    public function show()
    {
    }

    /** CHANGE STARTS HERE */
    public function getAll($request)
    {
        $vacancy = new Vacancy;
        $filters = [
            'page' => isset($request['page']) ? intval($request['page']) : 1,
            'search' => $request['search'] ?? null,
            'city' => $request['city'] ?? null,
            'salaryStart' => $request['salaryStart'] ?? null,
            'salaryEnd' => $request['salaryEnd'] ?? null,
            'postPerPage' => $request['perPage'] ?? 10
        ];

        $taxonomyFilters = [
            'education'     => array_key_exists('education', $request) ? explode(',', $request['education']) : NULL,
            'role'          => array_key_exists('role', $request) ? explode(',', $request['role']) : NULL,
            'sector'        => array_key_exists('sector', $request) ? explode(',', $request['sector']) : NULL,
            'working-hours' => array_key_exists('hoursPerWeek', $request) ? explode(',', $request['hoursPerWeek']) : NULL,
            'type'          => array_key_exists('employmentType', $request) ? explode(',', $request['employmentType']) : NULL,
            'location'      => array_key_exists('location', $request) ? explode(',', $request['location']) : NULL,
        ];

        $offset = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

        $args = [
            "post_type" => $this->_posttype,
            "posts_per_page" => $filters['postPerPage'],
            "offset" => $offset,
            "order" => "ASC",
            "post_status" => "publish",
            'tax_query' => []
        ];

        /** Set tax query */
        foreach ($taxonomyFilters as $key => $value) {
            if ($value && $value !== null && !empty($value)) {
                if (!array_key_exists('tax_query', $args)) {
                    $args['tax_query'] = [
                        "relation" => 'OR'
                    ];
                }

                array_push($args['tax_query'], [
                    'taxonomy' => $key,
                    'field'     => 'term_id',
                    'terms'     => $value,
                    'compare'  => 'IN'
                ]);
            }
        }

        /** Set meta query */
        if (($filters['salaryStart'] !== '' && isset($filters['salaryStart'])) || ($filters['salaryEnd'] !== '' && isset($filters['salaryEnd']))) {
            // If salaryStart and salaryEnd exist + more than 0
            if ($filters['salaryStart'] && $filters['salaryEnd'] && $filters['salaryEnd'] > 0) {
                if (!array_key_exists('meta_query', $args)) {
                    $args['meta_query'] = [
                        "relation" => 'OR'
                    ];
                }

                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => 'salary_start',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                    [
                        'key' => 'salary_end',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ],
                ]);
                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => 'salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                    [
                        'key' => 'salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ],
                ]);
            } else if ($filters['salaryStart'] || $filters['salaryEnd']) { // if only one of them is filled
                if (!array_key_exists('meta_query', $args)) {
                    $args['meta_query'] = [
                        "relation" => 'AND'
                    ];
                }

                if ($filters['salaryStart'] && !isset($filters['salaryEnd'])) { // if start is filled but other is empty
                    array_push($args['meta_query'], [
                        'key' => 'salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => 'salary_end',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ]);
                } else { // vice versa
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
                        'compare' => '>=',
                    ]);
                }
            }
        }

        /** Search */
        if (array_key_exists('search', $filters) && $filters['search'] !== '' && isset($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        // only display open job status
        array_push($args['tax_query'], [
            'taxonomy' => 'status',
            'field'     => 'slug',
            'terms'     => 'open',
            'compare'  => 'IN'
        ]);

        // echo '<pre>';
        // var_dump($args);
        // echo '</pre>';die;

        // $vacancies = get_posts($args);
        $vacancies = new WP_Query($args);

        return [
            'message' => $this->_message->get('vacancy.get_all'),
            'data'    => $vacancies->posts,
            'meta'    => [
                'currentPage' => isset($filters['page']) ? intval($filters['page']) : 1,
                'totalPage' => $vacancies->max_num_pages,
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
            "description" => $request["description"],
            "salary_start" => $request["salaryStart"],
            "salary_end" => $request["salaryEnd"],
            "external_url" => $request["externalUrl"],
            "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
            "is_paid" => false,
            "user_id" => $request["user_id"],
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "status" => [31] // set free job become pending category
            ],
        ];

        try {
            $vacancyModel = new Vacancy;

            $vacancyModel->storePost($payload);
            $vacancyModel->setTaxonomy($payload["taxonomy"]);
            $vacancyModel->setProp($vacancyModel->acf_description, $payload["description"]);
            $vacancyModel->setProp($vacancyModel->acf_is_paid, $payload["is_paid"]);
            $vacancyModel->setProp($vacancyModel->acf_salary_start, $payload["salary_start"]);
            $vacancyModel->setProp($vacancyModel->acf_salary_end, $payload["salary_end"]);
            $vacancyModel->setProp($vacancyModel->acf_apply_from_this_platform, $payload["apply_from_this_platform"]);
            $vacancyModel->setProp($vacancyModel->acf_expired_at, date("Y-m-d H:i:s"));

            if ($payload["apply_from_this_platform"]) {
                $vacancyModel->setProp($vacancyModel->acf_external_url, $payload["external_url"]);
            }

            $expiredAt = new DateTimeImmutable();
            $expiredAt = $expiredAt->modify("+30 days")->format("Y-m-d H:i:s");

            $vacancyModel->setStatus('processing');
            $vacancyModel->setProp("expired_at", $expiredAt);

            return [
                "status" => 201,
                "message" => $this->_message->get("vacancy.create.free.success"),
            ];
        } catch (\Throwable $th) {
            return [
                "status" => 500,
                "message" => $this->_message->get("vacancy.create.fail"),
            ];
        } catch (\WP_Error $e) {
            return [
                "status" => 500,
                "message" => $this->_message->get("vacancy.create.fail"),
            ];
        }
    }

    public function createPaid($request)
    {
        $payload = [
            "title" => $request["name"],
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
            "taxonomy" => [
                "sector" => $request["sector"],
                "role" => $request["role"],
                "working-hours" => $request["workingHours"],
                "location" => $request["location"],
                "education" => $request["education"],
                "type" => $request["employmentType"],
                "status" => [32] // set free job become pending category
            ],
            "application_process_step" => $request["applicationProcedureSteps"],
        ];

        try {
            $vacancyModel = new Vacancy;
            $vacancyModel->storePost($payload);
            $vacancyModel->setTaxonomy($payload["taxonomy"]);

            foreach ($payload as $acf_field => $value) {
                if ($acf_field !== "taxonomy") {
                    $vacancyModel->setProp($acf_field, $value, is_array($value));
                }
            }

            $vacancyModel->setStatus('open');

            return [
                "status" => 201,
                "message" => $this->_message->get("vacancy.create.paid.success"),
            ];
        } catch (\Throwable $th) {
            return [
                "status" => 500,
                // "message" => $this->_message->get("vacancy.create.paid.fail"),
                "message" => $th->getMessage(),

            ];
        } catch (\WP_Error $e) {
            return [
                "status" => 500,
                "message" => $e->get_error_message(),
                // "message" => $this->_message->get("vacancy.create.paid.fail"),
            ];
        }
    }

    public function filterVacancySearch($search,  $query)
    {
        global $wpdb;

        if ($query->is_search && $query->get('post-type') == $this->_posttype) {
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

    public function update( $request )
    {
        $vacancy_id = $request["vacancy_id"];
        $vacancyModel = new Vacancy( $vacancy_id );

        $vacancyIsPaid = $vacancyModel->getIsPaid();

        $payload = $this->createVacancyPayload( $vacancyIsPaid, $request );

        $vacancyModel->setTaxonomy($payload["taxonomy"]);

        foreach ($payload as $acf_field => $value) {
            if ($acf_field !== "taxonomy") {
                $vacancyModel->setProp($acf_field, $value, is_array($value));
            }
        }

        return [
            "status" => 200,
            "message" => $vacancyIsPaid ? $this->_message->get("vacancy.update.paid.success") : $this->_message->get("vacancy.update.paid.fail")
        ];
    }

    public function trash( $request )
    {
        $vacancy = new Vacancy($request["vacancy_id"]);

        if($request["user_id"] != $vacancy->getAuthor())
        {
            return [
                "status" => 400,
                "message" => $this->_message->get("vacancy.trash.not_authorized")
            ];
        }

        $trashed = $vacancy->trash();

        if(is_wp_error($trashed)){
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

    private function createVacancyPayload( $isPaid, $request )
    {
        $payload = [];

        if($isPaid)
        {
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
                    "status" => [32] // set free job become pending category
                ],
                "application_process_step" => $request["applicationProcedureSteps"],
        ];
        }

        if(!$isPaid)
        {
            $payload = [
                "title" => $request["name"],
                "description" => $request["description"],
                "salary_start" => $request["salaryStart"],
                "salary_end" => $request["salaryEnd"],
                "external_url" => $request["externalUrl"],
                "apply_from_this_platform" => isset($request["externalUrl"]) ? true : false,
                "user_id" => $request["user_id"],
                "taxonomy" => [
                    "sector" => $request["sector"],
                    "role" => $request["role"],
                    "working-hours" => $request["workingHours"],
                    "location" => $request["location"],
                    "education" => $request["education"],
                    "type" => $request["employmentType"],
                    "status" => [31] // set free job become pending category
                ],
            ];
        }

        return $payload;
    } 
}
