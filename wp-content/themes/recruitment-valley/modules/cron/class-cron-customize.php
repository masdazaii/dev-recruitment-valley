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
            wp_schedule_event(time(), 'hourly', 'rv_cron_expired_notification');
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

        $expired_posts = get_option('cron_expired_notify_in_5_d', []);

        $args = [
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'expired_at',
                    'value' => $dateFiveAfter,
                    'compare' => '=',
                    'type'  => 'DATE'
                ],
                [
                    'relation' => 'OR',
                    [
                        'key' => 'is_already_5_d',
                        'compare' => 'NOT EXISTS'
                    ],
                    [
                        'key'       => 'is_already_5_d',
                        'compare'   => '=',
                        'value'     => '0',
                    ]
                ]
            ],
            'post__not_in' => $expired_posts,
            'fields'    => 'ids',
        ];

        $query = new WP_Query($args);
        $expired_posts = $query->posts;
        error_log('[notify][expired +5 days] posts_id: ' . json_encode($expired_posts));

        foreach($expired_posts as $in => $post_id)
        {
            $post = get_post($post_id);
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
            error_log('[notify][expired +5 day] unset post_id: ' . $post_id);
            
            update_post_meta($post_id, 'is_already_5_d', 1);
        }
    }
}


new CronCustomize;