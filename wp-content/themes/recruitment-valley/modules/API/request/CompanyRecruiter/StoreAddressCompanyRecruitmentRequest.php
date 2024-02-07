<?php

namespace Request\CompanyRecruiter;

use Request\BaseRequest;
use ValidifyMI\Validator;
use WP_REST_Request;

class StoreAddressCompanyRecruitmentRequest extends BaseRequest
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
            "country"           => ["required"],
            "city"              => ["required"],
            "street"            => ["required"],
            "postCode"          => ["required"],
            "countryCode"       => []
        ];
    }

    public function messages(): array
    {
        return [
            "country.required"      => __("Country is required!", THEME_DOMAIN),
            "city.required"         => __("City is required!", THEME_DOMAIN),
            "street.required"       => __("Street is required!", THEME_DOMAIN),
            "postCode.required"     => __("Post Code is required!", THEME_DOMAIN)
        ];
    }

    public function sanitizer(): array
    {
        return [
            "country"       => "text",
            "street"        => "text",
            "city"          => "text",
            "postCode"      => "text",
            "countryCode"   => "text"
        ];
    }
}
