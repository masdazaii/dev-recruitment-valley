<?php

namespace Global;

use Request\ForgotPasswordRequest;
use Request\LoginRequest;
use WP_REST_Request;
use ResponseHelper;
use WP_Error;

class LoginService
{
    private $loginController;

    public function __construct()
    {
    
        $this->loginController = new LoginController;
    }

    public function login(WP_REST_Request $request)
    {
        $loginRequest = new LoginRequest($request);
        if(!$loginRequest->validate())
        {
            $errors = $loginRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $loginRequest->sanitize();
        $body = $loginRequest->getData();
        $response = $this->loginController->login($body);
        return ResponseHelper::build($response);
    }

    public function logout(WP_REST_Request $request)
    {
        $body = $request->get_header('Authorization');
        $response = $this->loginController->logout($body);
        return ResponseHelper::build($response);
    }

    public function forgotPassword( WP_REST_Request $request )
    {
        $forgotPasswordRequest = new ForgotPasswordRequest($request);
        if(!$forgotPasswordRequest->validate())
        {
            $errors = $forgotPasswordRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $forgotPasswordRequest->sanitize();
        $body = $forgotPasswordRequest->getData();
        
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
