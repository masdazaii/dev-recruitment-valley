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

    public function sendContact($request)
    {
        $validate = $this->_validate_request($request);

        if (!$validate['is_valid']) {
            return [
                "message"    => $this->_message->get('input.invalid_input'),
                "status" => 400
            ];
        }

        $arguments = [
            'post_name'     => $permalink ?? '',
            'post_title'    => $request['company-name'],
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
        $metaName = update_post_meta($contactID, '_contact_name', $request['name']);
        $metaMail = update_post_meta($contactID, '_contact_email', $request['email']);
        $metaName = update_post_meta($contactID, '_contact_phone', $request['phone-number']);

        return [
            "message"    => $this->_message->get('contact.success'),
            "status" => 200
        ];
    }

    private function _validate_request(array $request)
    {
        $response = [
            'is_valid' => true,
            'errors'   => []
        ];

        /** validate company-name */
        if (!isset($request['company-name']) || $request['company-name'] === '') {
            $response['is_valid'] = false;
            $response['errors']['company-name'][] = 'Company name is required.';
        }

        /** validate name */
        if (!isset($request['name']) || $request['name'] === '') {
            $response['is_valid'] = false;
            $response['errors']['name'][] = 'Name is required.';
        }

        /** validate email */
        if (!isset($request['email']) || $request['email'] === '') {
            $response['is_valid'] = false;
            $response['errors']['email'][] = 'Email is required.';
        }

        /** validate phone number */
        if (!isset($request['phone-number']) || $request['phone-number'] === '') {
            $response['is_valid'] = false;
            $response['errors']['phone-number'][] = 'Phone is required.';
        }

        return $response;
    }
}

new ContactController();
