<?php

namespace Cron;

use Controller\CompanyRecruiterController;
use DateTimeImmutable;

defined('ABSPATH') or die("Direct access not allowed!");

class CronCompanyRecruiterReport
{
    public function __construct()
    {
        add_filter('cron_schedules', [$this, 'setSchedules']);
        add_action('init', [$this, 'scheduling']);
        add_action('create_company_recruiter_report', [$this, 'createReport']);
    }

    public function setSchedules($schedules)
    {
        /** Set cron interval */
        $interval   = 24; // Run cron everyday

        $schedules['company_recruiter_report_each_first_of_the_month']  = [
            'interval'  => floor($interval) * HOUR_IN_SECONDS,
            'display'   => __("Make report of company recruiter each start of the month.")
        ];

        return $schedules;
    }

    public function scheduling()
    {
        if (!wp_next_scheduled('create_company_recruiter_report')) {
            wp_schedule_event(time(), 'company_recruiter_report_each_first_of_the_month', 'create_company_recruiter_report');
        }
    }

    public function createReport()
    {
        /** Get today's date */
        $now    = new DateTimeImmutable("now");
        $today  = $now->format('Y-m-d');
        $todayDate  = $now->format('d');

        /** Check if first date of the month */
        // if ($todayDate == 1) {
        //     /** Create Report */
        //     $companyRecruiterController = new CompanyRecruiterController();
        //     $companyRecruiterController->report([
        //         'filter'    => [
        //             'companyRecruiter' => 'all'
        //         ]
        //     ]);
        // }
        /** Create Report */
        $companyRecruiterController = new CompanyRecruiterController();
        $companyRecruiterController->report([
            'filter'    => [
                'companyRecruiter' => 'all'
            ]
        ]);
    }
}

/** Initialize */
new CronCompanyRecruiterReport();
