<?php

namespace Company\Profile;

use WP_REST_Request;
use ResponseHelper;

class ProfileService
{
    private $setupProfileController;

    public function __construct()
    {
        $this->setupProfileController = new ProfileController;
    }

    public function get(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->get($request);
        return ResponseHelper::build($response);
    }
}
