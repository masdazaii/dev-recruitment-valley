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

	public static function render_html_email($file, $args = [])
	{
		ob_start();
		require THEME_DIR . '/templates/email/' . $file;
		$content = ob_get_clean();

		foreach ($args as $key => $value)
		{
			$content = str_replace("{{ $key }}", $value, $content);
		}

		return $content;
	}
	
	/**
	 * send
	 * 
	 * to send email using template in `wp-content\themes\recruitment-valley\templates\email`
	 *
	 * @param  string $to
	 * @param  string $subject
	 * @param  array $args
	 * @param  string $template
	 * @param  array $headers
	 * @return bool
	 */
	public static function send($to, $subject, $args, $template, $headers = []): bool
	{
		$headers = (bool) $headers ? $headers : [
			'Content-Type: text/html; charset=UTF-8',
		];

		$contents = self::render_html_email($template, $args);

		$is_success = wp_mail($to, $subject, $contents, $headers);
		return $is_success;
	}
}
