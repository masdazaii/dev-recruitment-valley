<?php

namespace RefreshToken;

use Request\RefreshTokenRequest;
use ResponseHelper;
use WP_REST_Request;

class RefreshTokenService
{
    public $refreshTokenController;

    public function __construct()
    {
        $this->refreshTokenController = new RefreshTokenController;
    }

    public function refresh(WP_REST_Request $request)
    {
        $refreshTokenRequest = new RefreshTokenRequest($request);
        if(!$refreshTokenRequest->validate())
        {
            $errors = $refreshTokenRequest->getErrors();
            return ResponseHelper::build($errors);
        }

        $refreshTokenRequest->sanitize();
        $body = $refreshTokenRequest->getData();
        $response = $this->refreshTokenController->refresh($body);
        return ResponseHelper::build($response);
    }
}