<?php

/**
 * Example of component
 *
 * @package BornDigital
 */

namespace BD\Emails;

class Setup
{

	public function __construct()
	{
		remove_filter('wp_mail_content_type', [$this, 'email_content_type']);
		add_filter('wp_mail_content_type',  [$this, 'email_content_type']);
		add_action('acf/init', [$this, 'add_email_settings'], 10);
		add_action('acf/init', [$this, 'load_acf_fields']);
	}

	public function email_content_type()
	{
		return 'text/html';
	}

	public function add_email_settings()
	{

		if (!function_exists('acf_add_options_page')) {
			return;
		}

		$email_setting_page = acf_add_options_page(
			array(
				'page_title'  => __('Email Settings', 'projectstarter'),
				'menu_title'  => __('Email Settings', 'projectstarter'),
				'parent_slug' => 'options-general.php',
				'menu_slug'   => 'email-settings',
				'capability'  => 'manage_options',
			)
		);
	}

	public function load_acf_fields()
	{
		if (file_exists(__DIR__ . '/acf/resetpassword-acf-fields.php')) {
			include  __DIR__ . '/acf/resetpassword-acf-fields.php';
		}
	}
}

new Setup();
