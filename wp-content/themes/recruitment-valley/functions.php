<?php
/**
 * Setting up theme
 *
 * @package BornDigital
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

require_once __DIR__ . '/setup/class-theme.php';

/**
 * The main function responsible for returning the one true theme Instance to functions everywhere.
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * @since 1.0.0
 * @return object main object of theme
 */
function setup_theme() {
	global $bd_theme;

	if ( ! isset( $bd_theme ) ) {
		$bd_theme = new Theme();
		$bd_theme->initialize();
	}

	return $bd_theme;
}

// initialize!
setup_theme();

ini_set('error_log', ABSPATH . '/wp-content' . '/log/' . date('Y-m-d') . '.log');
