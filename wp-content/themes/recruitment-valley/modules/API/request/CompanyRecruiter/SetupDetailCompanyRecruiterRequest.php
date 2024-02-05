<?php

namespace Request;

use Request\BaseRequest;
use ValidifyMI\Validator;
use WP_REST_Request;

class StoreDetailCompanyRecruitmentRequest extends BaseRequest
{
    public function __construct(WP_REST_Request $request)
    {
        parent::__construct($request);
        $this->request      = $request;
        $this->validator    = Validator::make($this->request->get_params(), self::rules(), self::messages(), self::sanitizer());
    }

    public function rules(): array
    {
        return [
            "companyName"       => ["required"],
            "sector"            => ["required"],
            "sector.*"          => ["exists:term/sector/term_id/single"],
            "employeesTotal"    => [],
            "phoneNumberCode"   => [],
            "phoneNumber"       => [],
            "website"           => ["url"],
            "kvkNumber"         => [],
            "btwNumber"         => []
        ];
    }

    public function messages(): array
    {
        return [
            "companyName.required"  => __("Company Recruiter Name is required!", THEME_DOMAIN),
            "sector.required"       => __("Company Recruiter sector is required!", THEME_DOMAIN),
            "sector.*.exists"       => __("One of sector value is not exitst!", THEME_DOMAIN),
            "website.url"           => __("Website must be valid URL!", THEME_DOMAIN)
        ];
    }

    public function sanitizer(): array
    {
        return [
            "companyName"       => "text",
            "sector"            => "",
            "sector.*"          => "text",
            "employeesTotal"    => "text",
            "phoneNumber"       => "text",
            "phoneNumberCode"   => "text",
            "website"           => "text",
            "kvkNumber"         => "text",
            "btwNumber"         => "text"
        ];
    }
}
