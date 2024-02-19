<?php

namespace Model;

use DateTime;
use Exception;
use WP_Error;
use WP_Post;
use WP_Query;

/**
 * CPT Company Recruiter Report Model class
 *
 */
class CompanyRecruiterReport extends BaseModel
{
    public const post_type = 'recruiter-report';

    public $id;
    public $report;

    public const meta_report_url        = 'url';
    public const meta_report_periode_start  = 'periode_start';
    public const meta_report_periode_end    = 'periode_end';

    public function __construct(Mixed $report = null)
    {
        $this->selector = '';
        $this->prefix   = 'rv_company_recruiter_report_';

        if ($report) {
            if (is_numeric($report)) {
                $this->id       = $report;
                $this->report   = get_post($report);
            } else if ($report instanceof WP_Post) {
                $this->id       = $report->ID;
                $this->report   = $report;
            }
        }
    }

    public function title()
    {
        return isset($this->report) ? $this->report->title : '';
    }

    public function getDownloadURL()
    {
        return $this->get(self::meta_report_url, true, 'meta');
    }

    public function getPeriodeStart($format = 'Y-m-d')
    {
        $value = $this->get(self::meta_report_periode_start, true, 'meta');
        if ($value) {
            if (is_string($value)) {
                $date = new DateTime($value);
                return $date->format($format);
            }
        }
    }

    public function getPeriodeEnd($format = 'Y-m-d')
    {
        $value = $this->get(self::meta_report_periode_end, true, 'meta');
        if ($value) {
            if (is_string($value)) {
                $date = new DateTime($value);
                return $date->format($format);
            }
        }
    }
}
