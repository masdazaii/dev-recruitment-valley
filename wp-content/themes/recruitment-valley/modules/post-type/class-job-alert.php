<?php

namespace PostType;

class JobAlert extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterJobAlertCPT']);
    }

    public function RegisterJobAlertCPT()
    {
        $title = __('JobAlerts', THEME_DOMAIN);
        $slug = 'JobAlert';
        $args = [
            'has_archive' => false,
            'publicly_queryable' => false,
            'menu_position' => 7,
            'publicly_queryable' => false,
            'has_archive' => false,
            'supports' => ['title'],
        ];

        $this->customPostType($title, $slug, $args);
    }
}

new JobAlert();
