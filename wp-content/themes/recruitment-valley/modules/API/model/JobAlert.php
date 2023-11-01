<?php

namespace Model;

use Exception;

class JobAlert
{
    public $slug = 'jobalert';
    public $job_alert_id;
    public $job_alert;

    public $acf_working_hours_ja    = 'working_hours_ja';
    public $acf_salary_end_ja       = 'salary_end_ja';
    public $acf_salary_start_ja     = 'salary_start_ja';
    public $acf_sector_ja       = 'sector_ja';
    public $acf_experience_ja   = 'experience_ja';
    public $acf_role_ja         = 'role_ja';
    public $acf_location_ja     = 'location_ja';
    public $acf_type_of_employment_ja   = 'type_of_employment_ja';
    public $acf_education_ja    = 'education_ja';
    public $acf_email_ja        = 'email_ja';
    public $acf_last_name_ja    = 'last_name_ja';
    public $acf_first_name_ja   = 'first_name_ja';
    public $acf_date_save_ja    = 'date_save_ja';
    public $acf_email_frequency_ja  = 'email_frequency_ja';

    public $property = [
        'acf' => [],
        'meta' => []
    ];

    public function __construct($jobAlertID = null, $getAll = false)
    {
        if ($jobAlertID) {
            $this->job_alert_id = $jobAlertID;
            $this->job_alert = get_post($jobAlertID);
            if (!$this->job_alert) {
                throw new Exception("FAQ not found!");
            }

            if ($getAll) {
                $this->property['acf'] = get_fields($jobAlertID);
                $this->property['meta'] = get_post_meta($jobAlertID);
            }
        }
    }

    public function getter($key, $format, $type = 'acf')
    {
        if (array_key_exists($key, $this->property[$type])) {
            return $this->property[$type][$key];
        } else {
            if ($this->job_alert_id) {
                if ($type == 'meta') {
                    return get_post_meta($this->job_alert_id, $key, $format);
                } else {
                    return get_field($key, $this->job_alert_id, $format);
                }
            } else {
                throw new Exception('Please specify Job Alert!');
            }
        }
    }

    public function setter($key, $value, $type = "acf")
    {
        if ($this->job_alert_id) {
            switch ($type) {
                case 'meta':
                    return update_post_meta($this->job_alert_id, $key, $value);
                    break;
                default:
                    return update_field($key, $value, $this->job_alert_id);
                    break;
            }
        } else {
            throw new Exception('Please specify Job Alert!');
        }
    }

    public function getJobAlertFirstName()
    {
        return $this->getter($this->acf_first_name_ja, true);
    }
}
