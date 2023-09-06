<?php
defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Template Name: Debugging
 */

use JobAlert\Data;

$data = new Data();
$monthly = $data->main('monthly');

echo '<pre>';
var_dump($monthly);
echo '</pre>';
die;

?>
