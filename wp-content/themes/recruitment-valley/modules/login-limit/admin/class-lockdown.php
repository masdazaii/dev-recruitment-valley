<?php
/**
 * Lockdown
 *
 * @package BornDigital
 */

namespace BD\Login\Limit\Admin;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Lockdown class
 */
class Lockdown {
	/**
	 * Setup the flow
	 */
	public function __construct() {
		add_filter( 'authenticate', array( $this, 'validate_authenticate' ), 100, 3 );
		add_action( 'init', array( $this, 'process_unlock_request' ), 0 );
	}

	/**
	 * Handle authentication steps (in case of failed login):
	 * - increment number of failed logins for $username
	 * - lock the user
	 * - display a generic error message
	 *
	 * @since 1.0.0
	 * @param  WP_User|WP_Error $user WP_User or WP_Error.
	 * @param  string           $username username.
	 * @param  string           $password password.
	 * @return WP_User|WP_Error
	 */
	public function validate_authenticate( $user, $username, $password ) {
		if ( 1 !== absint( Login_Limit::get_saved_settings( 'enable' ) ) ) {
			return $user;
		}

		global $wpdb;

		// check if user locked or not.
		$lockdowns_table_name = Login_Limit::get_setting( 'db_lockdowns' );
		$ip                   = Login_Limit::get_user_ip(); // get the IP address of user.
		$ip_range             = Login_Limit::get_ip_range( $ip );
		$now                  = current_time( 'mysql' );
		$locked_user          = self::current_locked_user();

		if ( $locked_user ) {
			if ( isset( $locked_user->whitelists_ip ) && ! empty( $locked_user->whitelists_ip ) ) {
				$whitelists_ip = ( isset( $locked_user->whitelists_ip ) ) ? (array) @unserialize( $locked_user->whitelists_ip ) : array();
				if ( ! in_array( $ip, $whitelists_ip ) ) {
					$this->_lock_the_login();
				}
			} else {
				$this->_lock_the_login();
			}
		}

		if (
			// Authentication has been successful, there's nothing to do here.
			! is_wp_error( $user )
			||
			// Neither log nor block login attempts with empty username or password.
			empty( $username ) || empty( $password )
		) {
			return $user;
		}

		// increment failed login.
		$userdata = is_email( $username ) ? get_user_by( 'email', $username ) : get_user_by( 'login', $username ); // returns WP_User object.
		$user_id  = ( isset( $userdata->ID ) && ! is_wp_error( $userdata ) ) ? $userdata->ID : 0;

		$this->_increment_failed_logins( $user_id );

		$max_login_attempts = Login_Limit::get_saved_settings( 'max_login_attempts' );
		$login_fail_count   = $this->_get_login_fail_count();
		$remaining_attempts = $max_login_attempts - $login_fail_count;

		if ( $remaining_attempts <= 0 ) {
			$this->_lock_the_user( $username, $ip_range, $ip, $user_id, 'login_fail' );
			$this->_lock_the_login();
		}

		$errors = new \WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid login credentials.', 'themedomain' ) );
		// translators: %d: number.
		$errors->add( 'remaining_attempts', sprintf( __( '%d attempts remaining', 'themedomain' ), $remaining_attempts ) );

		return $errors;

	}

	/**
	 * Lock the login with unlock field
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function _lock_the_login() {
		nocache_headers();
		remove_action( 'wp_head', 'head_addons', 7 );
		include_once __DIR__ . '/forms/unlock-request.php';
		die();
	}

	/**
	 * Adds an entry to the `login_fails` table.
	 *
	 * @since 1.0.0
	 * @param  integer $user_id user ID of the user.
	 * @return void
	 */
	private function _increment_failed_logins( $user_id = 0 ) {
		global $wpdb;
		$login_fails_table_name = Login_Limit::get_setting( 'db_login_fails' );
		$ip                     = Login_Limit::get_user_ip();

		$result = $wpdb->insert(
			$login_fails_table_name,
			array(
				'user_id'            => $user_id,
				'login_attempt_date' => current_time( 'mysql' ),
				'login_attempt_ip'   => $ip,
			),
			array( '%d', '%s', '%s' )
		);
	}

