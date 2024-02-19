<?php

/**
 * Login Limit
 *
 * @package MadeIndonesia
 */

namespace BD\Login\Limit\Admin;

defined('ABSPATH') || die("Can't access directly");

/**
 * Login Limit class
 */
class Login_Limit
{

	/**
	 * Default settings
	 *
	 * @var @default_settings
	 */
	public static $default_settings = array();

	/**
	 * Setup the flow
	 */
	public function __construct()
	{

		global $wpdb;

		self::$default_settings = array(
			// options default.
			'enable'               => 1,
			'max_login_attempts'   => 5,
			'retry_time_period'    => 60, // in minutes.
			'lockout_time_length'  => 60, // in minutes.

			// database.
			'db_login_fails'       => $wpdb->prefix . 'login_limit_fails',
			'db_lockdowns'         => $wpdb->prefix . 'login_limit_lockdowns',

			// emails.
			'admin_email_subject'  => __('[{site_url}] Site Lockout Notification', 'asmlexploration'),
			'admin_email_body'     => __(
				"A lockdown event has occurred due to too many failed login attempts. \n\n
				<strong style='color: #ee782e;'>Username:</strong> {username} \n
				<strong style='color: #ee782e;'>IP Address:</strong> {ip} \n
				<strong style='color: #ee782e;'>IP Range:</strong> {ip_range}.* \n",
				'asmlexploration'
			),
			'unlock_email_subject' => __('[{site_url}] Unlock Request Notification', 'asmlexploration'),
			'unlock_email_body'    => __(
				"You have requested for the account with email address {email} to be unlocked. Please click the link below to unlock your account:\n\n
				<strong style='color: #ee782e;'>Unlock link:</strong> {unlock_link}\n\n
				After clicking the above link you will be able to login to the WordPress administration panel.",
				'asmlexploration'
			),
		);

		add_action('after_switch_theme', [$this, 'activate_theme'], 10, 2);
	}

	/**
	 * This function will run once when theme activated
	 *
	 * @param  string           $oldname old name.
	 * @param  WP_Theme|boolean $oldtheme old theme.
	 * @return void
	 */
	public function activate_theme($oldname, $oldtheme = false)
	{
		// run when plugin activated.
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		// include upgrade.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// create table bundle log.
		$login_fails_db_name = self::$default_settings['db_login_fails'];
		$sql                 = "CREATE TABLE IF NOT EXISTS $login_fails_db_name (
			login_attempt_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20),
			login_attempt_date datetime NOT NULL default '0000-00-00 00:00:00',
			login_attempt_ip varchar(100) NOT NULL default '',
			PRIMARY KEY  (login_attempt_ID)
		) $charset_collate;";
		dbDelta($sql);

		// create table bundle log.
		$lockdowns_db_name = self::$default_settings['db_lockdowns'];
		$sql               = "CREATE TABLE IF NOT EXISTS $lockdowns_db_name (
			lockdown_id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20),
			lockdown_date datetime NOT NULL default '0000-00-00 00:00:00',
			release_date datetime NOT NULL default '0000-00-00 00:00:00',
			lockdown_ip varchar(100) NOT NULL default '',
			lockdown_reason varchar(100) NOT NULL default '',
			unlock_key varchar(100) NOT NULL default '',
			whitelists_ip longtext,
			PRIMARY KEY  (lockdown_ID)
		) $charset_collate;";
		dbDelta($sql);
	}

	/**
	 * This function will return a value from the settings array found in the Login_Limit object
	 *
	 * @since 1.0.0
	 * @param  string $name name.
	 * @param  mixed  $value value.
	 * @return mixed
	 */
	public static function get_setting($name, $value = null)
	{

		// check settings.
		if (isset(self::$default_settings[$name])) {

			$value = self::$default_settings[$name];
		}

		return $value;
	}

	/**
	 * Get option from database if available and return from settings if not.
	 *
	 * @since 1.0.0
	 * @param  string $option option.
	 * @return mixed
	 */
	public static function get_saved_settings($option)
	{
		$saved_settings = (array) get_option('login_limit_settings', array());


		if (isset($saved_settings[$option])) {
			return $saved_settings[$option];
		}

		return self::$default_settings[$option];
	}


	/**
	 * Get option from database if available and return from settings if not.
	 *
	 * @since 1.0.0
	 * @param  string $option option.
	 * @return mixed
	 */
	public static function get_saved_admin_email($option)
	{

		$saved_admin_email = (array) get_option('login_limit_admin_email', array());

		if (isset($saved_admin_email[$option]) && !empty($saved_admin_email[$option])) {
			return $saved_admin_email[$option];
		}

		return self::$default_settings[$option];
	}


	/**
	 * Get option from database if available and return from settings if not.
	 *
	 * @since 1.0.0
	 * @param  string $option the options.
	 * @return mixed
	 */
	public static function get_saved_unlock_email($option)
	{

		$saved_unlock_email = (array) get_option('login_limit_unlock_email', array());

		if (isset($saved_unlock_email[$option]) && !empty($saved_unlock_email[$option])) {
			return $saved_unlock_email[$option];
		}

		return self::$default_settings[$option];
	}

	/**
	 * Get current client ip
	 *
	 * @since 1.0.0
	 * @return string ip address
	 */
	public static function get_user_ip()
	{

		$ipaddress = '';

		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
			$ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = 'UNKNOWN';
		}

		return sanitize_text_field($ipaddress);
	}


	/**
	 * Returns the first three octets of a sanitized IP address so it can used as an IP address range
	 *
	 * @since 1.0.0
	 * @param  string $ip the ip.
	 * @return string ip range.
	 */
	public static function get_ip_range($ip)
	{
		$ip_range = '';
		$valid_ip = filter_var($ip, FILTER_VALIDATE_IP); // sanitize the IP address.

		if ($valid_ip) {
			$ip_type = \WP_Http::is_ip_address($ip); // returns 4 or 6 if ipv4 or ipv6 or false if invalid.
			if (false === $ip_type || 6 == $ip_type) {
				return ''; // for now return empty if ipv6 or invalid IP.
			}
			$ip_range = substr($valid_ip, 0, strrpos($valid_ip, '.')); // strip last portion of address to leave an IP range.
		}
		return $ip_range;
	}
}

new Login_Limit();
