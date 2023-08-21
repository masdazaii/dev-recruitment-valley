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
use Vacancy\Vacancy;

defined( 'ABSPATH' ) || die( "Can't access directly" );


class CronCustomize
{
    protected $time_today, $fiveDayAfter;
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
        $this->time_today = time();
        $today = $this->time_today;
        $dateToday     = date('Y-m-d', $today);

        $expired_posts = maybe_unserialize(get_option('job_expires'));

        $this->_expired_after_5_days($expired_posts);
        $this->_expired_posts_handle($expired_posts);

        $not_expired_posts = array_filter($expired_posts, function ($el) use ($dateToday) {
            $date = date('Y-m-d', strtotime($el['expired_at']));
            $time_date = strtotime($date);
            $time_today = strtotime($dateToday);
            return $time_date > $time_today;
        });

        update_option('job_expires', $not_expired_posts);
    }

    private function _expired_posts_handle($expired_posts = [])
    {
        $today = $this->time_today;
        $expired_posts_already = array_filter($expired_posts, function ($el) use($today) {
            $time = strtotime($el['expired_at']);
            return $time <= $today;
        });

        if(!$expired_posts_already) return;

        error_log('[expired posts] ' . json_encode($expired_posts_already, JSON_PRETTY_PRINT));
        foreach($expired_posts_already as $in => $the_post)
        {
            $post = get_post($the_post['post_id']);
            $user = get_user_by('id', $post->post_author);

            $args = [
                'vacancy_title' => $post->post_title,
            ];

            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );
            $content = Email::render_html_email('job-expired-company.php', $args);
            $is_success = wp_mail($user->user_email, "Melding verlopen vacature", $content, $headers);

            error_log('[notify][expired vacancy] sending email to ' . $user->user_email);
            error_log('[notify][expired vacancy] sending email status (' . ($is_success ? 'success' : 'failed') . ')');
            error_log('[notify][expired vacancy] unset post_id: ' . $the_post['post_id']);
        }

        return $expired_posts;
    }

    private function _expired_after_5_days($expired_posts)
    {
        $today = $this->time_today;
        $fiveDaysAfter = strtotime('+5 days', $today);
        // 2023-08-11
        $dateFiveAfter = date('Y-m-d', $fiveDaysAfter);
        $expired_posts = array_filter($expired_posts, function ($el) use ($dateFiveAfter) {
            $time = strtotime($el['expired_at']);
            $date = date('Y-m-d', $time);
            return $date == $dateFiveAfter;
        });

        if(!$expired_posts) return;
        error_log("[notify][expired +5 days] today is " . date('Y-m-d'));
        error_log("[notify][expired +5 days] will expired at " . $dateFiveAfter);

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
    }
}


new CronCustomize;