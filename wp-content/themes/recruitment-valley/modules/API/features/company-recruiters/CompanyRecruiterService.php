<?php

namespace Service;

use Controller\CompanyRecruiterController;
use Request\SetupCompanyRecruitmentRequest;
use Request\StoreAddressCompanyRecruitmentRequest;
use Request\StoreDetailCompanyRecruitmentRequest;
use Request\StoreSocialCompanyRecruitmentRequest;
use Request\StoreInformationCompanyRecruiterRequest;
use ResponseHelper;
use WP_REST_Request;

class CompanyRecruiterService
{
    protected $companyRecruiterController;

    public function __construct()
    {
        $this->companyRecruiterController = new CompanyRecruiterController();
    }

    /**
     * Setup Account Company Recruiter service
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function setup(WP_REST_Request $request)
    {
        $setupRequest = new SetupCompanyRecruitmentRequest($request);
        if (!$setupRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $setupRequest->getFirstError(),
            ]);
        }

        // print('<pre>' . print_r($request->get_params(), true) . '</pre>');
        $response = $this->companyRecruiterController->setup($setupRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * Get Account Company Recruiter service
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function myProfile(WP_REST_Request $request)
    {
        $setupRequest = new SetupCompanyRecruitmentRequest($request);
        if (!$setupRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $setupRequest->getFirstError(),
            ]);
        }

        $response = $this->companyRecruiterController->myProfile($setupRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * POST / Update Detail Account Company Recruiter service
     *
     * companyName
     * sector
     * employeesTotal
     * phoneNumberCode
     * phoneNumber
     * website
     * kvkNumber
     * btwNumber
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function storeDetail(WP_REST_Request $request)
    {
        $storeDetailRequest = new StoreDetailCompanyRecruitmentRequest($request);
        if (!$storeDetailRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $storeDetailRequest->getFirstError(),
            ]);
        }

        $response = $this->companyRecruiterController->storeDetail($storeDetailRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * POST / Update Address Account Company Recruiter service
     *
     * country
     * street
     * city
     * postCode
     * countryCode
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function storeAddress(WP_REST_Request $request)
    {
        $storeAddressRequest = new StoreAddressCompanyRecruitmentRequest($request);
        if (!$storeAddressRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $storeAddressRequest->getFirstError(),
            ]);
        }

        $response = $this->companyRecruiterController->storeAddress($storeAddressRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * POST / Update Social Media Account Company Recruiter service
     *
     * facebook
     * instagram
     * linkedin
     * twitter
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function storeSocials(WP_REST_Request $request)
    {
        $storeSocialRequest = new StoreSocialCompanyRecruitmentRequest($request);
        if (!$storeSocialRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $storeSocialRequest->getFirstError(),
            ]);
        }

        $response = $this->companyRecruiterController->storeSocials($storeSocialRequest->sanitized());
        return ResponseHelper::build($response);
    }

    /**
     * POST / Update Information Account Company Recruiter service
     *
     * shortDescription
     * secondaryEmploymentConditions
     * companyVideo
     * gallery
     *
     * @param WP_REST_Request $request
     * @return void
     */
    public function storeInformation(WP_REST_Request $request)
    {
        $storeInformationRequest = new StoreInformationCompanyRecruiterRequest($request);
        if (!$storeInformationRequest->validate()) {
            return ResponseHelper::build([
                'status'    => 400,
                'message'   => $storeInformationRequest->getFirstError(),
            ]);
        }

        $response = $this->companyRecruiterController->storeInformation($storeInformationRequest->sanitized());
        return ResponseHelper::build($response);
    }

    // public function deleteGalleryItem(WP_REST_Request $request)
    // {
    //     $response = $this->companyRecruiterController->deleteGalleryItem();
    //     return ResponseHelper::build($response);
    // }
}
