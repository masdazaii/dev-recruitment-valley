<?php

namespace MI\Role;

use BD\Emails\Email;
use Exception;
use Log;
use Model\CompanyRecruiter;
use Model\Recruiter;
use WP_Error;
use WP_User;

defined('ABSPATH') || die("Can't access directly");

class RecruiterRole
{
    private const role = 'recruiter';
    public function __construct()
    {
        add_action('admin_init', [$this, 'addCompanyRecruiterRole']);

        // When theme is deactived
        add_action('switch_theme', [$this, 'themeDeactivated'], 10, 3);

        /** Add meta after user with this role is registered */
        add_action('user_register', [$this, 'addCompanyRecruiterMeta'], 10, 2);

        /** Intercept user new registration for case : Send real generated password to email. */
        // add_filter("wp_pre_insert_user_data", [$this, 'interceptRecruiterUser'], 10, 4);

        /** Override the email function for case : Send link to change password. */
        add_filter('wp_new_user_notification_email', [$this, 'customWPUserNotificationEmail'], 10, 3);
        // add_filter('wp_new_user_notification_email_admin', [$this, 'customAdminWPUserNotificationEmail'], 10, 3);
    }

    public function addCompanyRecruiterRole()
    {
        remove_role(self::role);
        add_role(self::role, 'Recruiter', []);
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
        remove_role(self::role);
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

    /**
     * Override Email Notification function
     *
     * @param array $newUserNotificationEmail
     * @param WP_User $user
     * @param string $blogname
     * @return void
     */
    public function customWPUserNotificationEmail(array $newUserNotificationEmail, WP_User $user, string $blogname)
    {
        /** Log attempt */
        $logData = [
            'request'   => [
                'userNotifEmail'    => $newUserNotificationEmail,
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
        $newUserNotificationEmail['subject']  = "{$site_title} - Recruiters Account";
        $newUserNotificationEmail['message']  = Email::render_html_email('new-recruiter-registered-email.php', $args);

        return $newUserNotificationEmail;
    }

    public function addCompanyRecruiterMeta(Int $userID, array $userdata = [])
    {
        /** Log attempt */
        $logData = [
            'request'   => [
                'user'      => $userID,
                'userData'  => $userdata
            ],
            'currentUserID' => get_current_user_id(),
        ];
        Log::info('After New Recruiter Account Registered.', $logData, date('Y_m_d') . '_log_new_recruiter_registered', false);

        if (is_admin() && current_user_can('administrator')) {
            if ($userID) {
                $user = get_user_by('id', $userID);
                if ($user && $user instanceof WP_User) {
                    if ($user->roles[0] == self::role) {
                        $companyRecruiter = CompanyRecruiter::find("id", $userID);

                        $set['otp'] = $companyRecruiter->setOTPIsVerified(true);

                        /** Log attempt */
                        $logData['set'] = $set;
                        Log::info('After New Recruiter Account Registered.', json_encode($logData, JSON_PRETTY_PRINT), date('Y_m_d') . '_log_new_recruiter_registered', false);
                    }
                }
            }
        }
    }
}

new RecruiterRole();
