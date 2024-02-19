<?php

/**
 * Content filtering
 *
 * @package MadeIndonesia
 */

namespace BD\Security;

defined('ABSPATH') || die("Can't access directly");

/**
 * Class to manage content filtering
 */
class Content_Filter
{
	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		add_filter('the_content', 'wp_kses_post', 1);
	}
}

new Content_Filter();
