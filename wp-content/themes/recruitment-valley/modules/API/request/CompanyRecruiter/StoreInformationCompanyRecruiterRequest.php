<?php

namespace Request;

use Request\BaseRequest;
use ValidifyMI\Validator;
use WP_REST_Request;

class StoreInformationCompanyRecruiterRequest extends BaseRequest
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
            "shortDescription"              => [],
            "secondaryEmploymentConditions" => [],
            "companyVideo"                  => ["max_file_size:2000000"],
            "gallery.*"                     => ["max_file_size:2000000"],
        ];
    }

    public function messages(): array
    {
        return [
            "companyVideo.max_file_size"    => __("Max file size is 2MB", THEME_DOMAIN),
            "companyVideo.*.max_file_size"  => __("Max file size is 2MB", THEME_DOMAIN)
        ];
    }

    public function sanitizer(): array
    {
        return [
            "shortDescription"              => "textarea",
            "secondaryEmploymentConditions" => "",
            "companyVideo"                  => "text",
            "gallery"                       => "",
        ];
    }
}
