<?php

namespace RefreshToken;

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
        $body = $request->get_params();
        $response = $this->refreshTokenController->refresh($body);
        return ResponseHelper::build($response);
    }
}