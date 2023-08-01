<?php
/**
 * Settings
 *
 * @package BornDigital
 */

namespace BD\Login\Limit\Admin;

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Setting class
 */
final class Settings {


	/**
	 * Call WordPress action to make settings page
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'submenu_page' ], 999 );
		add_action( 'admin_init', [ $this, 'init' ] );
	}

	/**
	 * Add custom submenu page under the Settings menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function submenu_page() {

		add_submenu_page(
			'users.php',
			__( 'Login Limit', 'themedomain' ),
			__( 'Login Limit', 'themedomain' ),
			'manage_options',
			'login-limit-settings',
			[ $this, 'render_submenu_page' ]
		);

	}


	/**
	 * Content for the custom submenu page under the Settings menu.
	 *
	 * @since 1.0.0
	 * @see $this->submenu_page()
	 * @return void
	 */
	public function render_submenu_page() {
		?>
		<div class="wrap login-limit-settings">

			<h2><?php esc_html_e( 'Login Limit', 'themedomain' ); ?></h2>
			<?php settings_errors(); ?>
			<?php $active_tab = ( isset( $_GET['tab'] ) ) ? sanitize_text_field( $_GET['tab'] ) : 'settings'; ?>

			<h2 class="nav-tab-wrapper">
				<a href="<?php echo add_query_arg( 'tab', 'settings' ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'themedomain' ); ?></a>
				<a href="<?php echo add_query_arg( 'tab', 'admin-email' ); ?>" class="nav-tab <?php echo 'admin-email' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php _e( 'Admin Notification', 'themedomain' ); ?></a>
				<a href="<?php echo add_query_arg( 'tab', 'unlock-email' ); ?>" class="nav-tab <?php echo 'unlock-email' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php _e( 'Email Unlock Request', 'themedomain' ); ?></a>
			</h2>

			<form method="post" action="options.php">
				<?php
				if ( 'admin-email' === $active_tab ) {

					settings_fields( 'login_limit_admin_email_page' );

					do_settings_sections( 'login_limit_admin_email_page' );
				} elseif ( 'unlock-email' === $active_tab ) {

					settings_fields( 'login_limit_unlock_email_page' );

					do_settings_sections( 'login_limit_unlock_email_page' );
				} else {

					settings_fields( 'login_limit_settings_page' );

					do_settings_sections( 'login_limit_settings_page' );
				}

				submit_button();

				?>
			</form>

		</div>
		<?php

	}

