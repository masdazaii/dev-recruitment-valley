<?php

namespace PostType;

use DateTimeImmutable;
use Vacancy\Vacancy as VacancyModel;

class Vacancy extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterVacancyCPT']);
        add_action('save_post', [$this, 'setExpiredDate'], 10, 3);
    }

    public function RegisterVacancyCPT()
    {
        $title = __('Vacancies', THEME_DOMAIN);
        $slug = 'vacancy';
        $args = [
            'menu_position' => 5,
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);

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
            [
                "name" => "location",
                "arguments" => [
                    'label' => __("Location", THEME_DOMAIN),
                ]
            ],
            [
                "name" => "experiences",
                "arguments" => [
                    'label' => __("Experience", THEME_DOMAIN),
                ]
            ]
        ];

        foreach ($taxonomies as $key => $taxonomy) {
            $this->taxonomy($slug, $taxonomy["name"], $taxonomy["arguments"]);
        }
    }

    public function setExpiredDate($post_id, $post, $postBefore)
    {
        if ($post->post_type == 'vacancy') {
            /** 'open' is term slug, 'status' is taxonomy name */
            if (has_term('open', 'status', $post)) {
                if (!get_field('is_paid', $post_id, true)) {
                    error_log('free');
                    $vacancyModel = new VacancyModel($post_id);
                    $today = new DateTimeImmutable("now");
                    $vacancyModel->setProp($vacancyModel->acf_expired_at, $today->modify("+30 days")->format("Y-m-d H:i:s"));
                }
            }
        }
    }
}
new Vacancy();
