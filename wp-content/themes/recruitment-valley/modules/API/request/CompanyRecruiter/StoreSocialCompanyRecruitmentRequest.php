<?php

namespace Request;

use Request\BaseRequest;
use ValidifyMI\Validator;
use WP_REST_Request;

class StoreSocialCompanyRecruitmentRequest extends BaseRequest
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
            "facebook"  => ["url"],
            "instagram" => ["url"],
            "linkedin"  => ["url"],
            "twitter"   => ["url"]
        ];
    }

    public function messages(): array
    {
        return [
            "website.url"   => __("Website must be valid URL!", THEME_DOMAIN),
            "facebook.url"  => __("Facebook must be valid URL!", THEME_DOMAIN),
            "instagram.url" => __("Instagram must be valid URL!", THEME_DOMAIN),
            "linkedin.url"  => __("LinkedIn must be valid URL!", THEME_DOMAIN),
            "twitter.url"   => __("Twitter must be valid URL!", THEME_DOMAIN)
        ];
    }

    public function sanitizer(): array
    {
        return [
            "kvkNumber" => "text",
            "btwNumber" => "text",
            "facebook"  => "text",
            "instagram" => "text",
            "linkedin"  => "text",
            "twitter"   => "text"
        ];
    }
}
