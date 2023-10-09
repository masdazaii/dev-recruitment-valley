<?php

use Model\Coupon;
use Model\Notification;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Template Name: Debugging
 */


$notification = new Coupon(627);
echo '<pre>';
var_dump($notification->getProperties());
echo '</pre>';die;
?>
