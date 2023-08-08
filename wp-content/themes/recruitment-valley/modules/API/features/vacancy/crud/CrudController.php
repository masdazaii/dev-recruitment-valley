<?php

namespace Vacancy;

use Constant\Message;
use WP_Post;

class VacancyCrudController
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message;
    }

    public function show()
    {
    }

    /** Anggit Syntax start here */
    // public function getAll($request)
    // {
    //     $params = $request;

    //     $page = $params["page"];

    //     $search = $params["search"];

    //     $city = $params["city"];

    //     $education = $params["education"];

    //     $role = $params["role"];

    //     $sector = $params["sector"];

    //     $hoursPerWeek = $params["hoursPerWeek"];

    //     $salaryStart = $params["salaryStart"];

    //     $salaryEnd = $params["salaryEnd"];

    //     $vacancy = new Vacancy;

    //     $postsPerPage = $params["postPerPage"];

    //     $args = [
    //         "post_type" => "vacancy",
    //         "numberposts" => -1,
    //         "offset" => 10,
    //         "order" => "ASC",
    //         "post_status" => "publish",
    //         // "paged" => $page
    //     ];

    //     $vacancies = get_posts($args);

    //     if (count($vacancies) > 0) {
    //         return [
    //             "status" => 200,
    //             "message" => $this->_message->get("vacancy.get_all"),
    //             "data" => $vacancies
    //         ];
    //     } else {
    //         return [
    //             "status" => 404,
    //             "message" => $this->_message->get("vacancy.not_found"),
    //             "data" => $vacancies
    //         ];
    //     }
    // }

    /** CHANGE STARTS HERE */
    public function getAll($request)
    {
        $vacancy = new Vacancy;
        $filters = [
            'page' => $request['page'] ?? null,
            'search' => $request['search'] ?? null,
            'city' => $request['city'] ?? null,
            'salaryStart' => $request['salaryStart'] ?? null,
            'salaryEnd' => $request['salaryEnd'] ?? null,
            'postPerPage' => $request['perPage'] ?? null
        ];

        $taxonomyFilters = [
            'education'     => array_key_exists('education', $request) ? explode(',', $request['education']) : NULL,
            'role'          => array_key_exists('role', $request) ? explode(',', $request['role']) : NULL,
            'sector'        => array_key_exists('sector', $request) ? explode(',', $request['sector']) : NULL,
            'working-hours' => array_key_exists('hoursPerWeek', $request) ? explode(',', $request['hoursPerWeek']) : NULL,
            'type'          => array_key_exists('employmentType', $request) ? explode(',', $request['employmentType']) : NULL,
            'location'      => array_key_exists('location', $request) ? explode(',', $request['location']) : NULL,
        ];

        $offset = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']) + 1);

        $args = [
            "post_type" => "vacancy",
            "numberposts" => $filters['postPerPage'],
            "offset" => $offset,
            "order" => "ASC",
            "post_status" => "publish",
            // "paged" => $page,
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

        return [
            'message' => $this->_message->get('vacancy.get_all'),
            'data'    => get_posts($args),
            'args'    => $args,
            'meta'    => [
                'currentPage' => $filters['page'],
                'totalPage' => 10
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
            // "sector" => $request["sector"],
            // "role" => $request["role"],
            "description" => $request["description"],
            // "type" => $request["type"],
            // "location" => $request["location"],
            // "education" => $request["education"],
            // "workingHours" => $request["workingHours"],
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

            if ($payload["apply_from_this_platform"]) {
                $vacancyModel->setProp($vacancyModel->acf_external_url, $payload["external_url"]);
            }

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
    }
}
