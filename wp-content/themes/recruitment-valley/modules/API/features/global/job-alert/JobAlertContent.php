<?php

namespace JobAlert;

use WP_Query;


class Data
{

    /**
     * _mapping_obj_filter
     *
     * @var array
     */
    private $_mapping_obj_filter = [
        'education'         => 'education_ja',
        'type'              => 'type_of_employment_ja',
        'location'          => 'location_ja',
        'role'              => 'role_ja',
        'experience'        => 'experience_ja',
        'sector'            => 'sector_ja',
        'salaryStart'       => 'salary_start_ja',
        'salaryEnd'         => 'salary_end_ja',
        'workingHours'      => 'working_hours_ja',
    ];

    /**
     * _mapping_field_nonOBJ
     *
     * @var array
     */
    private $_mapping_field_nonOBJ = [
        'emailFrequency'    => 'email_frequency_ja',
        'firstName'         => 'first_name_ja',
        'lastName'          => 'last_name_ja',
        'email'             => 'email_ja',
        'dateSave'          => 'date_save_ja',
    ];

    /**
     * _job_taxonomy
     *
     * @var array
     */
    private $_job_taxonomy = [
        'sector',
        'role',
        'type',
        'education',
        'working-hours',
        'location',
        'experiences',
    ];


    /**
     * _getJobAlert
     *
     * @return array
     */
    private function _getJobAlert(): array
    {
        $args = array(
            'post_type'         => 'jobalert',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
        );

        $jobAlertQuery = new WP_Query($args);

        $meta = [];

        $idx = 0;
        foreach ($jobAlertQuery->posts as $post) {
            $jobAlertId     = $post->ID;
            $field_value    = get_fields($jobAlertId);
            $meta[$idx]['job_alert_id'] = $post->ID; // Added Line

            // non object data
            foreach ($this->_mapping_field_nonOBJ as $kMeta => $kAcf) {
                $meta[$idx][$kMeta] = $field_value[$kAcf];
            }

            // filter
            foreach ($this->_mapping_obj_filter as $kMeta => $kAcf) {
                $tmp = $field_value[$kAcf];

                if (!is_array($tmp)) {
                    $meta[$idx]['filter'][$kMeta] = $tmp;
                    continue;
                }

                $tmp = array_map(function ($dt) {
                    return [
                        'id'    => isset($dt->term_id) ? $dt->term_id : 0,
                        'name'  => isset($dt->name) ? $dt->name : "",
                    ];
                }, $tmp);

                $meta[$idx]['filter'][$kMeta] = $tmp;
            }

            $idx++;
        }

        return $meta;
    }

    /**
     * _getVacanciesOneMonth
     *
     * @return array
     */
    private function _getVacanciesOneMonth()
    {
        $one_month_ago = strtotime('-1 month');

        $args = [
            'post_type'         => 'vacancy',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'orderBy'           => 'date',
            'date_query'        => [
                [
                    'after' => date('Y-m-d', $one_month_ago),
                ],
            ],
        ];

        $query = new \WP_Query($args);

        $job = [];
        foreach ($query->posts as $post) {
            $entry                  = [];
            $entry['id']            = $post->ID;
            $entry['post_date_gmt'] = $post->post_date_gmt;
            $entry['url']           = get_permalink($post->ID);

            $entry['name']          = $post->post_title;
            $entry['slug']          = $post->post_name;
            $salary_start           = get_field('salary_start', $post->ID);
            $salary_end             = get_field('salary_end', $post->ID);
            foreach ($this->_job_taxonomy as $taxonomy) {
                $terms = wp_get_object_terms($post->ID, $taxonomy);

                foreach ($terms as $key => $value) {
                    $entry[$taxonomy]['term_id'][]  = isset($value->term_id) ? $value->term_id : 0;
                    $entry[$taxonomy]['name'][]     = isset($value->name) ? $value->name : '';
                }
            }
            $entry['salary_start']  = $salary_start !== '' ? $salary_start : '0';
            $entry['salary_end']    = $salary_end !== '' ? $salary_end : '0';
            $job[] = $entry;
        }
        return $job;
    }

