<?php

use Model\Coupon;
use Model\Notification;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Template Name: Debugging
 */

 try {
    $notification = new Transaction(61);
    echo '<pre>';
    var_dump($notification->hasCoupon());
    echo '</pre>';die;
 } catch (Exception $th) {
    echo $th;
 }

?>
