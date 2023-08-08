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

    public function createFreeJob($request)
    {
        $vacancy = new Vacancy;

        $vacancy->setTitle($request["title"]);
        $vacancy->setDescription($request["description"]);
        $vacancy->setApplyFromThisPlatform($request["apply_from_this_platform"]);
        // $vacancy = new Vacancy();
        // return [
        //     "status" => 200,
        //     "data" => $vacancy->getPropeties()
        // ];



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
            'hoursPerWeek'  => array_key_exists('hoursPerWeek', $request) ? explode(',', $request['hoursPerWeek']) : NULL,
            'employmentType' => array_key_exists('employmentType', $request) ? explode(',', $request['employmentType']) : NULL,
            'location'       => array_key_exists('location', $request) ? explode(',', $request['location']) : NULL,
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
                        "relation" => 'AND'
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
            if (!array_key_exists('meta_query', $args)) {
                $args['meta_query'] = [
                    "relation" => 'OR'
                ];
            }

            // If salaryStart and salaryEnd exist + more than 0
            if ($filters['salaryStart'] && $filters['salaryEnd'] && $filters['salaryEnd'] > 0) {
                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => '_salary_start',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                    [
                        'key' => '_salary_end',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ],
                ]);
                array_push($args['meta_query'], [
                    'relation' => 'AND',
                    [
                        'key' => '_salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ],
                    [
                        'key' => '_salary_end',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ],
                ]);
            } else if ($filters['salaryStart'] || $filters['salaryEnd']) { // if only one of them is filled
                if ($filters['salaryStart'] && !isset($filters['salaryEnd'])) { // if start is filled but other is empty
                    array_push($args['meta_query'], [
                        'key' => '_salary_start',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => '_salary_end',
                        'value' => $filters['salaryStart'],
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ]);
                } else { // vice versa
                    array_push($args['meta_query'], [
                        'key' => '_salary_start',
                        'value' => $filters['salaryEnd'],
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ]);
                    array_push($args['meta_query'], [
                        'key' => '_salary_end',
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
            'message' => 'Success',
            'data'    => get_posts($args),
            // 'args'    => $args,
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
}
