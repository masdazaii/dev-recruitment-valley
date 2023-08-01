<?php

namespace BD\SWK\Admin;

defined('ABSPATH') or exit;

class Setup
{
    /**
	 * Current module directory
	 *
	 * @var string
	 */
	private $dir;

	/**
	 * Current module url
	 *
	 * @var string
	 */
	private $url;

    public function __construct()
    {
        $this->dir = MODULES_DIR . '/save-with-keyboard/admin';
		$this->url = MODULES_URL . '/save-with-keyboard/admin';

        add_action('admin_enqueue_scripts', [$this, 'save_with_keyboard_enqueue']); 
    }

    public function save_with_keyboard_enqueue()
    {

        /*** JS ***/
        // swk js
        wp_enqueue_script(
            'swk_js',
            $this->url . '/assets/js/saveWithKeyboard.js',
            ['jquery'],
            'auto',
            true
        );

    }
 
}

new Setup();