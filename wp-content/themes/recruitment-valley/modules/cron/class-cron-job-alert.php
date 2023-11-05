<?php

/**
 * Cron Job Alert
 *
 * Author: Zulfan
 *
 * Note :
 *
 *
 * @package HelloElementor
 */

use BD\Emails\Email;
use Helper\EmailHelper;
use JobAlert\Data;

defined('ABSPATH') || die("Can't access directly");

class CronJobAlert
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'set_schedule']);
        add_action('init', [$this, 'scheduling']);
        add_action('send_job_alert_per_one_day', [$this, 'send_job_alert_daily']);
        add_action('send_job_alert_per_7_day', [$this, 'send_job_alert_weekly']);
        add_action('send_job_alert_per_30_day', [$this, 'send_job_alert_monthly']);
    }

    /**
     * set_schedule
     *
     * @param  mixed $schedules
     * @return void
     */
    public function set_schedule($schedules)
    {
        $schedules['per_one_day'] = [
            'interval' => 1 * DAY_IN_SECONDS,
            'display'  => __('Every 24 Hours (Send Job Alert)')
        ];

        $schedules['per_one_month'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display'  => __('Every 30 day (Send Job Alert)')
        ];

        $schedules['per_one_week'] = [
            'interval' => 7 * DAY_IN_SECONDS,
            'display'  => __('Every 7 day (Send Job Alert)')
        ];

        return $schedules;
    }

    /**
     * scheduling
     *
     * @return void
     */
    public function scheduling()
    {
        if (!wp_next_scheduled('send_job_alert_per_one_day')) {
            wp_schedule_event(time(), 'per_one_day', 'send_job_alert_per_one_day');
        }

        if (!wp_next_scheduled('send_job_alert_per_7_day')) {
            wp_schedule_event(time(), 'per_one_week', 'send_job_alert_per_7_day');
        }

        if (!wp_next_scheduled('send_job_alert_per_30_day')) {
            wp_schedule_event(time(), 'per_one_month', 'send_job_alert_per_30_day');
        }
    }


    /**
     * send_job_alert_daily
     *
     * @return void
     */
    public function send_job_alert_daily()
    {
        $data = new Data();
        $job_data = $data->main('daily');

        // foreach($job_data as $email_key => $jobs)
        // {
        //     $headers    = ['Content-Type: text/html; charset=UTF-8'];
        //     $email      = $email_key;
        //     $subject    = 'Job Alert Daily Report';

        //     $message    = '<html><body>';
        //     $message    .= '<h2>Job Alert Daily Report</h2>';
        //     $message    .= '<ul>';

        //     foreach ($jobs as $job_data) {
        //         $job_title  = $job_data['title'];
        //         $job_url    = $job_data['url'];

        //         $message .= '<li><a href="' . $job_url . '">' . $job_title . '</a></li>';
        //     }
        //     $message .= '</ul>';
        //     $message .= '</body></html>';

        //     wp_mail($email, $subject, $message, $headers);
        // }
        error_log("send alert job daily trigerred");

        foreach ($job_data as $jobAlerEmail => $jobData) {
            /** Added Line to get jobalert data */
            $jobAlertModel = new \Model\JobAlert($jobData['jobAlertId']);
            $jobData['firstName'] = $jobAlertModel->getJobAlertFirstName();

            EmailHelper::sendJobAlert($jobData);
        }
    }

    /**
     * send_job_alert_weekly
     *
     * @return void
     */
    public function send_job_alert_weekly()
    {
        $data       = new Data();
        $job_data   = $data->main('weekly');

        // foreach($job_data as $email_key => $jobs)
        // {
        //     $headers    = ['Content-Type: text/html; charset=UTF-8'];
        //     $email      = $email_key;
        //     $subject    = 'Job Alert Weekly Report';

        //     $message    = '<html><body>';
        //     $message    .= '<h2>Job Alert Weekly Report</h2>';
        //     $message    .= '<ul>';

        //     foreach ($jobs as $job_data) {
        //         $job_title  = $job_data['title'];
        //         $job_url    = $job_data['url'];
        //         $message    .= '<li><a href="' . $job_url . '">' . $job_title . '</a></li>';
        //     }
        //     $message .= '</ul>';
        //     $message .= '</body></html>';

        //     wp_mail($email, $subject, $message, $headers);
        // }
        error_log("send alert job weekly triggered");
        foreach ($job_data as $jobAlerEmail => $jobData) {
            EmailHelper::sendJobAlert($jobData);
        }
    }

    /**
     * send_job_alert_monthly
     *
     * @return void
     */
    public function send_job_alert_monthly()
    {
        $data = new Data();
        $job_data = $data->main('monthly');

        // foreach($job_data as $email_key => $jobs)
        // {
        //     $headers = ['Content-Type: text/html; charset=UTF-8'];
        //     $email = $email_key;
        //     $subject = 'Job Alert Monthly Report';

        //     $message = '<html><body>';
        //     $message .= '<h2>Job Alert Monthly Report</h2>';
        //     $message .= '<ul>';

        //     foreach ($jobs as $job_data) {
        //         $job_title = $job_data['title'];
        //         $job_url = $job_data['url'];

        //         $message .= '<li><a href="' . $job_url . '">' . $job_title . '</a></li>';
        //     }
        //     $message .= '</ul>';
        //     $message .= '</body></html>';

        //     wp_mail($email, $subject, $message, $headers);
        // }


        error_log("send alert job monthly triggered");
        foreach ($job_data as $jobAlerEmail => $jobData) {
            EmailHelper::sendJobAlert($jobData);
        }
    }
}

new CronJobAlert();
