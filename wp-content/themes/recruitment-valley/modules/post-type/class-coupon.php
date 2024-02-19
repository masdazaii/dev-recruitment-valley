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
            'menu_posisiton' => 5,
            'publicly_queryable' => false,
            'has_archive' => false,
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true
        ];

        $this->customPostType('Coupons', $this->slugCPT, $additionalArgs);
    }
}

new Coupon;