	/**
	 * Register custom setting sections and fields.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		register_setting(
			'login_limit_settings_page',
			'login_limit_settings'
		);

		add_settings_section(
			'login_limit_settings_page_section',
			false,
			false,
			'login_limit_settings_page'
		);

		add_settings_field(
			'login_limit_settings_field_enable',
			esc_html__( 'Enable Plugin', 'themedomain' ),
			[ $this, 'render_field_enable' ],
			'login_limit_settings_page',
			'login_limit_settings_page_section'
		);

		add_settings_field(
			'login_limit_settings_field_max_login_attempts',
			esc_html__( 'Max login attempts', 'themedomain' ),
			[ $this, 'render_field_max_login_attempts' ],
			'login_limit_settings_page',
			'login_limit_settings_page_section'
		);

		add_settings_field(
			'login_limit_settings_field_retry_time_period',
			esc_html__( 'Retry max period', 'themedomain' ),
			[ $this, 'render_field_retry_time_period' ],
			'login_limit_settings_page',
			'login_limit_settings_page_section'
		);

		add_settings_field(
			'login_limit_settings_field_lockout_time_length',
			esc_html__( 'Lockout time length', 'themedomain' ),
			[ $this, 'render_field_lockout_time_length' ],
			'login_limit_settings_page',
			'login_limit_settings_page_section'
		);

		register_setting(
			'login_limit_admin_email_page',
			'login_limit_admin_email'
		);

		add_settings_section(
			'login_limit_admin_email_page_section',
			esc_html__( 'Admin Email', 'themedomain' ),
			false,
			'login_limit_admin_email_page'
		);

		add_settings_field(
			'login_limit_settings_field_admin_email_subject',
			esc_html__( 'Subject', 'themedomain' ),
			[ $this, 'render_field_admin_email_subject' ],
			'login_limit_admin_email_page',
			'login_limit_admin_email_page_section'
		);

		add_settings_field(
			'login_limit_settings_field_admin_email_body',
			esc_html__( 'Body', 'themedomain' ),
			[ $this, 'render_field_admin_email_body' ],
			'login_limit_admin_email_page',
			'login_limit_admin_email_page_section'
		);

		register_setting(
			'login_limit_unlock_email_page',
			'login_limit_unlock_email'
		);

		add_settings_section(
			'login_limit_unlock_email_page_section',
			esc_html__( 'Unlock Request Emails', 'themedomain' ),
			false,
			'login_limit_unlock_email_page'
		);

		add_settings_field(
			'login_limit_settings_field_unlock_email_subject',
			esc_html__( 'Subject', 'themedomain' ),
			[ $this, 'render_field_unlock_email_subject' ],
			'login_limit_unlock_email_page',
			'login_limit_unlock_email_page_section'
		);

		add_settings_field(
			'login_limit_settings_field_unlock_email_body',
			esc_html__( 'Body', 'themedomain' ),
			[ $this, 'render_field_unlock_email_body' ],
			'login_limit_unlock_email_page',
			'login_limit_unlock_email_page_section'
		);

	}


	/**
	 * Content for the subject of unlock email field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_unlock_email_subject() {

		$value = Login_Limit::get_saved_unlock_email( 'unlock_email_subject' );

		printf(
			'<input name="login_limit_unlock_email[unlock_email_subject]" type="text" value="%s" class="regular-text">',
			esc_attr( $value )
		);
	}

	/**
	 * Content for the body of unlock email field.
	 *
	 * @see $this->init()
	 */
	/**
	 * Content for the body of unlock email field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_unlock_email_body() {

		$value = Login_Limit::get_saved_unlock_email( 'unlock_email_body' );

		wp_editor(
			$value, 'unlock_email_body', array(
				'textarea_name' => 'login_limit_unlock_email[unlock_email_body]',
				'media_buttons' => false,
				'quicktags'     => false,
				'tinymce'       => array(
					'toolbar1' => 'bold, italic, underline, link',
				),
			)
		);
	}

	/**
	 * Content for the subject of admin email field.
	 *
	 * @see $this->init()
	 */
	/**
	 * Content for the subject of admin email field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_admin_email_subject() {

		$value = Login_Limit::get_saved_admin_email( 'admin_email_subject' );

		printf(
			'<input name="login_limit_admin_email[admin_email_subject]" type="text" value="%s" class="regular-text">',
			esc_attr( $value )
		);
	}

	/**
	 * Content for the body of admin email field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_admin_email_body() {

		$value = Login_Limit::get_saved_admin_email( 'admin_email_body' );

		wp_editor(
			$value, 'admin_email_body', array(
				'textarea_name' => 'login_limit_admin_email[admin_email_body]',
				'media_buttons' => false,
				'quicktags'     => false,
				'tinymce'       => array(
					'toolbar1' => 'bold, italic, underline, link',
				),
			)
		);
	}


	/**
	 * Content for the enable the plugin setting field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_enable() {

		$value = Login_Limit::get_saved_settings( 'enable' );

		printf(
			'<input name="login_limit_settings[enable]" type="hidden" value="0"><input name="login_limit_settings[enable]" type="checkbox" value="1" %s>',
			checked( absint( $value ), 1, false )
		);
	}


	/**
	 * Content for the max login attempts setting field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_max_login_attempts() {

		$value = Login_Limit::get_saved_settings( 'max_login_attempts' );

		printf(
			'<input name="login_limit_settings[max_login_attempts]" type="number" step="1" min="3" id="max_login_attempts" value="%d" class="small-text"> times',
			esc_attr( $value )
		);
	}


	/**
	 * Content for the retry time period setting field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_retry_time_period() {

		$value = Login_Limit::get_saved_settings( 'retry_time_period' );

		printf(
			'<input name="login_limit_settings[retry_time_period]" type="number" step="1" min="10" id="retry_time_period" value="%d" class="small-text"> %s',
			esc_attr( $value ),
			esc_html__( 'minutes', 'themedomain' ) . '<br><small>' . esc_html__( "If user's login trial time is longer than this value, the counting will be reset" ) . '</small>'
		);
	}


	/**
	 * Content for the lockout time setting field.
	 *
	 * @since 1.0.0
	 * @see $this->init()
	 * @return void
	 */
	public function render_field_lockout_time_length() {

		$value = Login_Limit::get_saved_settings( 'lockout_time_length' );

		printf(
			'<input name="login_limit_settings[lockout_time_length]" type="number" step="1" min="10" id="lockout_time_length" value="%d" class="small-text"> %s',
			esc_attr( $value ),
			esc_html__( 'minutes', 'themedomain' ) . '<br><small>' . esc_html__( 'How long users would be locked out?', 'themedomain' ) . '</small>'
		);
	}

}

new Settings();
