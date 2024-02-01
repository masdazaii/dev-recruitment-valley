<?php

namespace Service;

use Controller\CompanyRecruiterController;
use Request\SetupCompanyRecruitmentRequest;
use ResponseHelper;
use WP_REST_Request;

class CompanyRecruiterService
{
    protected $companyRecruiterController;

    public function __construct()
    {
        $this->companyRecruiterController = new CompanyRecruiterController();
    }

    public function setup(WP_REST_Request $request)
    {
        $setupRequest = new SetupCompanyRecruitmentRequest($request);
        if (!$setupRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $setupRequest->getFirstError(),
            ]);
        }

        $response = $this->companyRecruiterController->setup($setupRequest->sanitized());
        return ResponseHelper::build($response);
    }
}
