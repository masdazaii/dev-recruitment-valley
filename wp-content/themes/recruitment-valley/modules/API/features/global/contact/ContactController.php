<?php

namespace Global;

use WP_User;
use Constant\Message;

class ContactController
{
    protected $_message;

    public function __construct()
    {
        $this->_message = new Message();
    }

    public function sendContact(array $request, String $from)
    {
        $validate = $this->_validate_request($request, $from);

        if (!$validate['is_valid']) {
            return [
                "message"    => $this->_message->get('input.invalid_input'),
                "errors"    => $validate['errors'],
                "req"   => $request,
                "status" => 400
            ];
        }

        $arguments = [
            'post_name'     => $permalink ?? '',
            'post_title'    => ($from === 'company' ? $request['companyName'] . ' - ' . $request['name'] : $request['firstName'] . ' ' . $request['lastName']),
            'post_content'  => $request['message'],
            'post_date'     => date('Y-m-d H:i:s', time()),
            'post_date_gmt' => gmdate('Y-m-d H:i:s', time()),
            'post_status'   => 'publish',
            'ping_status'   => 'closed',
            'post_parent'   => 0,
            'post_author'   => get_current_user_id(),
            'post_type'     => 'contacts',
            'comment_status' => 'closed',
            'page_template'  => 'default'
        ];

        $contactID = wp_insert_post($arguments, false, true);

        if (!$contactID) {
            return [
                "message"    => $this->_message->get('input.failed_to_store'),
                "status" => 400
            ];
        }

        /** Set contact meta */
        update_field('contact_sender_role', $from, $contactID);

        if ($from === 'company') {
            update_field('company_name', $request['companyName'], $contactID);
            update_field('company_sender_name', $request['name'], $contactID);
        } else {
            update_field('job_seeker_first_name', $request['firstName'], $contactID);
            update_field('job_seeker_last_name', $request['lastName'], $contactID);
        }
        update_field('phone_number', $request['phoneNumber'], $contactID);
        update_field('phone_number_code_area', $request['phoneNumberCode'], $contactID);

        if ($request['email']) {
            update_field('email', $request['email'], $contactID);
        }

        /** Send email to admin */
        // $admin = get_users('role=Administrator');
        // $adminHeaders[] = 'From: ' . $request['email'] . '<' . $request['email']  . '>';
        // $subject = 'NEW MESSAGE - ';
        // if ($from === 'company') {
        //     $subject .= $request['companyName'] . ' [' . $request['name'] . ']';
        // } else {
        //     $subject .= $request['firstName'] . $request['lastName'];
        // }
        // foreach ($admin as $user) {
        //     wp_mail($user->user_email, $subject, $request['message'], $adminHeaders);
        // }

        return [
            "message"    => $this->_message->get('contact.success'),
            "status" => 200
        ];
    }

    private function _validate_request(array $request, String $from)
    {
        $response = [
            'is_valid' => true,
            'errors'   => []
        ];

        /** Validate Phone Number */
        if (!isset($request['phoneNumber']) || $request['phoneNumber'] === "") {
            $response['is_valid'] = false;
            $response['errors']['phoneNumber'][] = $this->_message->get('contact.invalid_input.phone_number_required');
        }

        /** Validate Phone Number Code */
        if (!isset($request['phoneNumberCode']) || $request['phoneNumberCode'] === "") {
            $response['is_valid'] = false;
            $response['errors']['phoneNumberCode'][] = $this->_message->get('contact.invalid_input.phone_number_code_required');
        }

        /** Validate Phone Number Code */
        if (!isset($request['message']) || $request['message'] === "") {
            $response['is_valid'] = false;
            $response['errors']['message'][] = $this->_message->get('contact.invalid_input.message_required');
        }

        /** Validate Email */
        // if (!isset($request['email']) || $request['email'] === "") {
        //     $response['is_valid'] = false;
        //     $response['errors']['email'][] = $this->_message->get('contact.invalid_input.email_required');
        // } else if (!filter_var($request['reservation-email'], FILTER_VALIDATE_EMAIL)) {
        //     $result['is_valid'] = false;
        //     $response['errors']['email'][] = $this->_message->get('contact.invalid_input.email_invalid');
        // }

        if ($from === 'company') {
            /** Validate Company Name */
            if (!isset($request['companyName']) || $request['companyName'] === "") {
                $response['is_valid'] = false;
                $response['errors']['companyName'][] = $this->_message->get('contact.invalid_input.company_name_required');
            }

            /** Validate Name */
            if (!isset($request['name']) || $request['name'] === "") {
                $response['is_valid'] = false;
                $response['errors']['name'][] = $this->_message->get('contact.invalid_input.company_sender_name_required');
            }
        } else {
            /** Validate First Name */
            if (!isset($request['firstName']) || $request['firstName'] === "") {
                $response['is_valid'] = false;
                $response['errors']['firstName'][] = $this->_message->get('contact.invalid_input.job_seeker_first_name_required');
            }

            /** Validate Last Name */
            if (!isset($request['lastName']) || $request['lastName'] === "") {
                $response['is_valid'] = false;
                $response['errors']['lastName'][] = $this->_message->get('contact.invalid_input.job_seeker_last_name_required');
            }
        }

        return $response;
    }
}

new ContactController();
