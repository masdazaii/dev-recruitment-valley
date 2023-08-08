<?php

namespace Vacancy;

use WP_Post;

class Vacancy
{
    public $vacancy = 'vacancy';

    public $vacancy_id;

    public $title;

    public $description;
    public $city;
    public $country;
    public $term;
    public $apply_from_this_platform;
    public $location;
    public $application_process_title;
    public $application_process_description;
    public $application_process_step;
    public $video_url;
    public $facebook_url;
    public $linkedin_url;
    public $instagram_url;
    public $twitter_url;
    public $gallery;
    public $reviews;
    public $is_paid;
    public $salary_start;
    public $salary_end;

    public $thumbnail;
    public $desscription;

    // acf field
    public $acf_description = "description";
    public $acf_term = "term";
    public $acf_is_paid = "is_paid";
    public $acf_apply_from_this_platform = "apply_from_this_platform";
    public $acf_application_process_title = "application_process_title" ;
    public $acf_application_process_description = "application_process_description";
    public $acf_application_process_step = "application_process_step";
    public $acf_video_url = "video_url";
    public $acf_facebook_url = "facebook_url";
    public $acf_linkedin_url = "linkedin_url";
    public $acf_instagram_url = "instagram_url";
    public $acf_twitter_url = "twitter_url";
    public $acf_gallery = "gallery";
    public $acf_reviews = "reviews";
    public $acf_city = "city";
    public $acf_country = "country";
    public $acf_salary_start = "salary_start";
    public $acf_salary_end = "salary_end";
    public $acf_external_url = "external_url";

    public function __construct( $vacancy_id = false )
    {
        if($vacancy_id)
        {
            $this->vacancy_id = $vacancy_id;
        }
    }

    // Setter methods
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setTitle($term)
    {
        $this->term = $term;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function setApplyFromThisPlatform($apply_from_this_platform)
    {
        $this->apply_from_this_platform = $apply_from_this_platform;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function setApplicationProcessTitle($application_process_title)
    {
        $this->application_process_title = $application_process_title;
    }

    public function setApplicationProcessDescription($application_process_description)
    {
        $this->application_process_description = $application_process_description;
    }

    public function setApplicationProcessStep($application_process_step)
    {
        $this->application_process_step = $application_process_step;
    }

    public function setVideoUrl($video_url)
    {
        $this->video_url = $video_url;
    }

    public function setFacebookUrl($facebook_url)
    {
        $this->facebook_url = $facebook_url;
    }

    public function setLinkedinUrl($linkedin_url)
    {
        $this->linkedin_url = $linkedin_url;
    }

    public function setInstagramUrl($instagram_url)
    {
        $this->instagram_url = $instagram_url;
    }

    public function setTwitterUrl($twitter_url)
    {
        $this->twitter_url = $twitter_url;
    }

    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
    }

    // public function setRole($roles)
    // {
    //     $this->setTaxonomy($roles, 'role');
    // }

    // public function setSector($sectores)
    // {
    //     $this->setTaxonomy($sectores, 'sector');
    // }

    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
    }

    public function setTaxonomy($taxonomies )
    {
        foreach ($taxonomies as $taxonomy => $terms) {
            wp_set_post_terms($this->vacancy_id, $terms, $taxonomy);
        }
    }

    // Getter methods
    public function getDescription()
    {
        return $this->getProp($this->acf_description);
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getTerm()
    {
        return $this->getProp($this->term);
    }

    public function getIsPaid()
    {
        return $this->getProp($this->acf_is_paid);
    }

    public function getApplyFromThisPlatform()
    {
        return $this->getProp($this->acf_apply_from_this_platform);
    }

    public function getApplicationProcessTitle()
    {
        return $this->getProp($this->acf_application_process_title);
    }

    public function getApplicationProcessDescription()
    {
        return $this->getProp($this->acf_application_process_description);
    }

    public function getApplicationProcessStep()
    {
        return $this->getProp($this->acf_application_process_step);
    }

    public function getVideoUrl()
    {
        return $this->getProp($this->acf_video_url);
    }

    public function getFacebookUrl()
    {
        return $this->getProp($this->acf_facebook_url);
    }

    public function getLinkedinUrl()
    {
        return $this->getProp($this->acf_linkedin_url);
    }

    public function getInstagramUrl()
    {
        return $this->getProp($this->acf_instagram_url);
    }

    public function getTwitterUrl()
    {
        return $this->getProp($this->acf_twitter_url);
    }

    public function getGallery()
    {
        return $this->getProp($this->acf_gallery);
    }

    public function getCity()
    {
        return $this->getProp($this->acf_city);
    }

    public function getCountry()
    {
        return $this->getProp($this->acf_country);
    }

    public function getReviews()
    {
        return $this->getProp($this->acf_reviews);
    }

    public function getSalaryStart()
    {
        return $this->getProp($this->acf_salary_start);
    }

    public function getSalaryEnd()
    {
        return $this->getProp($this->acf_salary_end);
    }

    public function setProp($acf_field, $value)
    {
        return update_field($acf_field, $value, $this->vacancy_id );
    } 

    public function getProp( $acf_field, $single = true )
    {
        return get_field($acf_field, $this->vacancy_id, $single);
    }

    public function getThumbnail()
    {
        return wp_get_attachment_image_src( get_post_thumbnail_id( $this->vacancy_id ), "thumbnail")[0] ?? "";
    }

    public function getPropeties()
    {
        return get_object_vars($this);
    }

    public function getTaxonomy( $formatted = false )
    {
        $taxonomies = get_post_taxonomies($this->vacancy_id);

        $taxes = wp_get_object_terms(
            $this->vacancy_id,
            $taxonomies,
            [
                "hide_empty" => false,
            ]
        );

        $groupedTax = [];

        foreach($taxes as $tax)
        {
            $tempTax = $tax->taxonomy;
            $taxField = [
                "id" => $tax->term_id,
                "name" => $tax->name
            ];

            if($formatted)
            {
                if(isset($groupedTax[$tempTax]))
                {
                    array_push($groupedTax[$tempTax], $taxField);
                }else{
                    $groupedTax[$tempTax] = [$taxField];
                }
            }else{
                array_push($groupedTax, $taxField);
            }
        }

        return $groupedTax;
    }

    public function storePost( $payload)
    {
        $args = [
            "post_title" => $payload["title"],
            "post_author" => $payload["user_id"],
            "post_type" => $this->vacancy,
            "post_status" => "publish"
        ];

        $vacancy = wp_insert_post($args);
        
        $this->vacancy_id = $vacancy;

        return $vacancy;
    }
}