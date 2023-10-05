<?php
/**
 * Dashboard restrictions
 *
 * @package BornDigital
 */

namespace BD\Dashboard\Restrictions\Admin;

/**
 * Setting up dashboard restrictions
 */
class Restrict {
	/**
	 * Setup the flow
	 */
	public function __construct() {
		$this->add_option_page();
		add_action( 'admin_init', [ $this, 'restrict_by_role' ] );
		add_action( 'admin_init', [ $this, 'restrict_by_ip' ] );
	}

	/**
	 * Add option page
	 *
	 * @return void
	 */
	public function add_option_page() {
		if ( ! function_exists( 'acf_add_options_page' ) ) {
			return;
		}

		// add sub page.
		acf_add_options_page([
			'page_title'  => 'Dashboard Restrictions',
			'menu_title'  => 'Dashboard Restrictions',
			'menu_slug'   => 'dashboard-restrictions',
			'parent_slug' => 'options-general.php',
			'capability'  => 'manage_options',
		]);
	}

	/**
	 * Restrict dashboard by specific role(s)
	 *
	 * @return void
	 */
	public function restrict_by_role() {
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		$is_enabled = (bool) get_option( 'options_enable_role_restriction' );

		if ( ! $is_enabled ) {
			return;
		}

		require __DIR__ . '/by-role.php';
	}

	/**
	 * Restrict specific ip address to access the dashboard
	 *
	 * @return void
	 */
	public function restrict_by_ip() {
		// allow ajax request.
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		global $pagenow;

		$current_screen = get_current_screen();

		// allow comment deletion.
		if ( 'comment.php' === $pagenow && isset( $_GET['action'] ) && 'deletecomment' === $_GET['action'] ) {
			return;
		}

		$current_user = wp_get_current_user();

		$is_enabled = (bool) get_option( 'options_enable_ip_restriction' );

		if ( ! $is_enabled ) {
			return;
		}

		require __DIR__ . '/by-ip.php';
	}

	/**
	 * CIDR to Range
	 *
	 * @param ? $cidr ?.
	 */
	public function cidr_to_range( $cidr ) {
		$range    = array();
		$cidr     = explode( '/', $cidr );
		$range[0] = long2ip( ( ip2long( $cidr[0] ) ) & ( ( -1 << ( 32 - (int) $cidr[1] ) ) ) );
		$range[1] = long2ip( ( ip2long( $cidr[0] ) ) + pow( 2, ( 32 - (int) $cidr[1] ) ) - 1 );
		return $range;
	}

	/**
	 * Redirect
	 *
	 * @param string $to destination url.
	 */
	public function redirect( $to = '' ) {
		if ( ! $to ) {
			$to = get_site_url();
		}
		wp_safe_redirect( $to );
		exit;
	}

	/**
	 * Get user IP
	 *
	 * @link http://stackoverflow.com/a/41382472
	 * @return string user ip
	 */
	public function get_user_ip() {
		$ipaddress = '';

		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} elseif ( isset( $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'] ) ) {
			$ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = 'UNKNOWN';
		}

		return sanitize_text_field( $ipaddress );
	}

}

new Restrict();
