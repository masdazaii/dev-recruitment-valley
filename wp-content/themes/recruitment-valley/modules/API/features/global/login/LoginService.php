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
        $body = $request->get_params();
        $response = $this->loginController->login($body);
        return ResponseHelper::build($response);
    }

    public function logout(WP_REST_Request $request)
    {
        // $body = $request->get_header('authorization');
        // $response = $this->loginController->logout($body);
        // return ResponseHelper::build($response);
    }

    public function forgotPassword( WP_REST_Request $request )
    {
        $body = $request->get_params();
        $response = $this->loginController->forgot_password($body);
        return ResponseHelper::build($response);
    }

    public function resetPassword(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->loginController->reset_password($body);
        return ResponseHelper::build($response);
    }

}
