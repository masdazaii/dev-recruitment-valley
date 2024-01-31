<?php

namespace MI\Role;

use BD\Emails\Email;
use MI\API\Model\Recruiter;

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
        remove_action('register_new_user', 'wp_send_new_user_notifications');
        add_action('register_new_user', [$this, 'customWPUserNotification']);
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
    public function customWPUserNotification($user_id, $deprecated = null, $notify = '')
    {
        if ($deprecated !== null) {
            _deprecated_argument(__FUNCTION__, '4.3.1');
        }

        global $wpdb;

        /** Get user data */
        // $user = get_userdata($user_id);
        $user = Recruiter::find('id', $user_id);

        /** Set Subject */
        $site_title = get_bloginfo('name') ?? 'Recruitment Valley';
        $subject    = "{$site_title} - Recruiters Account";

        /** Send Email to User */
        $args = [
            'email'             => NULL,
            'setPasswordURL'    => NULL,
        ];

        $sendMail = Email::send(NULL, $subject, $args, 'new-recruiter-registered-email.php');

        // Customize the email message
        $message = "Hello " . $user->display_name . ",\n\n";
        $message .= "Welcome to our site!\n\n";
        $message .= "Your username: " . $user->user_login . "\n\n";
        $message .= "You can log in at " . site_url('wp-login.php') . "\n\n";
        $message .= "Thank you for joining us.";

        // Set up email headers
        $headers = 'From: ' . get_option('admin_email') . "\r\n";
        $headers .= 'Content-Type: text/plain; charset=utf-8';

        // Send the email to the user
        wp_mail($user->user_email, $subject, $message, $headers);

        // Send notification to admin
        if ('admin' === $notify || empty($notify)) {
            // Customize the admin email subject and message
            $admin_subject = 'New User Registration: ' . $user->user_login;
            $admin_message = "A new user has registered on your site:\n\n";
            $admin_message .= "Username: " . $user->user_login . "\n";
            $admin_message .= "Email: " . $user->user_email . "\n\n";
            $admin_message .= "You can view and manage the user in the admin dashboard.";

            // Send the email to the admin
            wp_mail(get_option('admin_email'), $admin_subject, $admin_message, $headers);
        }
    }
}

new RecruiterRole();
