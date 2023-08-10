<?php

namespace Global;

use RegisterRequest;
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
        $body = $request->get_params();
        $response = $this->registrationController->validateOTP($body);
        return ResponseHelper::build($response);
    }

    public function resendOTP(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->registrationController->resendOTP($body);
        return ResponseHelper::build($response);
    }
}