	/**
	 * This function queries login_fails table and returns the number of failures for current IP within allowed failure period
	 *
	 * @since 1.0.0
	 * @return int number of failures.
	 */
	private function _get_login_fail_count() {
		global $wpdb;
		$login_fails_table_name = Login_Limit::get_setting( 'db_login_fails' );
		$ip                     = Login_Limit::get_user_ip();
		$login_retry_interval   = Login_Limit::get_saved_settings( 'retry_time_period' );
		$now                    = current_time( 'mysql' );

		$login_failures = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(login_attempt_id) FROM $login_fails_table_name WHERE login_attempt_date + INTERVAL %d SECOND >= %s AND login_attempt_ip = %s", array( $login_retry_interval * 60, $now, $ip )
			)
		);
		return $login_failures;
	}

	/**
	 * _lock_the_user
	 *
	 * @since 1.0.0
	 * @param  string  $username username.
	 * @param  string  $ip_range ip range.
	 * @param  string  $ip ip.
	 * @param  integer $user_id user id.
	 * @param  string  $locked_reason locked reason.
	 * @return void
	 */
	private function _lock_the_user( $username, $ip_range, $ip, $user_id = 0, $locked_reason = 'login_fail' ) {
		global $wpdb;
		$locked_user          = self::current_locked_user();
		$lockdowns_table_name = Login_Limit::get_setting( 'db_lockdowns' );
		$lock_time            = current_time( 'mysql' );
		$release_time         = strtotime( $lock_time . ' + ' . Login_Limit::get_saved_settings( 'lockout_time_length' ) . ' minute' );
		$release_time         = date( 'Y-m-d H:i:s', $release_time );

		// if already has locked record.
		if ( $locked_user ) {
			$whitelists_ip = ( isset( $locked_user->whitelists_ip ) ) ? (array) unserialize( $locked_user->whitelists_ip ) : array();
			if ( false !== ( $key = array_search( $ip, $whitelists_ip ) ) ) {
				unset( $whitelists_ip[ $key ] );
			}

			$result = $wpdb->update(
				$lockdowns_table_name,
				array(
					'whitelists_ip' => serialize( $whitelists_ip ),
					'release_date'  => $release_time,
				),
				array( 'lockdown_id' => $locked_user->lockdown_id ),
				array( '%s', '%s' )
			);

		} else {
			$result = $wpdb->insert(
				$lockdowns_table_name,
				array(
					'user_id'         => $user_id,
					'lockdown_date'   => $lock_time,
					'release_date'    => $release_time,
					'lockdown_ip'     => $ip,
					'lockdown_reason' => $locked_reason,
					'whitelists_ip'   => serialize( array() ),
				),
				array( '%d', '%s', '%s', '%s', '%s' )
			);
		}

		if ( $result ) {
			Notification::send_ip_lock_notification_email( $username, $ip_range, $ip );
		}
	}

	/**
	 * Get from database the record that make user lockeddown
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function current_locked_user() {
		global $wpdb;

		$lockdowns_table_name = Login_Limit::get_setting( 'db_lockdowns' );
		$ip                   = Login_Limit::get_user_ip(); // get the IP address of user.
		$ip_range             = Login_Limit::get_ip_range( $ip );
		$now                  = current_time( 'mysql' );
		$locked_user          = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $lockdowns_table_name WHERE release_date > %s AND lockdown_ip LIKE %s", array( $now, $ip_range . '%' ) )
		);
		return $locked_user;
	}


	/**
	 * This function will process an unlock request when someone clicks on the special URL
	 * It will check if the special random code matches that in lockdown table for the relevant user
	 * If so, it will unlock the user
	 *
	 * @since 10.0.0
	 * @param  string $unlock_key unclock key.
	 * @return void
	 */
	public static function process_unlock_request( $unlock_key ) {
		if ( ! isset( $_GET['auth_key'] ) ) {
			return;
		}

		// if URL contains unlock key in query param then process the request.
		$unlock_key = strip_tags( $_GET['auth_key'] );

		global $wpdb;
		$lockdowns_table_name = Login_Limit::get_setting( 'db_lockdowns' );
		$ip                   = Login_Limit::get_user_ip();

		// current locked user by unlock key.
		$ip_range    = Login_Limit::get_ip_range( $ip );
		$now         = current_time( 'mysql' );
		$locked_user = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $lockdowns_table_name WHERE release_date > %s AND lockdown_ip LIKE %s AND unlock_key = %s", array( $now, $ip_range . '%', $unlock_key )
			)
		);

		if ( ! $locked_user ) {
			return;
		}

		$whitelists_ip = ( isset( $locked_user->whitelists_ip ) ) ? (array) @unserialize( $locked_user->whitelists_ip ) : array();
		if ( ! in_array( $ip, $whitelists_ip ) ) {
			$whitelists_ip[] = $ip;
		}

		$result = $wpdb->update(
			$lockdowns_table_name,
			array(
				'whitelists_ip' => serialize( $whitelists_ip ),
			),
			array( 'lockdown_id' => $locked_user->lockdown_id ),
			array(
				'%s',
				'%d',
			)
		);

		if ( $result ) {
			self::redirect_to_url( wp_login_url() );
		}
	}

	/**
	 * Redirects to specified URL
	 *
	 * @since 1.0.0
	 * @param string $url url.
	 * @param int    $delay delay.
	 * @param int    $exit exit.
	 */
	public static function redirect_to_url( $url, $delay = '0', $exit = '1' ) {
		if ( empty( $url ) ) {
			printf( '<br /><strong>%s</strong>', __( 'Error! The URL value is empty. Please specify a correct URL value to redirect to!', 'themedomain' ) );
			exit;
		}
		if ( ! headers_sent() ) {
			header( 'Location: ' . $url );
		} else {
			echo '<meta http-equiv="refresh" content="' . $delay . ';url=' . $url . '" />';
		}
		if ( '1' == $exit ) {
			exit;
		}
	}
}

new Lockdown();
