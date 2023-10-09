<?php

namespace Model;

use Exception;
use WP_Query;

class Coupon
{
    public $couponID;
    private $_couponDiscountValue;
    private $_couponDiscountType;
    private $_couponCode;

    public function __construct($coupon_id)
    {
        if ($coupon_id) {
            $this->couponID = $coupon_id;
        }
    }

    /**
     * Setter function
     *
     * Set acf / meta / post data
     *
     * @return mixed
     */
    public function set($key, $value, $type = 'acf'): mixed
    {
        return  update_field($key, $value, $this->_couponCode);
    }

    public function get($key, $value, $type = 'acf')
    {
    }
}
