<?php

namespace Global;

use BD\Emails\Email;
use Request\RegisterRequest;
use Request\ResendOtpRequest;
use Request\ValidateOtpRequest;
use WP_REST_Request;
use ResponseHelper;

class RegistrationService
{
    private $registrationController;

    public function __construct()
    {
        $this->registrationController = new RegistrationController;
    }

    public function register(WP_REST_Request $request)
    {
        $registerRequest = new RegisterRequest($request);

        if(!$registerRequest->validate())
        {
            $errors = $registerRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $registerRequest->sanitize();
        $body = $registerRequest->getData();

        $response = $this->registrationController->registration($body);
        return ResponseHelper::build($response);
    }

    public function validateOTP(WP_REST_Request $request)
    {
        $validateOtpRequest = new ValidateOtpRequest($request);

        if(!$validateOtpRequest->validate())
        {
            $errors = $validateOtpRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $validateOtpRequest->sanitize();
        $body = $validateOtpRequest->getData();

        $body = $request->get_params();
        $response = $this->registrationController->validateOTP($body);

        $user = get_user_by("email", $body["email"]);
        if($response['status'] === 200 && $user) {
            if (in_array('candidate', $user->roles)) {
                $args = [
                    'applicant.firstName' => $user->user_email,
                    'applicant.lastName' => '',
                ];

                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                );

                $content = Email::render_html_email('create-account-candidate.php', $args);
                wp_mail($body['email'], "Bevestiging aanmaken account", $content, $headers);
            }
        }

        return ResponseHelper::build($response);
    }

    public function resendOTP(WP_REST_Request $request)
    {
        $resendOtpRequest = new ResendOtpRequest($request);
        if(!$resendOtpRequest->validate())
        {
            $errors = $resendOtpRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $resendOtpRequest->sanitize();
        $body = $resendOtpRequest->getData();
        $response = $this->registrationController->resendOTP($body);
        return ResponseHelper::build($response);
    }
}
