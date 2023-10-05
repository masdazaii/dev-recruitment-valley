<?php
namespace BD\NewJquery;

defined('ABSPATH') or die('Can\'t access directly');

class Setup
{
	private $_url;
	private $_dir;

	public function __construct()
	{
		$this->_dir = THEME_DIR . '/modules/new-jquery';
		$this->_url = THEME_URL . '/modules/new-jquery';

		add_action( 'wp_enqueue_scripts', [$this, 'new_version_of_jquery'], 0);
    }

	public function new_version_of_jquery()
	{
		if (!is_admin()) {
			$jq_file_name = 'jquery-3.5.0.min.js';
			$src = $this->_url . '/assets/js/' . $jq_file_name;

			if (file_exists(THEME_DIR . '/dists/jquery/' . $jq_file_name)) {
				$src = THEME_URL . '/dists/jquery/' . $jq_file_name;
			}


			wp_deregister_script( 'jquery' );
			// wp_enqueue_script(
			// 	'jquery',
			// 	$src,
			// 	array(), '3.5.0', true
			// );
		}
	}

}

new Setup();
