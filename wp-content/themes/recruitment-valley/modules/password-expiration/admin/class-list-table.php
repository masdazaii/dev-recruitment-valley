<?php

/**
 * List Table
 *
 * @package MadeIndonesia
 */

namespace BD\Password\Expiration\Admin;

defined('ABSPATH') || die("Can't access directly");

/**
 * List Table class
 */
final class List_Table
{

	/**
	 * Class constructor.
	 */
	public function __construct()
	{

		add_action('admin_head', array($this, 'admin_css'));
		add_filter('manage_users_columns', array($this, 'users_column'));
		add_action('manage_users_custom_column', array($this, 'render_users_column'), 10, 3);
	}

	/**
	 * Print custom CSS styles for the users.php screen.
	 *
	 * @action admin_head
	 */
	public function admin_css()
	{

		$screen = get_current_screen();

		if (!isset($screen->id) || 'users' !== $screen->id) {

			return;
		}

?>
		<style type="text/css">
			.fixed .column-bdpass {
				width: 150px;
			}

			@media screen and (max-width: 782px) {
				.fixed .column-bdpass {
					display: none;
				}
			}

			.bdpass-is-expired {
				color: #a00;
			}
		</style>
<?php

	}

	/**
	 * Add a custom column to the Users list table.
	 *
	 * @filter manage_users_columns
	 *
	 * @param  array $columns The columns.
	 *
	 * @return array
	 */
	public function users_column($columns)
	{

		$columns['bdpass'] = esc_html__('Password Reset', 'themedomain');

		return $columns;
	}

	/**
	 * Add content to the custom column in the Users list table.
	 *
	 * @action manage_users_custom_column
	 *
	 * @param  string $value The value.
	 * @param  string $column_name The column name.
	 * @param  int    $user_id The user id.
	 *
	 * @return string
	 */
	public function render_users_column($value, $column_name, $user_id)
	{

		if ('bdpass' !== $column_name) {
			return $value;
		}

		$reset = Setup::get_user_meta($user_id);

		if (false === $reset || !Setup::has_expirable_role($user_id)) {
			return '&mdash;';
		}

		// translators: %1$s: time.
		$time_diff = sprintf(__('%1$s ago', 'themedomain'), human_time_diff($reset, time()));
		$class     = Setup::is_expired($user_id) ? 'bdpass-is-expired' : 'bdpass-not-expired';

		return sprintf(
			// translators: %s: time.
			'<span class="%s">%s</span>',
			esc_attr($class),
			esc_html($time_diff)
		);
	}
}
