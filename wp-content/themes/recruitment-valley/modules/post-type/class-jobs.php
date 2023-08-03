<?php

class Job extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterJobCPT']);
    }

    public function RegisterJobCPT()
    {
        $title = __('Jobs', THEME_DOMAIN);
        $slug = 'job';
        $args = [
            'menu_position' => 5
        ];

        $this->customPostType( $title, $slug, $args);

        $sector_taxonomy = "sector";
        $args = [
            'label' => __("Sector", THEME_DOMAIN),
        ];

        $taxonomies = [
            [
                "name" => "sector",
                "arguments" => [
                    'label' => __("Sector", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "role",
                "arguments" => [
                    'label' => __("Role", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "type",
                "arguments" => [
                    'label' => __("Type", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "education",
                "arguments" => [
                    'label' => __("Education", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "working-hours",
                "arguments" => [
                    'label' => __("Working Hours", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "status",
                "arguments" => [
                    'label' => __("Status", THEME_DOMAIN),
                ]
            ],
        ];

        foreach ($taxonomies as $key => $taxonomy) {
            $this->taxonomy($slug, $taxonomy["name"], $taxonomy["arguments"]);
        }
    }
}