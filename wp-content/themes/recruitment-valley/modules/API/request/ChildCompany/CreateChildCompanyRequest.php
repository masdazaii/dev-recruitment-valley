<?php

namespace Request\ChildCompany;

use Request\BaseRequest;
use ValidifyMI\Validator;
use WP_REST_Request;

class CreateChildCompanyRequest extends BaseRequest
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
            "btwNumber"         => [],
            "facebook"          => ["url"],
            "instagram"         => ["url"],
            "linkedin"          => ["url"],
            "twitter"           => ["url"],
            "country"           => ["required"],
            "city"              => ["required"],
            "street"            => ["required"],
            "postCode"          => ["required"],
            "shortDescription"              => [],
            "secondaryEmploymentConditions" => [],
            "companyVideo"                  => ["max_file_size:2000000"],
            "image"                         => ["max_file_size:2000000"],
            "longitude"                     => [],
            "latitude"                      => [],
            "countryCode"                   => [],
            "companyEmail"                  => ["required", "email"]
        ];
    }

    public function messages(): array
    {
        return [
            "companyName.required"  => __("Company Recruiter Name is required!", THEME_DOMAIN),
            "sector.required"       => __("Company Recruiter sector is required!", THEME_DOMAIN),
            "sector.*.exists"       => __("One of sector value is not exitst!", THEME_DOMAIN),
            "website.url"           => __("Website must be valid URL!", THEME_DOMAIN),
            "facebook.url"          => __("Facebook must be valid URL!", THEME_DOMAIN),
            "instagram.url"         => __("Instagram must be valid URL!", THEME_DOMAIN),
            "linkedin.url"          => __("LinkedIn must be valid URL!", THEME_DOMAIN),
            "twitter.url"           => __("Twitter must be valid URL!", THEME_DOMAIN),
            "country.required"      => __("Country is required!", THEME_DOMAIN),
            "city.required"         => __("City is required!", THEME_DOMAIN),
            "street.required"       => __("Street is required!", THEME_DOMAIN),
            "postCode.required"     => __("Post Code is required!", THEME_DOMAIN),
            "image.max_file_size"   => __("Max file size is 2MB", THEME_DOMAIN),
            "companyVideo.max_file_size"    => __("Max file size is 2MB", THEME_DOMAIN),
            "companyEmail.required"         => __("Company Email is required!", THEME_DOMAIN),
            "companyEmail.email"            => __("Company Email is not valid!", THEME_DOMAIN)
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
            // "email" => "email",
            "website"           => "text",
            "kvkNumber"         => "text",
            "btwNumber"         => "text",
            "facebook"          => "text",
            "instagram"         => "text",
            "linkedin"          => "text",
            "twitter"           => "text",
            "country"           => "text",
            "street"            => "text",
            "city"              => "text",
            "postCode"          => "text",
            "shortDescription"  => "textarea",
            "secondaryEmploymentConditions" => "",
            "companyVideo"                  => "text",
            "gallery"                       => "",
            "image"                         => "",
            "longitude"                     => "",
            "latitude"                      => "",
            "countryCode"                   => "text",
            "companyEmail"                  => "text"
        ];
    }
}
