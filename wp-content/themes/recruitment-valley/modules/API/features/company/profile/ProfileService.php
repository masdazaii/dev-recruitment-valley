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

    public function post_address(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->post_address($request);
        return ResponseHelper::build($response);
    }

    public function post_socials(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->post_socials($request);
        return ResponseHelper::build($response);
    }

    public function post_information(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->post_information($request);
        return ResponseHelper::build($response);
    }

    public function delete_gallery(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->delete_gallery($request);
        return ResponseHelper::build($response);
    }
}
