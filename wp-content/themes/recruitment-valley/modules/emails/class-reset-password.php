<?php

namespace BD\Emails;

class ResetPassword
{

	public function __construct()
	{
		add_filter('retrieve_password_message', [$this, 'on_reset_password'], 10, 4); // reset password
		add_filter('send_password_change_email', '__return_false'); //
		add_filter('password_change_email', '__return_false'); //
		add_filter('email_change_email', '__return_false'); //
		add_filter('site_admin_email_change_email', '__return_false'); //
	}

	public function on_reset_password($message, $key, $user_login, $user_data)
	{
		global $wpdb, $wp_hasher;

		$errors = new \WP_Error();

		/** BD Syntax start here */
		// if (empty($_POST['user_login'])) {
		// 	$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.', 'asmlinteractive'));
		// } elseif (strpos($_POST['user_login'], '@')) {
		// 	$user_data = get_user_by('email', trim(wp_unslash($_POST['user_login'])));
		// 	if (empty($user_data))
		// 		$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', 'asmlinteractive'));
		// } else {
		// 	$login = trim($_POST['user_login']);
		// 	$user_data = get_user_by('login', $login);
		// }
		/** BD Syntax end here */

		/** Changes start here */
		if (empty($user_data->data->user_email)) {
			$errors->add('empty_username', __('<strong>ERROR</strong>: Enter a username or email address.', 'asmlinteractive'));
		} elseif (strpos($user_data->data->user_email, '@')) {
			$user_data = get_user_by('email', trim(wp_unslash($user_data->data->user_email)));
			if (empty($user_data))
				$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', 'asmlinteractive'));
		} else {
			$login = trim($user_data->data->user_email);
			$user_data = get_user_by('login', $login);
		}
		/** Changes end here */

		do_action('lostpassword_post', $errors);

		if ($errors->get_error_code())
			return $errors;

		if (!$user_data) {
			$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email.', 'asmlinteractive'));
			return $errors;
		}

		/** BD Syntax start from here */
		// Redefining user_login ensures we return the right case in the email.
		// $user_login = $user_data->user_login;
		// $user_email = $user_data->user_email;
		/** BD Syntax end here */

		/** Changes Start here */
		$user_login = $user_data->data->user_login;
		$user_email = $user_data->data->user_email;
		/** Changes End here */

		$key = wp_generate_password(20, false);

		if (empty($wp_hasher)) {
			require_once(ABSPATH . WPINC . '/class-phpass.php');
			$wp_hasher = new \PasswordHash(8, true);
		}

		$hashed = time() . ':' . $wp_hasher->HashPassword($key);
		$wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

		if (is_wp_error($key)) {
			return $key;
		}

		/** Make sure acf is exists.
		 * ACF supposed to be created when class-setup.php is called.
		 */
		$subject = get_field('bd_lost_password_subject', 'option');
		$body    = get_field('bd_lost_password_body', 'option');

		if (!is_wp_error($key)) {
			$reset_password_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
		} else {
			error_log('failed to generate reset password key !');
		}

		$tags = [
			'{site_url}'           => get_site_url(),
			'{first_name}'         => $user_data->first_name,
			'{last_name}'          => $user_data->last_name,
			'{full_name}'          => $user_data->first_name . ' ' . $user_data->last_name,
			'{user_email}'         => $user_data->user_email,
			'{reset_password_url}' => $reset_password_url,
		];

		$subject = Email::set_placeholder($subject, $tags);
		$body    = Email::set_placeholder($body, $tags);

		$GLOBALS['freshjet_template_vars'] = [
			'body' => $body,
			'subject' => $subject,
		];

		$is_sent = wp_mail($user_email, $subject, $body);

		if (!$is_sent) {
			error_log('failed to send email in function reset password !');
		}

		return false;
	}
}

new ResetPassword();
