<?php

namespace Sitemap;

use WP_Post;
use Vacancy\Vacancy;
use Constant\Message;
use Helper\DateHelper;

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

    public function getCompanies($request, $response = 'response')
    {
        $filters = [];

        // Order By
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

        // Sort
        switch (strtolower($request['sort'])) {
            case 'asc':
            case 'desc':
                $filters['sort'] = $request['sort'];
                break;
            default:
                $filters['sort'] = 'ASC';
        }

        $filters = [
            'page'      => array_key_exists('perPage', $request) && is_numeric($request['page']) ? (int)$request['page'] : 1,
            'perPage'   => array_key_exists('perPage', $request) && is_numeric($request['perPage']) ? (int)$request['perPage'] : 10,
        ];

        $filters['offset'] = $filters['page'] <= 1 ? 0 : (((int)$filters['page'] - 1) * (int)$filters['postPerPage']);

        $companies = get_users([
            'role'      => ['company'],
            'orderby'   => $filters['orderBy'],
            'sort'      => $filters['sort'],
            'meta_key'  => ['is_deleted'],
            'meta_value' => false,
            'paged'     => $filters['page'],
            'number'    => $filters['perPage'],
            'offset'    => $filters['offset']
        ]);

        if ($response == 'response') {
            return [
                'status'    => 200,
                'message'   => $this->_message->get('sitemap.show_companies_success'),
                'data'      => $companies
            ];
        } else {
            return $companies;
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
        return [
            'status'    => 200,
            'message'   => $this->_message->get('sitemap.get_success'),
            // 'data'      => [
            //     [
            //         $this->getCompanies($request)
            //     ]
            // ]
            'data' => []
        ];
    }
}
