<?php

namespace Cron;

defined('ABSPATH') or die("Direct access not allowed!");

use Vacancy\VacancyCrudController;

class CronVacancyApproval
{
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'setSchedules'], 10, 2);
        add_action('init', [$this, 'scheduling']);
        add_action('check_vacancy_approval', [$this, 'checkVacancyApproval']);
    }

    public function setSchedules($schedules)
    {
        $schedules['check_vacancy_approval'] = [
            'interval'  => 6 * HOUR_IN_SECONDS,
            'display'   => __('Every 6 Hour make request to flexfeed API.')
        ];

        return $schedules;
    }

    public function scheduling()
    {
        if (!wp_next_scheduled('check_vacancy_approval')) {
            wp_schedule_event(time(), 'check_vacancy_approval', 'check_vacancy_approval');
        }
    }

    public function checkVacancyApproval()
    {
        $vacancyController = new VacancyCrudController();

        try {
            $vacancyController->checkVacancyApprovalInLastHours();
        } catch (\Exception $e) {
            error_log('Cron flexfeed, Exception - ' . $e->getMessage());
        } catch (\Throwable $th) {
            error_log('Cron flexfeed, Throwable - ' . $th->getMessage());
        }
    }
}

// Initialize
new CronVacancyApproval();
