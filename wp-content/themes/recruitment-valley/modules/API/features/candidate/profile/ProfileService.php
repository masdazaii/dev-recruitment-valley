<?php

namespace Candidate\Profile;

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
        $response = $this->setupProfileController->get( $request );
        return ResponseHelper::build($response);
    }

    public function setup(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->setup($request);
        return ResponseHelper::build($response);
    }

    public function updatePhoto(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updatePhoto( $request );
        return ResponseHelper::build($response);
    }

    public function updateCv(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updateCv( $request );
        return ResponseHelper::build($response);
    }

    public function updateProfile(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updateProfile( $request );
        return ResponseHelper::build($response);
    }

    public function changeEmailRequest(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->changeEmailRequest($request);
        return ResponseHelper::build($response);
    }

    public function changeEmail(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->changeEmail($request);
        return ResponseHelper::build($response);
    }
}
