<?php

namespace Global;

use BD\Emails\Email;
use WP_REST_Request;
use ResponseHelper;

class ContactService
{
    private $contactController;

    public function __construct()
    {
        $this->contactController = new ContactController;
    }

    public function sendContactEmployer(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->contactController->sendContact($body, 'company');

        $this->_send_email_contact_to_admin($response, $body, 'company');
        $this->_send_email_to_sender($response, $body, 'company');

        return ResponseHelper::build($response);
    }

    public function sendContactJobSeeker(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->contactController->sendContact($body, 'candidate');

        $this->_send_email_contact_to_admin($response, $body, 'candidate');
        $this->_send_email_to_sender($response, $body, 'candidate');

        return ResponseHelper::build($response);
    }

    private function _send_email_contact_to_admin($response, $body, $type = 'candidate')
    {
        if ($response['status'] !== 200) return;

        $args = [
            'contact.firstName' => $type === 'candidate' ? $body['firstName'] : $body['companyName'],
            'contact.lastName'  => $type === 'candidate' ? $body['lastName'] : $body['name'],
            'contact.phone'  => "(+" . $body['phoneNumberCode'] . ") " . $body['phoneNumber'],
            'contact.email'  => $body['email'],
            'contact.remark'  => $body['message'] ?? "",
        ];
        $content = Email::render_html_email('contact-form-admin.php', $args);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $site_title = get_bloginfo('name');
        $site_email = get_option('admin_email');

        wp_mail($site_email, "Contact Bevestiging - $site_title", $content, $headers);
    }

    private function _send_email_to_sender($response, $body, $type = 'candidate')
    {
        $email = $body['email'];
        if ($response['status'] !== 200 || !isset($email)) return;

        $args = [
            'contact.firstName' => $type === 'candidate' ? $body['firstName'] : $body['companyName'],
            'contact.lastName'  => $type === 'candidate' ? $body['lastName'] : $body['name'],
        ];

        $content = Email::render_html_email('contact-form-confirmation-client.php', $args);

        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
        );

        $site_title = get_bloginfo('name');
        wp_mail($email, "Contactbevestiging - $site_title", $content, $headers);
    }
}
