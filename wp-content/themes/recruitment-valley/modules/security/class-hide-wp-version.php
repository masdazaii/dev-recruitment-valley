<?php
/**
 * Hiding WP Version
 *
 * @package BornDigital
 */

namespace BD\Security;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Hide WP Version class
 */
class Hide_WP_Version {
	/**
	 * Setup the flow
	 */
	public function __construct() {
		add_filter( 'the_generator', [ $this, 'remove_version_generator' ] );
		add_filter( 'style_loader_src', [ $this, 'remove_version_from_style_js' ] );
		add_filter( 'script_loader_src', [ $this, 'remove_version_from_style_js' ] );
	}

	/**
	 * Pick out the version number from scripts and styles
	 *
	 * @param string $src source.
	 */
	public function remove_version_from_style_js( $src ) {
		if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;
	}

	/**
	 * Remove version generator
	 */
	public function remove_version_generator() {
		return '';
	}
}

new Hide_WP_Version();
