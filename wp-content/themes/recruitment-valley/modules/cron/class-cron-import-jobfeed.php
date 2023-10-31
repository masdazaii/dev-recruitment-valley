<?php

namespace Cron;

defined('ABSPATH') or die("Direct access not allowed!");

use Vacancy\Import\Jobfeed\JobfeedController;

class CronImportJobfeed
{
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'setSchedules']);
        add_action('init', [$this, 'scheduling']);
        add_action('request_import_api_jobfeed', [$this, 'requestImportApiJobfeed']);
    }

    public function setSchedules($schedules)
    {
        /** Set cron interval, every 24 hour */
        $interval = 24;

        $schedules['jobfeed_request_per_24_hour'] = [
            'interval'  => floor($interval) * HOUR_IN_SECONDS,
            'display'   => __('Every ' . $interval . ' Hour make request to jobfeed API.')
        ];

        return $schedules;
    }

    public function scheduling()
    {
        if (!wp_next_scheduled('request_import_api_jobfeed')) {
            wp_schedule_event(time(), 'jobfeed_request_per_24_hour', 'request_import_api_jobfeed');
        }
    }

    public function requestImportApiJobfeed()
    {
        $jobFeedController = new JobfeedController();

        try {
            $jobFeedController->import([], 'all', 0);
            $jobFeedController->expire([], 'all', 0);
        } catch (\Exception $e) {
            error_log('Cron jobfeed, Exception - ' . $e->getMessage());
        } catch (\Throwable $th) {
            error_log('Cron jobfeed, Throwable - ' . $th->getMessage());
        }
    }
}

// Initialize
new CronImportJobfeed();
