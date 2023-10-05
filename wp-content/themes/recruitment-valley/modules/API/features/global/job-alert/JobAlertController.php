<?php

namespace JobAlert;

use Constant\Message;
use Helper\ValidationHelper;
use JWTHelper;
use WP_Error;
use WP_Query;
use WP_REST_Request;

class JobAlertController
{
    private $message = null;

    private $wpdb;

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

    public function __construct()
    {
        global $wpdb;

        $this->message = new Message;
        $this->wpdb = $wpdb;
    }

    public function jobAlert($request)
    {
        $body = $request->get_body();
        $body = json_decode($body, true);
        
        if(!isset($body)) {
            return [
                "status"    => 500,
                "message"   => $this->message->get('job_alert.email_alert_failed')
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
                "message"   => $this->message->get('job_alert.email_alert_success')
            ];
        }

        return [
            "status"    => 500,
            "message"   => $this->message->get('job_alert.email_alert_failed')
        ];
    }

    public function unsubscribe( WP_REST_Request $request )
    {
        $checkedToken = JWTHelper::check( $request["token"] );

        // mean error to pass into response helper
        if (is_array($checkedToken)) {
            return $checkedToken;
        }

        $jobAlertId = $checkedToken->job_alert_id;

        $jobAlert = get_post($jobAlertId);

        if(!$jobAlert)
        {
            return [
                "status" => 404,
                "message" => "Job alert niet gevonden"
            ];
        }

        try {
            $this->wpdb->query("START TRANSACTION");

            $args = [
                'ID' => $jobAlert->ID,
                'post_status' => "draft"
            ];

            wp_update_post($args);

            $this->wpdb->query("COMMIT");

            return [
                "status" => 200,
                "message"=> "u hebt de jobwaarschuwing met succes uitgeschreven"
            ];
        } catch (\Throwable $th) {
            $this->wpdb->query('ROLLBACK');
            error_log($th->getMessage());
            return [
                "status" => 400,
                "message"=> "Uitschrijven taakwaarschuwing mislukt"
            ];
        } catch (WP_Error $error){
            $this->wpdb->query('ROLLBACK');
            error_log($error->get_error_message());
            return [
                "status" => 400,
                "message"=> "Uitschrijven taakwaarschuwing mislukt"
            ];
        }
    }

    private function _updateMeta ($postId, $body)
    {
        $fields = $this->_fields;
        foreach ($fields as $post_param => $acf_key) {
            if (isset($body[$post_param])) {
                update_field($acf_key, $body[$post_param], $postId);
            }
        }
        $currentDate = date('d-m-Y');

        update_field('date_save_ja', $currentDate, $postId);
    }
}