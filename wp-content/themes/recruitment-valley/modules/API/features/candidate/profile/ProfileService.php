<?php

namespace Candidate\Profile;

use WP_REST_Request;
use ResponseHelper;
use Constant\Message;
use Helper\ValidationHelper;

class ProfileService
{
    private $_message;
    private $setupProfileController;

    public function __construct()
    {
        $this->_message = new Message();
        $this->setupProfileController = new ProfileController;
    }

    public function get(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->get($request);
        return ResponseHelper::build($response);
    }

    public function setup(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->setup($request);
        return ResponseHelper::build($response);
    }

    public function updatePhoto(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updatePhoto($request);
        return ResponseHelper::build($response);
    }

    public function updateCv(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updateCv($request);
        return ResponseHelper::build($response);
    }

    public function updateProfile(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updateProfile($request);
        return ResponseHelper::build($response);
    }

    public function changeEmailRequest(WP_REST_Request $request)
    {
        // $params = $request->get_params(); // Changed Line

        /** Changes Start here */
        $validator = new ValidationHelper('candidateChangeEmail', $request->get_params());

        if (!$validator->tempValidate()) {
            $errors = $validator->getErrors();
            return ResponseHelper::build([
                'message' => $this->_message->get('candidate.change_email_request.invalid'),
                'errors' => $errors,
                'status' => 400
            ]);
        }

        $validator->tempSanitize();
        $request = $validator->getData();
        /** Changes End here */

        $response = $this->setupProfileController->changeEmailRequest($request);
        return ResponseHelper::build($response);
    }

    public function changeEmail(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->changeEmail($request);
        return ResponseHelper::build($response);
    }

    public function changePassword(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->changePassword($request);
        return ResponseHelper::build($response);
    }
}
