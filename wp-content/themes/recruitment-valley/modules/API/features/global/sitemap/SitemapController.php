<?php

namespace Sitemap;

use WP_Post;
use Vacancy\Vacancy;
use Constant\Message;
use Model\Company;
use Helper\DateHelper;
use WP_User_Query;

class SitemapController
{
    private $_message;

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function vacancy()
    {
        $vacancy = new Vacancy;
        $vacancySlugs = $vacancy->allSlug();

        $vacancySlugs = array_map(function (WP_Post $vacancy) {
            return $vacancy->post_name;
        }, $vacancySlugs);

        return [
            "status" => 200,
            "message" => "success get vacancy sitempa",
            "data" => $vacancySlugs
        ];
    }

    public function getVacancy($request, $response = 'response')
    {
        $filters = [
            'orderBy'   => 'date',
            'sort'      => ($request['orderBy'] && $request['orderBy'] == 'title' ? 'asc' : 'desc'),
            'page'      => array_key_exists('perPage', $request) && is_numeric($request['page']) ? (int)$request['page'] : 1,
            'postPerPage'   => array_key_exists('perPage', $request) && is_numeric($request['perPage']) ? (int)$request['perPage'] : 10,
        ];

        // Order By
        if (isset($request['orderBy'])) {
            switch ($request['orderBy']) {
                case 'id':
                    $filters['orderBy'] = 'ID';
                    break;
                case 'title':
                    $filters['orderBy'] = 'title';
                    break;
                case 'date':
                case 'post_date':
                    $filters['orderBy'] = 'date';
                    break;
                default:
                    $filters['orderBy'] = 'date';
            }
        }

        // Sort
        if (isset($request['sort'])) {
            switch (strtolower($request['sort'])) {
                case 'asc':
                case 'desc':
                    $filters['sort'] = $request['sort'];
                    break;
                default:
                    $filters['sort'] = ($request['orderBy'] && $request['orderBy'] == 'title' ? 'asc' : 'desc');
            }
        }

        $filters['offset'] = $filters['page'] <= 1 ? 0 : (((int)$filters['page'] - 1) * (int)$filters['postPerPage']);

        $vacancy = new Vacancy;
        $vacancies = $vacancy->getAllVacancies($filters);

        $response = [];

        if ($vacancies) {
            foreach ($vacancies->posts as $key => $values) {
                $vacancy    = new vacancy($values->ID);
                $response[] = [
                    "title"         => $vacancy->getTitle(),
                    "description"   => $vacancy->getDescription(),
                    "url"           => '/vacatures/' . $values->post_name,
                ];
            }
        }

        if ($response == 'response') {
            return [
                'status'    => 200,
                'message'   => $this->_message->get('sitemap.show_companies_success'),
                'data'      => $response
            ];
        } else {
            return [
                'page'  => (int)$filters['page'],
                'perPage'  => (int)$filters['postPerPage'],
                'total' => (int)$vacancies->found_posts ?? 0,
                'totalPage' => (int)$vacancies->max_num_pages ?? 0,
                'data'  => array_values($response)
            ];
        }
    }

    public function getCompanies($request, $response = 'response')
    {
        $filters = [
            'orderBy'   => 'name',
            'sort'      => 'ASC',
            'page'      => array_key_exists('perPage', $request) && is_numeric($request['page']) ? (int)$request['page'] : 1,
            'perPage'   => array_key_exists('perPage', $request) && is_numeric($request['perPage']) ? (int)$request['perPage'] : 10,
        ];

        // Order By
        if (isset($request['orderBy'])) {
            switch ($request['orderBy']) {
                case 'name':
                case 'display_name':
                    $filters['orderBy'] = 'name';
                    break;
                case 'user':
                case 'id':
                    $filters['orderBy'] = 'ID';
                    break;
                case 'user_registered':
                case 'registered':
                    $filters['orderBy'] = 'user_registered';
                    break;
                default:
                    $filters['orderBy'] = 'name';
            }
        }

        // Sort
        if (isset($request['sort'])) {
            switch (strtolower($request['sort'])) {
                case 'asc':
                case 'desc':
                    $filters['sort'] = $request['sort'];
                    break;
                default:
                    $filters['sort'] = 'ASC';
            }
        }

        $filters['offset'] = $filters['page'] <= 1 ? 0 : (((int)$filters['page'] - 1) * (int)$filters['perPage']);

        $companies = new WP_User_Query([
            'role'      => ['company'],
            'orderby'   => $filters['orderBy'],
            'sort'      => $filters['sort'],
            'paged'     => $filters['page'],
            'number'    => $filters['perPage'],
            'offset'    => $filters['offset'],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'relation' => 'OR',
                    [
                        'key'   => 'is_deleted',
                        'value' => false,
                        'compare' => '='
                    ],
                    [
                        'key'   => 'is_deleted',
                        'compare' => 'NOT EXISTS'
                    ],
                ],
                [
                    'key'   => 'ucma_is_full_registered',
                    'compare' => true,
                    'compare' => '='
                ]
            ]
        ]);

        $response = [];

        foreach ($companies->get_results() as $key => $values) {
            $company    = new Company($values->ID);
            $response[] = [
                "title"         => $company->getName(),
                "description"   => $company->getDescription(),
                "url"           => null,
            ];
        }

        if ($response == 'response') {
            return [
                'status'    => 200,
                'message'   => $this->_message->get('sitemap.show_companies_success'),
                'data'      => $response
            ];
        } else {
            return [
                'page'  => (int)$filters['page'],
                'perPage'  => (int)$filters['perPage'],
                'total' => (int)$companies->get_total(),
                'totalPage' => (int)$companies->get_pages() ?? 0,
                'data'  => array_values($response)
            ];
        }
    }

    /**
     * Get all sitemaps controller
     *
     * @param WP_REST_Request $request
     * @return array
     */
    public function get($request)
    {
        $companies = $this->getCompanies($request, 'array');
        $vacancies = $this->getVacancy($request, 'array');

        return [
            'status'    => 200,
            'message'   => $this->_message->get('sitemap.get_success'),
            'data'      => [
                [
                    'label' => 'Vacancy',
                    'count' => $vacancies['total'],
                    'data'  => $vacancies['data'],
                    'meta'  => [
                        'page' => $vacancies['page'],
                        'perPage' => $vacancies['perPage'],
                        'totalPage' => $vacancies['totalPage']
                    ],
                    'sortParams' => [
                        [
                            'label' => 'Title',
                            'value' => 'title'
                        ],
                        [
                            'label' => 'Post Date',
                            'value' => 'date'
                        ]
                    ]
                ],
                [
                    'label' => 'Company',
                    'count' => $companies['total'],
                    'data'  => $companies['data'],
                    'meta'  => [
                        'page' => $companies['page'],
                        'perPage' => $companies['perPage'],
                        'totalPage' => $vacancies['totalPage']
                    ],
                    'sortParams' => [
                        [
                            'label' => 'Company Name',
                            'value' => 'name'
                        ],
                        [
                            'label' => 'Registered Date',
                            'value' => 'registered'
                        ]
                    ]
                ],
                // [
                //     'label' => 'Blog',
                //     'count' => 0,
                //     'data'  => [],
                //     'meta'  => [
                //         'page' => (int)$request['page'],
                //         'perPage' => (int)$request['perPage']
                //     ]
                // ],
                // [
                //     'label' => 'Event',
                //     'count' => 0,
                //     'data'  => [],
                //     'meta'  => [
                //         'page' => (int)$request['page'],
                //         'perPage' => (int)$request['perPage']
                //     ]
                // ]
            ]
        ];
    }
}
