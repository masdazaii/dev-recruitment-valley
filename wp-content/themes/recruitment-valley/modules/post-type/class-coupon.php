<?php

namespace PostType;

class Coupon extends RegisterCPT
{
    protected $slugCPT;

    public function __construct()
    {
        $this->slugCPT = 'coupon';

        add_action('init', [$this, 'CreateCouponCPT']);
    }

    public function CreateCouponCPT()
    {
        $additionalArgs = [
            'publicly_queryable' => false,
            'menu_posisiton' => 5,
            'has_archive' => false,
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true
        ];

        $this->customPostType('Coupons', $this->slugCPT, $additionalArgs);
    }
}

new Coupon;