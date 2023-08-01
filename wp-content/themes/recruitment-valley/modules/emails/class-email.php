<?php

namespace BD\Emails;

defined('ABSPATH') or exit;

class Email
{
	public static function set_placeholder($content, $placeholder_tags)
	{
		$placeholder = new Placeholder();
		return $placeholder->setContent($content)->convert($placeholder_tags);
	}

	public static function send_magic_link($user_id, $login_token)
	{
		$user    = get_userdata($user_id);
		$subject = get_field('bd_magic_link_subject', 'option');
		$body    = get_field('bd_magic_link_body', 'option');

		if (empty($subject) || empty($body)) {
			return false;
		}

		if ($user) {
			$login_url = site_url('welcome');
			$login_url = add_query_arg('t1', $user_id, $login_url);
			$login_url = add_query_arg('t2', $login_token, $login_url);

			$tags = [
				'{first_name}' => $user->first_name,
				'{last_name}'  => $user->last_name,
				'{full_name}'  => $user->first_name . ' ' . $user->last_name,
				'{magic_link}' => $login_url
			];

			// implement content tags to subject & body
			$subject = Email::set_placeholder($subject, $tags);
			$body    = Email::set_placeholder($body, $tags);

			$GLOBALS['freshjet_template_vars'] = [
				'body'    => $body,
				'subject' => $subject,
			];

			$is_sent = wp_mail($user->user_email, $subject, $body);
		}
	}
}
