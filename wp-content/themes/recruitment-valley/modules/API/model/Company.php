<?php

namespace Model;

use Vacancy\Vacancy;
use WP_Query;

class Company
{
    public $user_id;
    public $user;
    public $vacancyModel;

    private $dateOfBirth = "ucma_date_of_birth";
    private $phone = "ucma_phone";
    private $phoneCode = "ucma_phone_code";
    private $country = "ucma_country";
    private $city = "ucma_city";
    private $isFullRegistered = "ucma_is_full_registered";
    private $image = "ucma_image";
    private $name = "ucma_company_name";
    private $description = "ucma_short_decription";
    private $totalEmployee = "ucma_employees";
    private $website = "ucma_website_url";

    private $facebook = "ucma_facebook_url";
    private $twitter = "ucma_twitter_url";
    private $instagram = "ucma_instagram_url";
    private $linkedin = "ucma_linkedin_url";

    public function __construct($userId = false)
    {
        $this->vacancyModel = new Vacancy;
        if($userId)
        {
            $this->user_id = $userId;
            $this->user = get_user_by('id', $this->user_id);
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

    public function getName()
    {
        return $this->getProp($this->name);
    }

    public function getDescription()
    {
        return  $this->getProp($this->description);
    }

    public function getPhone()
    {
        return  $this->getProp($this->phone);
    }

    public function getPhoneCode()
    {
        return $this->getProp($this->phoneCode);
    }

    public function getEmail()
    {
        return $this->user->user_email;
    }

    public function getTotalEmployees()
    {
        return $this->getProp($this->totalEmployee);
    }

    public function getFacebook()
    {
        return $this->getProp($this->facebook);
    }

    public function getTwitter()
    {
        return $this->getProp($this->twitter);
    }
    public function getLinkedin()
    {
        return $this->getProp($this->linkedin);
    }
    public function getInstagram()
    {
        return $this->getProp($this->instagram);
    }

    public function getWebsite()
    {
        return $this->getProp($this->website);
    }

    public function getProp( $acf_field, $single = false)
    {
        return get_field($acf_field, "user_".$this->user_id, $single);
    }

}