    /**
     * _getContentEmail
     *
     * @param  mixed $vacancies
     * @param  mixed $jobAllert
     * @return void
     */
    private function _getContentEmail($vacancies, $jobAllert)
    {
        $result = [];
        foreach ($vacancies as $vacancie) {
            foreach ($jobAllert as $filter) {
                foreach ($filter['filter'] as $key => $ftl) {
                    if (!is_array($ftl)) continue;

                    $id = array_map(function ($data) {
                        return $data['id'];
                    }, $ftl);

                    if (!isset($vacancie[$key]['term_id'])) continue;

                    $vacancie_id    = $vacancie[$key]['term_id'];
                    $intersection   = array_intersect($vacancie_id, $id);

                    if ($intersection) {
                        $email = $filter['email'];
                        $jobId = $vacancie['id'];

                        if (!isset($result[$email])) {
                            $result[$email] = [
                                'email'     => $email,
                                'jobs'      => [],
                                'jobIds'    => [],

                                /** Added Line, add job_alert_id */
                                'jobAlertId' => $filter['job_alert_id']
                            ];
                        }

                        $job = [
                            'slug'      => isset($vacancie['slug']) ? $vacancie['slug'] : '',
                            'title'     => isset($vacancie['name']) ? $vacancie['name'] : '',
                            'post_date' => isset($vacancie['post_date_gmt']) ? $vacancie['post_date_gmt'] : '',
                        ];

                        $result[$email]['jobs'][$jobId] = $job;

                        if (!in_array($vacancie['id'], $result[$email]['jobIds'])) {
                            $result[$email]['jobIds'][] = $vacancie['id'];
                        }

                        $result[$email]['jobIds'] = array_unique($result[$email]['jobIds']);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * _mappingVacanciesPerSchedule
     *
     * @return void
     */
    private function _mappingVacanciesPerSchedule()
    {
        $vacancies = $this->_getVacanciesOneMonth();

        $Vday   = [];
        $Vweek  = [];
        $Vmonth = [];

        foreach ($vacancies as $vacancie) {
            $inputDate      = $vacancie['post_date_gmt'];
            $currentDate    = date('Y-m-d');

            $inputDateTime      = new \DateTime($inputDate);
            $currentDateTime    = new \DateTime($currentDate);

            $interval       = $inputDateTime->diff($currentDateTime);
            $daysDifference = $interval->days;

            if ($daysDifference == 0) {
                $Vday[] =  $vacancie;
            } elseif ($daysDifference <= 7) {
                $Vweek[]  = $vacancie;
            }

            $Vmonth[] = $vacancie;
        }

        return [
            'daily'     => $Vday,
            'weekly'    => $Vweek,
            'monthly'   => $Vmonth,
        ];
    }

    /**
     * mappingJobPerSchedule
     *
     * @return void
     */
    private function mappingJobPerSchedule()
    {
        $job_alert = $this->_getJobAlert();
        $Jday   = [];
        $Jweek  = [];
        $Jmonth = [];
        foreach ($job_alert as $persons) {
            if ($persons['emailFrequency'] === 'daily') {
                $Jday[]   = $persons;
            } elseif ($persons['emailFrequency'] === 'weekly') {
                $Jweek[]  = $persons;
            } elseif ($persons['emailFrequency'] === 'monthly') {
                $Jmonth[] = $persons;
            }
        }

        return [
            'daily'     => $Jday,
            'weekly'    => $Jweek,
            'monthly'   => $Jmonth,
        ];
    }

    /**
     * main
     *
     * @return array
     */
    public function main($schedule)
    {
        $vacancies  = $this->_mappingVacanciesPerSchedule();
        $jobAllert  = $this->mappingJobPerSchedule();
        $data       = $this->_getContentEmail($vacancies[$schedule], $jobAllert[$schedule]);

        return $data;
    }
}
