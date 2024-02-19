<?php

/**
 * Api Auth
 *
 * @package MadeIndonesia
 */

namespace BD\Security;

defined('ABSPATH') || die("Can't access directly");

use WP_Error;

/**
 * Api Auth class
 */
class Rest_API_Auth
{

	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		add_filter('rest_endpoints', [$this, 'secure_default_endpoints']);
	}

	public function secure_default_endpoints($endpoints)
	{
		if (is_user_logged_in() && current_user_can('administrator')) return $endpoints;

		foreach ($endpoints as $route => $endpoint) {
			if (false === stripos($route, '/mi/') && false === stripos($route, 'wpe_sign_on_plugin')) {
				unset($endpoints[$route]);
			}
		}

		return $endpoints;
	}
}

new Rest_API_Auth();
