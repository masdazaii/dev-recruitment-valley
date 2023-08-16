<?php
/**
* Cron Customize
*
* Author: Wikla
* 
* Note : 
* 
*
* @package HelloElementor
*/

use BD\Emails\Email;

defined( 'ABSPATH' ) || die( "Can't access directly" );


class CronCustomize
{
    public function __construct()
    {
        add_action('init', [$this, 'create_cron_job']);
    }

    public function create_cron_job()
    {
        add_action('rv_cron_expired_notification', [$this, 'handle_cron_expired_notify']);

        if(!wp_next_scheduled('rv_cron_expired_notification')) {
            wp_schedule_event(time(), 'daily', 'rv_cron_expired_notification');
        }
    }

    public function handle_cron_expired_notify()
    {
        $today = time();
        $fiveDaysAfter = strtotime('+5 days', $today);
        // 2023-08-11
        $dateFiveAfter = date('Y-m-d', $fiveDaysAfter);
        error_log("[notify][expired +5 days] today is " . date('Y-m-d'));
        error_log("[notify][expired +5 days] will expired at " . $dateFiveAfter);

        $expired_posts = [
            [
                'post_id' => 308,
                'expired_at' => '2023-08-21 04:11:00'
            ],
            [
                'post_id' => 291,
                'expired_at' => '2023-09-15 02:50:05',
            ]
        ];

        $expired_posts = array_filter($expired_posts, function ($el) use ($dateFiveAfter) {
            $time = strtotime($el['expired_at']);
            $date = date('Y-m-d', $time);
            return $date == $dateFiveAfter;
        });

        error_log('[notify][expired +5 days] posts: ' . json_encode($expired_posts, JSON_PRETTY_PRINT));

        foreach($expired_posts as $in => $the_post)
        {
            $post = get_post($the_post['post_id']);
            $user = get_user_by('id', $post->post_author);
            
            $args = [
                'vacancy_title' => $post->post_title,
            ];
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );
            
            $content = Email::render_html_email('reminder-5days-expire-company.php', $args);

            $site_title = get_bloginfo('name');
            $is_success = wp_mail($user->user_email, "Reminder verlopen vacature - $site_title", $content, $headers);
            
            error_log('[notify][expired +5 day] sending email to ' . $user->user_email);
            error_log('[notify][expired +5 day] sending email status (' . ($is_success ? 'success' : 'failed') . ')');
            error_log('[notify][expired +5 day] unset post_id: ' . $the_post['post_id']);
            
            unset($expired_posts[$in]);
        }

        // update_option('meta_key', $expired_posts);
    }
}


new CronCustomize;