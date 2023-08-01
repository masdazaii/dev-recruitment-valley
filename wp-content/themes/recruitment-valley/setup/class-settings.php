<?php
/**
* Setting up theme settings
*
* @package BornDigital
*/

namespace BD_Theme\Setup;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
* Settings class to setup theme settings
*/
class Settings {

	/**
	* Setup the flow
	*/
	public function __construct() {
		// phpcs:ignore
		// add_action( 'init', [ $this, 'add_image_sizes' ] );
		add_filter( 'show_admin_bar', '__return_false' );
		add_action('init', [$this, 'cleanup_wp_unnecessary_scripts']);
	}

	/**
	* Adding image sizes
	*/
	public function add_image_sizes() {
		/**
		* You can add images size in here
		*
		* @link https://developer.wordpress.org/reference/functions/add_image_size/
		*/
		// adjust the sizes below according to your need
		$sizes = [
			[
				'name'   => "custom-size",
				'width'  => 400,
				'height' => 9999
			],
			[
				'name'   => "another-custom-size",
				'width'  => 300,
				'height' => 200,
				'crop'   => true
			]
		];

		if (!$sizes) {
			return;
		}

		foreach ($sizes as $size) {
			$name   = $size['name'];
			$width  = isset($size['width'])     ? $size['width']    : 9999;
			$height = isset($size['height'])    ? $size['height']   : 9999;
			$crop   = isset($size['crop'])      ? $size['crop']     : false;

			add_image_size($name, $width, $height, $crop);
		}
	}

	public function cleanup_wp_unnecessary_scripts()
	{
		if (!is_admin()) { wp_deregister_script('wp-embed'); }

		// REMOVE EMOJI ICONS
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');

		// EditURI link
		remove_action('wp_head', 'rsd_link');

		// Category feed links
		remove_action('wp_head', 'feed_links_extra', 3);

		// Post and comment feed links
		remove_action('wp_head', 'feed_links', 2);

		// Windows Live Writer
		remove_action('wp_head', 'wlwmanifest_link');

		// Index link
		remove_action('wp_head', 'index_rel_link');

		// Previous link
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);

		// Start link
		remove_action('wp_head', 'start_post_rel_link', 10, 0);

		// Canonical
		remove_action('wp_head', 'rel_canonical', 10, 0);

		// Shortlink
		remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

		// Links for adjacent posts
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

		// WP version
		remove_action('wp_head', 'wp_generator');

		// Wp Oembed
		remove_action('wp_head', 'wp_oembed_add_discovery_links');

		//REST API
		remove_action('wp_head', 'rest_output_link_wp_head');
	}

}

new Settings();
