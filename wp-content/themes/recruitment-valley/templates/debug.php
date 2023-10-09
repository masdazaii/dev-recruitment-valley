<?php

use Model\Coupon;
use Model\Notification;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Template Name: Debugging
 */


$notification = new Coupon(627);
echo '<pre>';
var_dump($notification->getCode());
var_dump($notification->getDescription());
var_dump($notification->getExpiredAt());
var_dump($notification->getDiscountType());
var_dump($notification->getDiscountValue());
var_dump($notification->getStatus());
echo '</pre>';die;
?>
