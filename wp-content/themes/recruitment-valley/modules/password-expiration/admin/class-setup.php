<?php
/**
 * Setting up
 *
 * @package BornDigital
 */

namespace BD\Password\Expiration\Admin;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Setup
 */
final class Setup {

	/**
	 * Default limit for password age (in days).
	 *
	 * @var int
	 */
	public static $default_limit;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	/**
	 * Set property values and fire hooks.
	 *
	 * @action init
	 */
	public function init() {
		/**
		 * Filter the default age limit for passwords (in days)
		 * when the limit settings field is not set or empty.
		 *
		 * @return int
		 */
		self::$default_limit = absint( apply_filters( 'passexp_default_limit', 90 ) );

		add_action( 'user_register', array( __CLASS__, 'save_user_meta' ) );
		add_action( 'password_reset', array( __CLASS__, 'save_user_meta' ) );
		add_action( 'wp_login', array( $this, 'check_password_reset_meta' ), 10, 2 );

		if ( $this->is_enabled() ) {
			new Login_Screen();
		}

		if ( ! is_user_logged_in() ) {

			return;

		}

		new List_Table();
		new Settings();

	}

	/**
	 * Check whether password expiration feature is enabled or not
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		$options = (array) get_option( 'bd_passexp_settings', array() );
		return isset( $options['is_enabled'] ) && $options['is_enabled'] ? true : false;
	}

	/**
	 * Check password reset meta
	 *
	 * @param string $user_login user login.
	 * @param object $user user object.
	 */
	public function check_password_reset_meta( $user_login, $user ) {
		$user_meta = self::get_user_meta( $user );
		if ( ! $user_meta ) {
			self::save_user_meta( $user );
		}
	}

	/**
	 * Save password reset user meta to the database.
	 *
	 * @action user_register
	 * @action password_reset
	 *
	 * @param mixed $user (optional).
	 */
	public static function save_user_meta( $user = null ) {
		$user_id = self::get_user_id( $user );

		if ( false === $user_id ) {
			return;
		}

		// phpcs:ignore
		update_user_meta( $user_id, 'bd_password_reset', gmdate( 'U' ) );

	}

	/**
	 * Return password reset user meta from the database.
	 *
	 * @param  mixed $user (optional).
	 *
	 * @return mixed|false
	 */
	public static function get_user_meta( $user = null ) {
		$user_id = self::get_user_id( $user );

		if ( false === $user_id ) {
			return false;
		}

		// phpcs:ignore
		$value = get_user_meta( $user_id, 'bd_password_reset', true );

		return ( $value ) ? absint( $value ) : false;

	}

	/**
	 * Return the password age limit setting.
	 *
	 * A hard limit of 365 days is built into this plugin. If
	 * you want to require passwords to be reset less than once
	 * per year then you probably don't need this plugin. :-)
	 *
	 * @return int
	 */
	public static function get_limit() {

		$options = (array) get_option( 'bd_passexp_settings', array() );

		return ( empty( $options['limit'] ) || absint( $options['limit'] ) > 365 ) ? self::$default_limit : absint( $options['limit'] );

	}

	/**
	 * Return the array of expirable roles setting.
	 *
	 * @return array
	 */
	public static function get_roles() {

		$options = (array) get_option( 'bd_passexp_settings', array() );

		if ( ! empty( $options['roles'] ) ) {

			return array_keys( $options['roles'] );

		}

		if ( ! function_exists( 'get_editable_roles' ) ) {

			require_once ABSPATH . 'wp-admin/includes/user.php';

		}

		$roles = array_keys( get_editable_roles() );

		// return all roles except admins by default if not set.
		if ( isset( $roles['administrator'] ) ) {

			unset( $roles['administrator'] );

		}

		return $roles;

	}

	/**
	 * Return the password expiration date for a user.
	 *
	 * @param  mixed  $user        (optional).
	 * @param  string $date_format (optional).
	 *
	 * @return string|false
	 */
	public static function get_expiration( $user = null, $date_format = 'U' ) {
		$reset = self::get_user_meta( $user );

		if (
			! self::has_expirable_role( $user )
			||
			false === $reset
		) {

			return false;

		}

		$expires = strtotime( sprintf( '@%d + %d days', $reset, self::get_limit() ) );

		return gmdate( $date_format, $expires );

	}

	/**
	 * Determine if a user belongs to an expirable role defined in the settings.
	 *
	 * @param  mixed $user (optional).
	 *
	 * @return bool
	 */
	public static function has_expirable_role( $user = null ) {
		$user_id = self::get_user_id( $user );

		if ( false === $user_id ) {

			return false;

		}

		$user  = get_user_by( 'ID', $user_id );
		$roles = array_intersect( $user->roles, self::get_roles() );

		return empty( $user->roles[0] ) ? false : ! empty( $roles );

	}

	/**
	 * Determine if a user's password has exceeded the age limit.
	 *
	 * @param  mixed $user (optional).
	 *
	 * @return bool
	 */
	public static function is_expired( $user = null ) {
		$expires = self::get_expiration( $user );
		return ( false === $expires ) ? false : ( time() > $expires );
	}

	/**
	 * Return the user ID for a give user.
	 *
	 * @param  mixed $user user.
	 *
	 * @return int|false
	 */
	private static function get_user_id( $user = null ) {

		switch ( true ) {

			case is_numeric( $user ):
				$user_id = absint( $user );

				break;

			case is_a( $user, 'WP_User' ):
				$user_id = $user->ID;

				break;

			default:
				$user_id = get_current_user_id();

		}

		if ( ! get_user_by( 'ID', $user_id ) ) {

			return false;

		}

		return $user_id;

	}
}

new Setup();
