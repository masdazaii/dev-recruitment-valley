<?php

/**
 * Disabe file editing
 *
 * @package MadeIndonesia
 */

namespace BD\Security\Admin;

defined('ABSPATH') || die("Can't access directly");

/**
 * Class to disable file editing
 */
class Disable_File_Editing
{
	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		add_filter('map_meta_cap', [$this, 'disallow_file_edit'], 20, 4);
	}

	/**
	 * Disallow file editing
	 *
	 * @param array  $caps capabilities.
	 * @param string $cap capability.
	 * @param int    $user_id user's id.
	 * @param ?      $args ?.
	 */
	public function disallow_file_edit($caps, $cap, $user_id, $args)
	{
		if ($cap && in_array($cap, array('edit_files', 'edit_plugins', 'edit_themes'), true)) {
			$caps[] = 'do_not_allow';
		}

		return $caps;
	}
}

new Disable_File_Editing();
