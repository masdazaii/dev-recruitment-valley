<?php

namespace PostType;

class Transaction extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterTransactionCPT']);
    }

    public function RegisterTransactionCPT()
    {
        $title = __('Transactions', THEME_DOMAIN);
        $slug = 'transaction';
        $args = [
            'menu_position' => 10,
            'publicly_queryable' => false,
            'has_archive' => false,
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);

        $taxonomies = [
            [
                "name" => "payment_status",
                "arguments" => [
                    'label' => __("Payment Status", THEME_DOMAIN),
                ]
            ],
        ];

        foreach ($taxonomies as $key => $taxonomy) {
            $this->taxonomy($slug, $taxonomy["name"], $taxonomy["arguments"]);
        }
    }
}

new Transaction();
