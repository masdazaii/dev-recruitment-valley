<?php

namespace Company\Profile;

use WP_REST_Request;
use ResponseHelper;
use Constant\Message;
use Helper\ValidationHelper;

class ProfileService
{
    private $setupProfileController;
    private $_message;

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
        $extraRules = [
            'gallery' => ["max_stored:10,file/user/meta/ucma_gallery_photo," . $request->user_id]
        ];

        $validator = new ValidationHelper('companyUpdateInformation', $request->get_params(), $extraRules);

        if (!$validator->tempValidate()) {
            $errors = $validator->getErrors();
            return ResponseHelper::build([
                'message' => $this->_message->get('company.profile.update_failed'),
                'errors' => $errors,
                'status' => 400
            ]);
        }

        $response = $this->setupProfileController->post_information($request);
        return ResponseHelper::build($response);
    }

    public function post_detail(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->post_detail($request);
        return ResponseHelper::build($response);
    }

    public function delete_gallery(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->delete_gallery($request);
        return ResponseHelper::build($response);
    }

    public function updatePhoto(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->updatePhoto($request);
        return ResponseHelper::build($response);
    }

    public function updateDetail(WP_REST_Request $request)
    {
        $body = $request->get_params();
        $response = $this->setupProfileController->updateDetail($body);
        return ResponseHelper::build($response);
    }

    public function setup(WP_REST_Request $request)
    {
        $extraRules = [
            'gallery' => ["max_stored:10,file/user/meta/ucma_gallery_photo," . $request['user_id']]
        ];

        $validator = new ValidationHelper('companySetupProfile', $request->get_params(), $extraRules);

        if (!$validator->tempValidate()) {
            $errors = $validator->getErrors();
            return ResponseHelper::build([
                'message' => $this->_message->get('company.profile.setup_invalid'),
                'errors' => $errors,
                'status' => 400
            ]);
        }

        $body = $validator->tempSanitize();
        $body = $validator->getData();

        $response = $this->setupProfileController->setup($body);
        return ResponseHelper::build($response);
    }

    public function getPhoto(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->getPhoto($request->get_params());
        return ResponseHelper::build($response);
    }

    public function getCredit(WP_REST_Request $request)
    {
        $response = $this->setupProfileController->getCredit($request);
        return ResponseHelper::build($response);
    }
}
