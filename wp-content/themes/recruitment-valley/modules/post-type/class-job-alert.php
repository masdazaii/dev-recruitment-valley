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
            'menu_position' => 7,
            'supports' => ['title'], 
        ];

        $this->customPostType($title, $slug, $args);
    }
}

new JobAlert();
