<?php
/**
 * Setup class
 *
 * @package BornDigital
 */

defined( 'ABSPATH' ) || die( "Can't access directly" );

/**
 * Setup Beyourbest theme
 */
class Theme {

	/**
	 * A dummy constructor to ensure the class is only initialized once
	 */
	public function __construct() {
		/* Do nothing here */
	}

	/**
	 * The real constructor to initialize Limit Login
	 *
	 * @return void
	 */
	public function initialize() {
		global $wpdb;

		$theme = wp_get_theme();

		// vars.
		$this->settings = array(

			// basic.
			'name'     => $theme->get( 'Name' ),
			'domain'   => $theme->get( 'TextDomain' ),
			'version'  => $theme->get( 'Version' ),

			// urls.
			'file'     => __FILE__,
			'basename' => plugin_basename( __FILE__ ),
			'path'     => get_stylesheet_directory(),
			'url'      => get_stylesheet_directory_uri(),

		);

		// constants.
		define( 'BASE_URL', get_site_url() );
		define( 'BASE_DIR', rtrim( ABSPATH, '/' ) );

		define( 'THEME_URL', $this->settings['url'] );
		define( 'THEME_DIR', $this->settings['path'] );
		define( 'THEME_VERSION', $this->settings['version'] );
		define( 'THEME_DOMAIN', $this->settings['domain'] );

		define( 'MODULES_URL', THEME_URL . '/modules' );
		define( 'MODULES_DIR', THEME_DIR . '/modules' );

		// load dependencies.
		require_once THEME_DIR . '/vendor/autoload.php';
		require_once THEME_DIR . '/libraries/autoload.php';

		// load all files.
		$this->load(
			[
				THEME_DIR . '/setup/*.php',
				THEME_DIR . '/modules/*/autoload.php',
			]
		);

		// theme activated.
		add_action( 'after_switch_theme', [ $this, 'activate_theme' ], 10, 2 );

		// theme deactivated.
		add_action( 'switch_theme', [ $this, 'deactivate_theme' ], 10, 3 );

		// actions.
		add_action( 'init', [ $this, 'on_init' ], 5 );
	}

	/**
	 * Load files
	 *
	 * @param  array $files_path path of the file.
	 * @return void
	 */
	private function load( $files_path ) {
		foreach ( $files_path as $path ) {
			foreach ( glob( $path ) as $file ) {
				require_once $file;
			}
		}
	}

	/**
	 * This function will run once when theme activated
	 *
	 * @param  string           $oldname old name.
	 * @param  WP_Theme|boolean $oldtheme old theme.
	 * @return void
	 */
	public function activate_theme( $oldname, $oldtheme = false ) {
		/**
		 * You can create new database table in here
		 *
		 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins
		 */

		flush_rewrite_rules();
	}

	/**
	 * This function will run once when theme deactivated
	 *
	 * @param  string   $new_name The new name.
	 * @param  WP_Theme $new_theme The new theme.
	 * @param  WP_Theme $old_theme The old theme.
	 * @return void
	 */
	public function deactivate_theme( $new_name, $new_theme, $old_theme ) {
		/**
		 * You can delete database table in here
		 *
		 * @link https://codex.wordpress.org/Creating_Tables_with_Plugins
		 */

		flush_rewrite_rules();
	}

	/**
	 * This function will run after all plugins and theme functions have been included
	 *
	 * @return void
	 */
	public function on_init() {

		// textdomain.
		$this->load_theme_textdomain();

	}


	/**
	 * This function will load the textdomain file
	 *
	 * @return void
	 */
	public function load_theme_textdomain() {
		// vars.
		load_theme_textdomain( 'themedomain', THEME_DIR . '/languages' );
	}


	/**
	 * This function will safely define a constant
	 *
	 * @param string $name The name.
	 * @param mixed  $value The value.
	 * @return mixed
	 */
	public function define( $name, $value = true ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}


	/**
	 * This function will return a value from the settings array found in the kdbm_pbfl object
	 *
	 * @param  string $name The name.
	 * @param  mixed  $value The value.
	 * @return mixed
	 */
	public function get_setting( $name, $value = null ) {

		// check settings.
		if ( isset( $this->settings[ $name ] ) ) {
			$value = $this->settings[ $name ];
		}

		return $value;

	}
}
