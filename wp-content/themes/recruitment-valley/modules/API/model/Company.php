<?php

namespace Model;

use Vacancy\Vacancy;
use WP_Query;

class Company
{
    public $user_id;
    public $vacancyModel;

    private $dateOfBirth = "ucma_date_of_birth";
    private $phone = "ucma_phone";
    private $phoneCode = "ucma_phone_code";
    private $country = "ucma_country";
    private $city = "ucma_city";
    private $linkedin = "ucma_linkedin_url_page";
    private $isFullRegistered = "ucma_is_full_registered";
    private $cv = "ucma_cv";
    private $image = "ucma_image";

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

    public function getThumbnail()
    {
        $attachment_id = $this->getProp($this->image); 
        return wp_get_attachment_url($attachment_id) ? wp_get_attachment_url($attachment_id) : null;
    }

    public function getProp( $acf_field, $single = false)
    {
        return get_field($acf_field, "user_".$this->user_id, $single);
    }

}