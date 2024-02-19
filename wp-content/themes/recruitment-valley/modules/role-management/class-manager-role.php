<?php

/**
 * Mananger Role
 *
 * @package MadeIndonesia
 */

namespace BD\Security;

defined('ABSPATH') || die("Can't access directly");

/**
 * Manager Role class
 */
class Manager_Role
{
	/**
	 * Setup the flow
	 */
	public function __construct()
	{
		// theme activated.
		add_action('admin_init', [$this, 'add_manager_role']);

		// theme deactivated.
		add_action('switch_theme', [$this, 'theme_deactivated'], 10, 3);
	}

	/**
	 * Function to add new role and capabilities run once when theme activated
	 * https://codex.wordpress.org/Roles_and_Capabilities
	 * @return void
	 */
	public function add_manager_role()
	{
		remove_role('manager');
		add_role(
			'manager',
			'Manager',
			array(
				// 'switch_themes'       => true,
				// 'edit_themes'         => true,
				// 'activate_plugins'    => true,
				// 'edit_plugins'        => true,
				'edit_users'             => false,
				// 'edit_files'          => true,
				// 'manage_options'      => true,
				'moderate_comments'      => true,
				'manage_categories'      => true,
				// 'manage_links'        => true,
				'upload_files'           => true,
				// 'import'              => true,
				'unfiltered_html'        => true,
				'edit_posts'             => true,
				'edit_others_posts'      => true,
				'edit_published_posts'   => true,
				'publish_posts'          => true,
				'edit_pages'             => true,
				'read'                   => true,
				'edit_others_pages'      => true,
				'edit_published_pages'   => true,
				'publish_pages'          => true,
				'delete_pages'           => true,
				'delete_others_pages'    => true,
				'delete_published_pages' => true,
				'delete_posts'           => true,
				'delete_others_posts'    => true,
				'delete_published_posts' => true,
				'delete_private_posts'   => true,
				'edit_private_posts'     => true,
				'read_private_posts'     => true,
				'delete_private_pages'   => true,
				'edit_private_pages'     => true,
				'read_private_pages'     => true,
				'delete_users'           => false,
				'create_users'           => false,
				'unfiltered_upload'      => true,
				'edit_dashboard'         => true,
				// 'update_plugins'      => true,
				// 'delete_plugins'      => true,
				// 'install_plugins'     => true,
				// 'update_themes'       => true,
				// 'install_themes'      => true,
				// 'update_core'         => true,
				'list_users'             => true,
				'remove_users'           => true,
				'manage_downloads'       => true,
				'dlm_manage_logs'        => true,
				'manager'                => true,
				// 'promote_users'       => true,
				// 'edit_theme_options'  => true,
				// 'delete_themes'       => true,
				// 'export'              => true,
				// 'view_query_monitor'  => true
			)
		);

		$role = get_role('administrator');
		$role->add_cap('manager');
	}

	/**
	 * This function will run once when theme deactivated
	 *
	 * @param  string   $new_name the new name.
	 * @param  WP_Theme $new_theme the new theme.
	 * @param  WP_Theme $old_theme the old theme.
	 * @return void
	 */
	public function theme_deactivated($new_name, $new_theme, $old_theme)
	{
		remove_role('manager');
	}
}

new Manager_Role();
