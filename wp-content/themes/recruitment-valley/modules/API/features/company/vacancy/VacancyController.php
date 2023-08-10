<?php

namespace Company\Vacancy;

use Constant\Message;
use Model\Company;
use Model\Term;
use Vacancy\Vacancy;
use WP_Query;
use WP_Term;

class VacancyController
{

    public $vacancyModel;
    public $termModel;
    public $company;
    private $_message;

    public function __construct()
    {
        $this->company = new Company;
        $this->vacancyModel = new Vacancy;
        $this->termModel = new Term;
        $this->_message = new Message;
    }

    public function getByStatus( $request )
    {
        $status = $request["status"];
        $vacancies = $this->vacancyModel->getByStatus( $status );
        return [
            "status" => 200,
            "data" => $vacancies,
            "message" => $this->_message->get("vacancy.get_all")
        ];
    }

    public function getTermCount( $request )
    {
        $this->company->setUserId($request["user_id"]);

        return [
            "status" => 200,
            "data" => [
                "open" => $this->company->getVacancyByStatus('open'),
                "close" => $this->company->getVacancyByStatus('close'),
                "declined" => $this->company->getVacancyByStatus('declined'),
                "processing" => $this->company->getVacancyByStatus('processing'),
            ],
        ];
    }

    public function getAll( $request )
    {
        $vacancy = new Vacancy;
        $filters = [
            'page' => $request['page'] ?? null,
            'postPerPage' => $request['perPage'] ?? null,
            'search' => $request['search'] ?? null,
        ];

        $taxonomyFilters = [
            'status'     => array_key_exists('status', $request) ? explode(',', $request['status']) : NULL,
        ];

        $offset = $filters['page'] <= 1 ? 0 : ((intval($filters['page']) - 1) * intval($filters['postPerPage']));

        $args = [
            "post_type" => $vacancy->vacancy,
            "post_author" => $request["user_id"],
            "posts_per_page" => $filters['postPerPage'],
            "offset" => $offset,
            "order" => "ASC",
            "post_status" => "publish",
        ];

        foreach ($taxonomyFilters as $key => $value) {
            if ($value && $value !== null && !empty($value)) {
                if (!array_key_exists('tax_query', $args)) {
                    $args['tax_query'] = [
                        "relation" => 'OR'
                    ];
                }

                array_push($args['tax_query'], [
                    'taxonomy' => $key,
                    'field'     => 'slug',
                    'terms'     => $value,
                    'compare'  => 'IN'
                ]);
            }
        }

        if (array_key_exists('search', $filters) && $filters['search'] !== '' && isset($filters['search'])) {
            $args['s'] = $filters['search'];
        }

        $vacancies = new WP_Query($args);

        return [
            'message' => $this->_message->get('vacancy.get_all'),
            'data'    => $vacancies->posts,
            'meta'    => [
                'currentPage' => $filters['page'],
                'totalPage' => $vacancies->max_num_pages
            ],
            'status'  => 200
        ];
    }

}