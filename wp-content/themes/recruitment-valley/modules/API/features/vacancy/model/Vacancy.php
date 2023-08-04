<?php

namespace Vacancy;

class Vacancy
{
    public $vacancy = 'vacancy';

    public $vacancy_id;

    public $title;

    public $description;
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

    // acf field
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

    public $vacancy_property = [
        "description",
        "term",
        "apply_from_this_platform",
        "location",
        "application_process_title",
        "application_process_description",
        "application_process_step",
        "video_url",
        "facebook_url",
        "linkedin_url",
        "instagram_url",
        "twitter_url",
        "gallery",
        "reviews",
    ];

    public function getAcfProperties()
    {
        $vacancy_id = $this->vacancy_id;

        $properties = get_field_objects($vacancy_id);

        $formatted_acf = [];

        foreach ($this->vacancy_property as $key => $property) {
            if(isset($properties[$property]))
            {
                $formatted_acf[$property] = $properties[$property]["value"];
            }
        }

        return $formatted_acf;
    }

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

    public function setReviews($reviews)
    {
        $this->reviews = $reviews;
    }

    // Getter methods
    public function getDescription()
    {
        return $this->description;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getApplyFromThisPlatform()
    {
        return $this->apply_from_this_platform;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getApplicationProcessTitle()
    {
        return $this->application_process_title;
    }

    public function getApplicationProcessDescription()
    {
        return $this->application_process_description;
    }

    public function getApplicationProcessStep()
    {
        return $this->application_process_step;
    }

    public function getVideoUrl()
    {
        return $this->video_url;
    }

    public function getFacebookUrl()
    {
        return $this->facebook_url;
    }

    public function getLinkedinUrl()
    {
        return $this->linkedin_url;
    }

    public function getInstagramUrl()
    {
        return $this->instagram_url;
    }

    public function getTwitterUrl()
    {
        return $this->twitter_url;
    }

    public function getGallery()
    {
        return $this->gallery;
    }

    public function getReviews()
    {
        return $this->reviews;
    }

    public function updateField()
    {
        
    }

    public function getPropeties()
    {
        return get_object_vars($this);
    }

    // public function save()
    // {
    //     get_object_vars();
    // }
}