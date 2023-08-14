<?php

namespace Model;

use Vacancy\Vacancy;
use WP_Query;

class Company
{
    public $user_id;
    public $vacancyModel;

    public function __construct($userId = false)
    {
        $this->vacancyModel = new Vacancy;
        if($userId)
        {
            $this->user_id = $userId;
        }
    }

    public function setUserId( $userId )
    {
        $this->user_id = $userId;
    }

    public function getVacancyByStatus( $status )
    {
        $args = [
            "post_type" => $this->vacancyModel->vacancy,
            "post_author" => $this->user_id,
            "posts_per_page" => -1,
            "tax_query" => [
                [
                    'taxonomy' => 'status',
                    'field' => 'slug',
                    'terms' => array( $status ),
                    'operator' => 'IN'
                ],
            ],
        ];

        $vacancies = new WP_Query( $args );

        return $vacancies->found_posts;
    }


}