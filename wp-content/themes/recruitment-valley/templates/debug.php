<?php

use Model\Notification;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Template Name: Debugging
 */


$notification = new Notification(1);
echo '<pre>';
var_dump($notification->getReadStatus());
echo '</pre>';die;
?>
