<?php
/**
 * Setting up enqueue
 *
 * @package BornDigital
 */

namespace BD_Theme\Setup;

/**
 * Enqueue class to setup assets enqueue
 */
class Enqueue {
	/**
	 * Setup the flow
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'editor_enqueue' ] );

		add_filter( 'style_loader_src', [ $this, 'support_autoversion' ] );
		add_filter( 'script_loader_src', [ $this, 'support_autoversion' ] );

		add_action( 'login_enqueue_scripts', [ $this, 'login_enqueue' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'theme_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'theme_scripts' ] );
	}

	/**
	 * Add autoversion support to style & script's "src"
	 *
	 * @param string $src Non-raw url from style/ script enqueue.
	 * @return string
	 */
	public function support_autoversion( $src ) {
		if ( strpos( $src, 'ver=auto' ) ) {
			$src = remove_query_arg( 'ver', $src );

			if ( false === strpos( $src, BASE_URL ) ) {
				return $src;
			}

			$dir = str_replace( BASE_URL, BASE_DIR, $src );

			if ( ! file_exists( $dir ) ) {
				$last_modifed = '0';
			} else {
				$last_modifed = date( 'YmdHis', filemtime( $dir ) );
			}

			$src = add_query_arg( 'ver', $last_modifed, $src );
		}

		return $src;
	}

	/**
	 * Enqueue all styles & scripts to adjust editor's content
	 *
	 * @return void
	 */
	public function editor_enqueue() {
		add_editor_style( 'assets/css/src/wp-editor.min.css' );
	}

	/**
	 * Enqueue all styles and scripts to enhance login screen
	 *
	 * @return void
	 */
	public function login_enqueue() {
		wp_enqueue_style(
			'style',
			THEME_URL . '/assets/css/dist/wp-login.min.css',
			[],
			'auto'
		);

		wp_enqueue_script(
			'loginjs',
			THEME_URL . '/assets/js/dist/wp-login.min.js',
			[ 'jquery' ],
			'auto',
			true
		);
	}

	/**
	 * Enqueue all styles and scripts to custom admin style and behaviour
	 *
	 * @return void
	 */
	public function admin_enqueue() {
		wp_enqueue_script(
			'bd-admin-js',
			THEME_URL . '/assets/js/dist/wp-admin.min.js',
			[ 'jquery' ],
			'auto',
			true
		);
	}

	/**
	 * Enqueue all style that must included to make theme work
	 *
	 * @return void
	 */
	public function theme_styles() {
		wp_enqueue_style(
			'style',
			THEME_URL . '/assets/css/dist/themes.min.css',
			[],
			'auto'
		);
	}

	/**
	 * Enqueue all scripts that must included to make theme work
	 *
	 * @return void
	 */
	public function theme_scripts() {
		wp_enqueue_script(
			'bd-vendor-js',
			THEME_URL . '/assets/js/dist/vendors.min.js',
			array(),
			'auto',
			true
		);

		wp_enqueue_script(
			'bd-theme-js',
			THEME_URL . '/assets/js/dist/themes.min.js',
			array(),
			'auto',
			true
		);

		$error_messages = [
			'required'    => __( 'This field is required. Please be sure to check.', 'themedomain' ),
			'email'       => __( 'Your E-mail address appears to be invalid. Please be sure to check.', 'themedomain' ),
			'number'      => __( 'You can enter only numbers in this field.', 'themedomain' ),
			'maxLength'   => __( 'Maximum {count} characters allowed!', 'themedomain' ),
			'minLength'   => __( 'Minimum {count} characters allowed!', 'themedomain' ),
			'maxChecked'  => __( 'Maximum {count} options allowed. Please be sure to check.', 'themedomain' ),
			'minChecked'  => __( 'Please select minimum {count} options.', 'themedomain' ),
			'maxSelected' => __( 'Maximum {count} selection allowed. Please be sure to check.', 'themedomain' ),
			'minSelected' => __( 'Minimum {count} selection allowed. Please be sure to check.', 'themedomain' ),
			'notEqual'    => __( 'Fields do not match. Please be sure to check.', 'themedomain' ),
			'different'   => __( 'Fields cannot be the same as each other', 'themedomain' ),
			'creditCard'  => __( 'Invalid credit card number. Please be sure to check.', 'themedomain' ),
		];

		wp_localize_script(
			'theme', 'themeObj', [
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'themeUrl'      => THEME_URL,
				'errorMessages' => $error_messages,
			]
		);

		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}
}

new Enqueue();
