<?php

namespace PostType;

defined('ABSPATH') or die('Direct access not allowed!');

class FaqCPT extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterFAQCPT']);
    }

    public function RegisterFAQCPT()
    {
        $title  = __('FAQ', THEME_DOMAIN);
        $slug   = 'faq';
        $args   = [
            'has_archive' => false,
            'publicly_queryable' => false,
            'menu_position' => 5,
            'supports'      => array('title', 'editor', 'author', 'thumbnail'),
        ];

        $this->customPostType($title, $slug, $args);
    }
}

// Initialize
new FaqCPT();
