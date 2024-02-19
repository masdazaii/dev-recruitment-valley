<?php

/**
 * Change login's error to use general error message
 *
 * @package MadeIndonesia
 */

namespace BD\Security;

defined('ABSPATH') || die("Can't access directly");

/**
 * Class to manage error messages
 */
class General_Error_Messages
{
	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		add_action('login_errors', [$this, 'custom_login_error_messages'], 10, 1);
	}

	/**
	 * Custom login error messages
	 *
	 * @param object $error WP_Error object.
	 * @return object
	 */
	public function custom_login_error_messages($error)
	{
		global $errors;

		$err_codes = $errors->get_error_codes();

		if (!is_array($err_codes)) {
			return $error;
		}

		if (in_array('incorrect_password', $err_codes, true) || in_array('invalid_username', $err_codes, true)) {
			$error = __('<strong>ERROR</strong>: Invalid username or password.', 'themedomain');
		}

		return $error;
	}
}

new General_Error_Messages();
