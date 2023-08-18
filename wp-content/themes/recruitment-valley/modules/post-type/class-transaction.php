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
            'supports' => array('title', 'editor', 'author', 'thumbnail')
        ];

        $this->customPostType($title, $slug, $args);
    }
}

new Transaction();
