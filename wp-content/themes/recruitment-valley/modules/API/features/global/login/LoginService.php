<?php

namespace Global;

use WP_REST_Request;
use ResponseHelper;

class LoginService
{
    private $loginController;

    public function __construct()
    {
        $this->loginController = new LoginController;
    }

    public function login(WP_REST_Request $request)
    {
        $body = $request->get_body();
        // print('<pre>' . print_r($req, true) . '</pre>');
        $response = $this->loginController->login($body);
        return ResponseHelper::build($response);
    }
}
