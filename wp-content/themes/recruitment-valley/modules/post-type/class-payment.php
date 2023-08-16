<?php

namespace PostType;

class Payment extends RegisterCPT
{
    public function __construct()
    {
        add_action('init', [$this, 'RegisterPaymentCPT']);
    }

    public function RegisterPaymentCPT()
    {
        $title = __('Payments', THEME_DOMAIN);
        $slug = 'payment';
        $args = [
            'menu_position' => 6
        ];

        $this->customPostType($title, $slug, $args);
    }
}

new Payment();
