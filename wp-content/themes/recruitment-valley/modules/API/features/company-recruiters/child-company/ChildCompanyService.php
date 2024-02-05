<?php

namespace Service;

use Controller\ChildCompanyController;
use Request\ChildCompany\CreateChildCompanyRequest;
use ResponseHelper;
use WP_REST_Request;

class ChildCompanyService
{
    protected $childCompanyController;

    public function __construct()
    {
        $this->childCompanyController = new ChildCompanyController();
    }

    /**
     * Setup Account Company Recruiter service
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function createChildCompany(WP_REST_Request $request)
    {
        $createChildCompanyRequest = new CreateChildCompanyRequest($request);
        if (!$createChildCompanyRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $createChildCompanyRequest->getFirstError(),
            ]);
        }

        $response = $this->childCompanyController->store($createChildCompanyRequest->sanitized());
        return ResponseHelper::build($response);
    }
}
