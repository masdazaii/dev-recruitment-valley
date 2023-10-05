<?php
namespace BD\NewJquery\Admin;

defined('ABSPATH') or die('Can\'t access directly');

class Setup
{
	private $_url;
	private $_dir;

	public function __construct()
	{
		$this->_dir = THEME_DIR . '/modules/new-jquery/admin';
		$this->_url = THEME_URL . '/modules/new-jquery/admin';

		add_action( 'admin_enqueue_scripts', [$this, 'new_version_of_jquery'], 0);
    }

	public function new_version_of_jquery()
	{
		global $wp_scripts;
		global $concatenate_scripts;

		$concatenate_scripts = false;

		$jq_file_name = 'jquery-3.5.0.min.js';
		$core_src = $this->_url . '/assets/js/' . $jq_file_name;

		if (file_exists(THEME_DIR . '/dists/jquery/' . $jq_file_name)) {
			$core_src = THEME_URL . '/dists/jquery/' . $jq_file_name;
		}

		$jq_migrate_name = 'jquery-migrate-3.1.0.min.js';
		$migrate_src = $this->_url . '/assets/js/' . $jq_migrate_name;

		if (file_exists(THEME_DIR . '/dists/jquery/' . $jq_migrate_name)) {
			$migrate_src = THEME_URL . '/dists/jquery/' . $jq_migrate_name;
		}


		$updated_scripts = array(
			'jquery-core'    => array(
				'src' => $core_src,
				'ver' => '3.5.0'
			),
			'jquery-migrate'    => array(
				'src' => $migrate_src,
				'ver' => '3.1.0'
			),
		);

		foreach ($wp_scripts->registered as $handler => $register) {
			if (isset($updated_scripts[$handler])) {
				$wp_scripts->registered[$handler]->src = $updated_scripts[$handler]['src'];
				$wp_scripts->registered[$handler]->ver = $updated_scripts[$handler]['ver'];
			}
		}
	}

}

new Setup();
