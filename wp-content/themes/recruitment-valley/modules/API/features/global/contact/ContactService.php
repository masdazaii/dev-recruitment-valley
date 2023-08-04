<?php

namespace Global;

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
        return ResponseHelper::build($response);
    }

    public function sendContactJobSeeker(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->contactController->sendContact($body, 'candidate');
        return ResponseHelper::build($response);
    }
}
