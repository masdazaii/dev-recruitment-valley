<?php

namespace PostType;

class Package extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterPackageCPT']);
    }

    public function RegisterPackageCPT()
    {
        $title = __('Packages', THEME_DOMAIN);
        $slug = 'package';
        $args = [
            'has_archive' => false,
            'publicly_queryable' => false,
            'menu_position' => 6
        ];

        $this->customPostType($title, $slug, $args);
    }
}

new Package();
