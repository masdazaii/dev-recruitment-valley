<?php
/**
 * Setting up widgets
 *
 * @package BornDigital
 */

namespace BD_Theme\Setup;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Widgets class to setup widgets
 */
class Widgets {
	/**
	 * Put the flow here
	 * add_action( 'widgets_init', [$this, 'widget_init'] );
	 */
	public function __construct() {

	}

	/**
	 * Provide some sidebars.
	 *
	 * @return void
	 */
	public function widget_init() {
		register_sidebar(
			[
				'name'          => esc_html__( 'Sidebar', 'themedomain' ),
				'id'            => 'sidebar-1',
				'description'   => esc_html__( 'Add widgets here.', 'themedomain' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			]
		);
	}
}

new Widgets();
