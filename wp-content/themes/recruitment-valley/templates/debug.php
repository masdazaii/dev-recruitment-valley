<?php

use Integration\ActiveCampaign\ActiveCampaign;
use Model\Coupon;
use Model\Notification;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Template Name: Debugging
 */

$activeCampaign = new ActiveCampaign();
$data = [
   "firstName" => "anggit",
   "lastName" => "prayoga", 
   "email" => "Anggitp@gmail.com",
   "phone" => "09409934128"
];

?>