<?php

namespace PostType;

defined('ABSPATH') || die("Direct access not allowed");

class ApplicantCPT extends RegisterCPT
{
    protected $slugCPT;

    public function __construct()
    {
        $this->slugCPT = 'applicants';

        add_action('init', [$this, 'applicantCreateCPT']);
    }

    public function applicantCreateCPT()
    {
        $additionalArgs = [
            'menu_posisiton' => 5,
            'publicly_queryable' => false,
            'has_archive' => true,
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true
        ];

        $this->customPostType('Applicant', $this->slugCPT, $additionalArgs);
    }
}

// Initiate
new ApplicantCPT();
