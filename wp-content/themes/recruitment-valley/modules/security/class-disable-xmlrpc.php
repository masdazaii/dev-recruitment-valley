<?php

/**
 * Disable XMLRPC.php
 *
 * @package MadeIndonesia
 */

namespace BD\Security;

defined('ABSPATH') || die("Can't access directly");

/**
 * Class to disable xmlrpc.php
 */
class Disable_XMLRPC
{
	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		add_filter('xmlrpc_enabled', '__return_false');
		add_action('init', [$this, 'check_file_request']);
	}

	public function check_file_request()
	{
		$file_request = @$_SERVER['PHP_SELF'];
		if (strpos($file_request, 'xmlrpc.php')) {
			wp_die();
		}
	}
}

new Disable_XMLRPC();
