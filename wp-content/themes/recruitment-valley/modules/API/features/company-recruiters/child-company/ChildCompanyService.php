<?php

namespace Service;

use Controller\ChildCompanyController;
use Request\ChildCompany\CreateChildCompanyRequest;
use Request\ChildCompany\ShowChildCompanyRequest;
use Request\ChildCompany\UpdateChildCompanyRequest;
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
     * Create Child Company Recruiter service
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

    /**
     * List Child Company Recruiter service
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function listChildCompany(WP_REST_Request $request)
    {
        $response = $this->childCompanyController->list($request->get_params());
        return ResponseHelper::build($response);
    }

    /**
     * Show Single Child Company Recruiter service
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function showChildCompany(WP_REST_Request $request)
    {
        $showChildCompanyRequest = new ShowChildCompanyRequest($request);
        if (!$showChildCompanyRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $showChildCompanyRequest->getFirstError(),
            ]);
        }

        $response = $this->childCompanyController->show($showChildCompanyRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * Update Child Company Recruiter service
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function updateChildCompany(WP_REST_Request $request)
    {
        $updateChildCompanyRequest = new UpdateChildCompanyRequest($request);
        if (!$updateChildCompanyRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $updateChildCompanyRequest->getFirstError(),
            ]);
        }

        $response = $this->childCompanyController->update($updateChildCompanyRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * Get Child Company data for create vacancy default value service
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getCreatePaidVacancyDefaultValue(WP_REST_Request $request)
    {
        $showChildCompanyRequest = new ShowChildCompanyRequest($request);
        if (!$showChildCompanyRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $showChildCompanyRequest->getFirstError(),
            ]);
        }

        $response = $this->childCompanyController->getVacancyDefaultValue($showChildCompanyRequest->sanitized());
        return ResponseHelper::build($response);
    }
}
