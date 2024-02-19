<?php

/**
 * Security Header
 *
 * @package MadeIndonesia
 */

namespace BD\Security;

defined('ABSPATH') || die("Can't access directly");

/**
 * Class to manage security header
 */
class Security_Header
{
	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		add_action('login_init', [$this, 'add_security_header']);
		add_action('admin_init', [$this, 'add_security_header']);
		add_action('init', [$this, 'add_security_header']);
	}

	/**
	 * Add security header
	 */
	public function add_security_header()
	{
		header('X-Frame-Options: DENY');
		header('strict-transport-security: max-age=31536000; includeSubDomains;');
		header('Referrer-Policy: no-referrer-when-downgrade');
		header('X-Content-Type-Options: nosniff');
		header('X-XSS-Protection: 1; mode=block');
		header("Content-Security-Policy: base-uri 'self'");
	}
}

new Security_Header();
