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

    public function sendContact(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->contactController->sendContact($body);
        return ResponseHelper::build($response);
    }
}
