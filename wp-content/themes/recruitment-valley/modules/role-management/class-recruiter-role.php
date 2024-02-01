<?php

namespace MI\Role;

use BD\Emails\Email;
use Log;
use Model\Recruiter;
use WP_User;

defined('ABSPATH') || die("Can't access directly");

class RecruiterRole
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'addRecruiterRole']);

        // When theme is deactived
        add_action('switch_theme', [$this, 'themeDeactivated'], 10, 3);

        /** Intercept user new registration for case : Send real generated password to email. */
        // add_filter("wp_pre_insert_user_data", [$this, 'interceptRecruiterUser'], 10, 4);

        /** Override the email function for case : Send link to change password. */
        add_filter('wp_new_user_notification_email', [$this, 'customWPUserNotificationEmail'], 10, 3);
        // add_filter('wp_new_user_notification_email_admin', [$this, 'customAdminWPUserNotificationEmail'], 10, 3);
    }

    public function addRecruiterRole()
    {
        remove_role('recruiter');
        add_role('recruiter', 'Recruiter', []);
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
        remove_role('recruiter');
    }

    /**
     * Intercept user before is saved to database function
     *
     * @param array $data
     * @param boolean $update
     * @param integer|null $user_id
     * @param array $userdata
     * @return void
     */
    // public function interceptRecruiterUser(array $data, bool $update, int|null $user_id, array $userdata)
    // {
    //     return $data;
    // }

    public function customWPUserNotificationEmail(array $wp_new_user_notification_email, WP_User $user, string $blogname)
    {
        /** Log attempt */
        $logData = [
            'request'   => [
                'userNotifEmail'    => $wp_new_user_notification_email,
                'user'              => $user->ID,
                'blogname'          => $blogname
            ],
            'currentUserID' => get_current_user_id(),
        ];

        /** Set reset password key. */
        $key    = wp_generate_uuid4();
        $setKey = update_user_meta($user->ID, "reset_password_key", $key);
        $reset_password_token = base64_encode($user->user_login . "_" . $key);

        /** Log Data */
        $logData['isTokenSet'] = $setKey;
        Log::info('Notification New Recruiter Account.', $logData, date('Y_m_d') . '_log_new_recruiter_registered', false);

        $args = [
            'site_name'         => $blogname ?? get_bloginfo('name') ?? 'Recruitment Valley',
            'user_email'        => isset($user->user_email) ? $user->user_email : '',
            'set_password_url'  => FRONTEND_RESET_PASSWORD_PATH . "?key={$reset_password_token}",
        ];

        $site_title = $blogname ?? get_bloginfo('name') ?? 'Recruitment Valley';
        $wp_new_user_notification_email['subject']  = "{$site_title} - Recruiters Account";
        $wp_new_user_notification_email['message']  = Email::render_html_email('new-recruiter-registered-email.php', $args);

        return $wp_new_user_notification_email;
    }
}

new RecruiterRole();
