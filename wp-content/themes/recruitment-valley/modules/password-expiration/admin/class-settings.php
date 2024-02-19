<?php

/**
 * Settings
 *
 * @package MadeIndonesia
 */

namespace BD\Password\Expiration\Admin;

defined('ABSPATH') || die("Can't access directly");

/**
 * Settings class to setup setting page
 */
final class Settings
{

	/**
	 * Class constructor.
	 */
	public function __construct()
	{

		add_action('admin_menu', array($this, 'submenu_page'));
		add_action('admin_init', array($this, 'init'));
	}

	/**
	 * Add custom submenu page under the Users menu.
	 *
	 * @action admin_menu
	 */
	public function submenu_page()
	{

		add_submenu_page(
			'users.php',
			esc_html__('Password Expiration', 'themedomain'),
			esc_html__('Password Expiration', 'themedomain'),
			'manage_options',
			'bd_expire_passwords',
			array($this, 'render_submenu_page')
		);
	}

	/**
	 * Content for the custom submenu page under the Users menu.
	 *
	 * @see $this->submenu_page()
	 */
	public function render_submenu_page()
	{

?>
		<div class="wrap">

			<h2><?php esc_html_e('Password Expiration', 'themedomain'); ?></h2>

			<form method="post" action="options.php">
				<?php

				settings_fields('bd_passexp_settings_page');

				do_settings_sections('bd_passexp_settings_page');

				submit_button();

				?>
			</form>

		</div>
<?php

	}

	/**
	 * Register custom setting sections and fields.
	 *
	 * @action admin_init
	 */
	public function init()
	{

		register_setting(
			'bd_passexp_settings_page',
			'bd_passexp_settings'
		);

		add_settings_section(
			'bd_passexp_settings_page_section',
			null,
			array($this, 'render_section'),
			'bd_passexp_settings_page'
		);

		add_settings_field(
			'passexp_settings_field_is_enabled',
			esc_html__('Enable password expiration feature?', 'themedomain'),
			array($this, 'render_field_is_enabled'),
			'bd_passexp_settings_page',
			'bd_passexp_settings_page_section'
		);

		add_settings_field(
			'passexp_settings_field_limit',
			esc_html__('Require password reset every', 'themedomain'),
			array($this, 'render_field_limit'),
			'bd_passexp_settings_page',
			'bd_passexp_settings_page_section'
		);

		add_settings_field(
			'passexp_settings_field_roles',
			esc_html__('For users in these roles', 'themedomain'),
			array($this, 'render_field_roles'),
			'bd_passexp_settings_page',
			'bd_passexp_settings_page_section'
		);
	}

	/**
	 * Content for the custom settings section.
	 *
	 * @see $this->init()
	 */
	public function render_section()
	{

		printf(
			'<p>%s</p>',
			esc_html__('Require certain users to change their passwords on a regular basis.', 'themedomain')
		);
	}

	/**
	 * Content for the "is_enabled" setting field.
	 *
	 * @see $this->init()
	 */
	public function render_field_is_enabled()
	{

		$options = (array) get_option('bd_passexp_settings', []);
		$value   = isset($options['is_enabled']) ? absint($options['is_enabled']) : 0;
		$checked = $value ? 'checked' : '';

		printf(
			'<input type="checkbox" name="bd_passexp_settings[is_enabled]" %s value="1"> %s',
			esc_attr($checked),
			esc_html__('Yes', 'themedomain')
		);
	}

	/**
	 * Content for the limit setting field.
	 *
	 * @see $this->init()
	 */
	public function render_field_limit()
	{

		$options = (array) get_option('bd_passexp_settings', []);
		$value   = isset($options['limit']) ? $options['limit'] : null;

		printf(
			'<input type="number" min="1" max="365" maxlength="3" name="bd_passexp_settings[limit]" placeholder="%s" value="%s"> %s',
			esc_attr(Setup::$default_limit),
			esc_attr($value),
			esc_html__('days', 'themedomain')
		);
	}

	/**
	 * Content for the roles setting field.
	 *
	 * @see $this->init()
	 */
	public function render_field_roles()
	{

		$options = (array) get_option('bd_passexp_settings', []);
		$roles   = get_editable_roles();

		foreach ($roles as $role => $role_data) {

			$name  = sanitize_key($role);
			$value = (!$options) ? ('administrator' === $role ? 0 : 1) : (empty($options['roles'][$name]) ? 0 : 1);

			printf(
				'<p><input type="checkbox" name="bd_passexp_settings[roles][%1$s]" id="bd_passexp_settings[roles][%1$s]" %2$s value="1"><label for="bd_passexp_settings[roles][%1$s]">%3$s</label></p>',
				esc_attr($name),
				checked($value, 1, false),
				esc_html($role_data['name'])
			);
		}
	}
}
