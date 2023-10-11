<?php

namespace Cron;

defined('ABSPATH') or die("Direct access not allowed!");

use Vacancy\Import\Xml\FlexFeedController;

class CronImportFlexfeed
{
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'setSchedules']);
        add_action('init', [$this, 'scheduling']);
        add_action('request_import_api_flexfeed', [$this, 'requestImportApiFlexfeed']);
    }

    public function setSchedules($schedules)
    {
        /** Set cron interval */
        $optionManyTimeADay = get_field('import_api_refresh_per_day', 'option');
        if ($optionManyTimeADay && !empty($optionManyTimeADay)) {
            $interval = 24 / (int)$optionManyTimeADay; // 24 divide by how many time a day admin want to get data.
        } else {
            $interval = 12; // Default : set cron each 12 hour (2 times a day)
        }

        $schedules['flexfeed_request_per_x_hour'] = [
            'interval'  => floor($interval) * HOUR_IN_SECONDS,
            'display'   => __('Every ' . $interval . ' Hour make request to flexfeed API.')
        ];

        return $schedules;
    }

    public function scheduling()
    {
        if (!wp_next_scheduled('request_import_api_flexfeed')) {
            wp_schedule_event(time(), 'flexfeed_request_per_x_hour', 'request_import_api_flexfeed');
        }
    }

    public function requestImportApiFlexfeed()
    {
        $flexFeedController = new FlexFeedController(FLEXFEED_API_URL ?? NULL);

        try {
            $flexFeedController->import(100);
        } catch (\Exception $e) {
            error_log('Cron flexfeed, Exception - ' . $e->getMessage());
        } catch (\Throwable $th) {
            error_log('Cron flexfeed, Throwable - ' . $th->getMessage());
        }
    }
}

// Initialize
new CronImportFlexfeed();
