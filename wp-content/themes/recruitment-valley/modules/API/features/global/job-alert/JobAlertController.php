<?php

namespace JobAlert;

use Constant\Message;
use Helper\ValidationHelper;
use WP_Query;

class JobAlertController
{
    private $message = null;

    private $_fields = [
        'emailFrequency'    => 'email_frequency_ja',
        'firstName'         => 'first_name_ja',
        'lastName'          => 'last_name_ja',
        'email'             => 'email_ja',
        'education'         => 'education_ja',
        'type'              => 'type_of_employment_ja',
        'location'          => 'location_ja',
        'role'              => 'role_ja',
        'experience'        => 'experience_ja',
        'sector'            => 'sector_ja',
        'salaryStart'       => 'salary_start_ja',
        'salaryEnd'         => 'salary_end_ja',
        'workingHours'      => 'working_hours_ja',
        'dateSave'          => 'date_save_ja',
    ];    


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

    private $_mapping_field_nonOBJ = [
        'emailFrequency'    => 'email_frequency_ja',
        'firstName'         => 'first_name_ja',
        'lastName'          => 'last_name_ja',
        'email'             => 'email_ja',
        'dateSave'          => 'date_save_ja',
    ];

    public function __construct()
    {
        $this->message = new Message;
    }

    public function jobAlert($request)
    {
        $body = $request->get_body();
        $body = json_decode($body, true);
        
        if(!isset($body)) {
            return [
                "status"    => 500,
                "message"   => $this->message->get('candidate.profile.email_alert_failed')
            ];
        }

        $validate = ValidationHelper::doValidate($body, [
            "emailFrequency"    => "required",
            "firstName"         => "required",
            "lastName"          => "required",
            "email"             => "required",
        ]);

        if (!$validate['is_valid']) return wp_send_json_error(['validation' => $validate['fields'], 'status' => 400], 400);

        $emailFrequency = isset($body['emailFrequency']) ? $body['emailFrequency'] : '';
        $firstName      = isset($body['firstName']) ? $body['firstName'] : '';
        $lastName       = isset($body['lastName']) ? $body['lastName'] : '';

        $title = $firstName . ' ' . $lastName . ' - ' . $emailFrequency;

        $postData = array(
            'post_title'   => $title,
            'post_status'  => 'publish',
            'post_type'    => 'jobalert',
        );

        $postId = wp_insert_post($postData);

        if (!is_wp_error($postId)) {

            $this->_updateMeta($postId, $body);

            return [
                "status"    => 200,
                "message"   => $this->message->get('candidate.profile.email_alert_success')
            ];
        }

        return [
            "status"    => 500,
            "message"   => $this->message->get('candidate.profile.email_alert_failed')
        ];
    }

    private function _updateMeta ($postId, $body)
    {
        $fields = $this->_fields;
        foreach ($fields as $post_param => $acf_key) {
            if (isset($body[$post_param])) {
                update_field($acf_key, $body[$post_param], $postId);
            }
        }
        $currentDate = date('d/m/Y');

        update_field('date_save_ja', $currentDate, $postId);
    }

    private function _getJobAlert() : array
    {
        $args = array(
            'post_type' => 'jobalert',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Jumlah posting yang akan diambil (-1 untuk semua)
            //  get yang tanggalnya >= hari ini.
        );
        
        $jobAlertQuery = new WP_Query($args);

        $meta = [];

        $idx = 0;
        foreach ($jobAlertQuery->posts as $post) {
            $jobAlertId = $post->ID;
            $field_value = get_fields($jobAlertId);     
            
            // non object data
            foreach($this->_mapping_field_nonOBJ as $kMeta => $kAcf) {
                $meta[$idx][$kMeta] = $field_value[$kAcf];
            }

       
            
            // filter
            foreach($this->_mapping_obj_filter as $kMeta => $kAcf) {
                $tmp = $field_value[$kAcf];

                if(!is_array($tmp)) {
                    $meta['filter'][$kMeta] = $tmp;
                    continue;
                }

                $tmp = array_map(function($dt){
                  return [
                    'id'    => isset($dt->term_id) ? $dt->term_id : 0,
                    'name'  => isset($dt->name) ? $dt->name : "",
                  ];

                }, $tmp);

                $meta[$idx]['filter'][$kMeta] = $tmp;
            }

            $idx++;

        }

        $selectedFilter = $this->_mapping_obj_filter;
        echo '<pre>';
        var_dump($meta);
        echo '</pre>';
        die;

        foreach($selectedFilter as $selected => $acf) {
            $aggregatedEmails = $this->aggregateEmailsByFilter($meta, $selected);
    
            echo '<pre>';
            var_dump( $aggregatedEmails);
            echo '</pre>';
        }
        die;

       

        return $meta;
    }


        
    /**
     * aggregateEmailsByFilter
     *
     * @param  mixed $data
     * @param  mixed $filterCategory
     * @return void
     * 
     * example how to use
     * 
     *  $selectedFilter = 'education'; // You can change this to any other filter category
        $aggregatedEmails = $this->aggregateEmailsByFilter($meta, $selectedFilter);
     */
    public function aggregateEmailsByFilter($data, $filterCategory) {
        $aggregatedData = array();

        foreach ($data as $entry) {
            $email = $entry['email'];

            foreach ($entry['filter'][$filterCategory] as $filter) {
                $filterName = $filter['name'];

                if (!isset($aggregatedData[$filterCategory][$filterName])) {
                    $aggregatedData[$filterCategory][$filterName] = array();
                }

                // Add email to the filter value only if it's not already there
                if (!in_array($email, $aggregatedData[$filterCategory][$filterName])) {
                    $aggregatedData[$filterCategory][$filterName][] = $email;
                }
            }
        }

        return $aggregatedData;
    }

    function generateFilter($inputArray, $categoryKey, $emailKey = "email") {
        $filter = [];
        $categories = [];
        $emailLabels = [];
    
        foreach ($inputArray[$categoryKey] as $category => $emails) {
            $categories[] = $category;
    
            foreach ($emails as $email) {
                $emailLabels[] = $email;
            }
        }
    
        $uniqueEmails = array_unique($emailLabels);
    
        $filter[$categoryKey] = implode(", ", $categories);
        $filter[$emailKey] = implode(", ", $uniqueEmails);
    
        return $filter;
    }
    
}