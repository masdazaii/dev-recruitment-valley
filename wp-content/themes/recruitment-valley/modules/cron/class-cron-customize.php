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
use constant\NotificationConstant;
use Global\NotificationService;

defined('ABSPATH') || die("Can't access directly");


class CronCustomize
{
    protected $time_today, $fiveDayAfter;
    private $_notification;
    private $_notificationConstant;
    private $wpdb;

    public function __construct()
    {
        add_action('init', [$this, 'create_cron_job']);
        $this->_notification = new NotificationService();
        $this->_notificationConstant = new NotificationConstant();

        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function create_cron_job()
    {
        add_action('rv_cron_expired_notification', [$this, 'handle_cron_expired_notify']);

        if (!wp_next_scheduled('rv_cron_expired_notification')) {
            wp_schedule_event(time(), 'daily', 'rv_cron_expired_notification');
        }
    }

    public function handle_cron_expired_notify()
    {
        $this->time_today = time();
        $today = $this->time_today;
        $dateToday     = date('Y-m-d', $today);

        $expired_posts = maybe_unserialize(get_option('job_expires'));

        error_log('Start call');
        // $this->_expired_after_5_days($expired_posts);

        // ? +7 days before
        $this->_expired_after_in_days($expired_posts, 7);
        // ? +5 days before
        $this->_expired_after_in_days($expired_posts, 5);
        // ? +3 days before
        $this->_expired_after_in_days($expired_posts, 3);

        $this->_expired_posts_handle($expired_posts);
        error_log('end call');

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
        error_log('expired now');

        /** Changes 25-10-2023: adding try catch, and DID NOT changing the script inside the try argument */
        $this->wpdb->query("START TRANSACTION");
        try {
            $today = $this->time_today;
            $expired_posts_already = array_filter($expired_posts, function ($el) use ($today) {
                $time = strtotime($el['expired_at']);
                return $time <= $today;
            });

            if (!$expired_posts_already) return;

            error_log('[expired posts] ' . json_encode($expired_posts_already, JSON_PRETTY_PRINT));
            foreach ($expired_posts_already as $in => $the_post) {
                $post = get_post($the_post['post_id']);
                $user = get_user_by('id', $post->post_author);

                // $vacancy = new Vacancy($post); // Changed below 25-10-2023
                $vacancy = new Vacancy($the_post['post_id']);
                $setStatus = $vacancy->setStatus("close");

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

                /** Unset from option */
                unset($expired_posts[$in]);

                /** Create notification : vacancy is expired */
                $this->_notification->write($this->_notificationConstant::VACANCY_EXPIRED, $post->post_author, [
                    'id'    => $the_post['post_id'],
                    'title' => $post->post_title,
                    'slug'  => $post->post_name
                ]);
            }

            $this->wpdb->query("COMMIT");
            return $expired_posts;
        } catch (WP_Error $err) {
            $this->wpdb->query("ROLLBACK");
            error_log($err->get_error_message());
        } catch (Exception $e) {
            $this->wpdb->query("ROLLBACK");
            error_log($e->getMessage());
        } catch (Throwable $th) {
            $this->wpdb->query("ROLLBACK");
            error_log($th->getMessage());
        }
    }

    private function _expired_after_in_days($expired_posts, $in_days = 5, $template = 'reminder-5days-expire-company.php')
    {
        $today = $this->time_today;
        $text_time = sprintf('+%s days', $in_days);
        $fewDaysAfter = strtotime($text_time, $today);
        // 2023-08-11
        $dateAfter = date('Y-m-d', $fewDaysAfter);
        $expired_posts = array_filter($expired_posts, function ($el) use ($dateAfter) {
            $time = strtotime($el['expired_at']);
            $date = date('Y-m-d', $time);
            return $date == $dateAfter;
        });

        if (!$expired_posts) return;
        error_log("[notify][expired '. $text_time .'] today is " . date('Y-m-d'));
        error_log("[notify][expired '. $text_time .'] will expired at " . $dateAfter);

        error_log('[notify][expired ' . $text_time . '] posts: ' . json_encode($expired_posts, JSON_PRETTY_PRINT));

        foreach ($expired_posts as $in => $the_post) {
            $post = get_post($the_post['post_id']);
            $user = get_user_by('id', $post->post_author);

            $args = [
                'vacancy_title' => $post->post_title,
            ];
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
            );

            $content = Email::render_html_email($template, $args);

            $site_title = get_bloginfo('name');
            $is_success = wp_mail($user->user_email, "Reminder verlopen vacature - $site_title", $content, $headers);

            error_log('[notify][expired ' . $text_time . '] sending email to ' . $user->user_email);
            error_log('[notify][expired ' . $text_time . '] sending email status (' . ($is_success ? 'success' : 'failed') . ')');
            error_log('[notify][expired ' . $text_time . '] unset post_id: ' . $the_post['post_id']);

            /** Create notification : vacancy is expired in 5 days */
            $this->_notification->write($this->_notificationConstant::VACANCY_EXPIRED_5, $post->post_author, [
                'id'    => $the_post['post_id'],
                'title' => $post->post_title,
                'slug'  => $post->post_name
            ]);

            unset($expired_posts[$in]);
        }
    }
}


new CronCustomize;
