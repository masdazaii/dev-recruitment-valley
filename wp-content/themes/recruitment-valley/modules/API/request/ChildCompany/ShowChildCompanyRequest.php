<?php

namespace Request\ChildCompany;

use Model\ChildCompany;
use Request\BaseRequest;
use ValidifyMI\Validator;
use WP_REST_Request;

class ShowChildCompanyRequest extends BaseRequest
{
    public function __construct(WP_REST_Request $request)
    {
        parent::__construct($request);
        $this->request      = $request;
        $this->validator    = Validator::make($this->request->get_params(), self::rules(), self::messages(), self::sanitizer());
    }

    public function rules(): array
    {
        if (is_numeric($this->request->get_params()['childCompany'])) {
            return [
                'childCompany'  => ['exists:post/child-company/id']
            ];
        } else if (is_string($this->request->get_params()['childCompany']) && (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->request->get_params()['childCompany']) !== 1)) {
            return [
                'childCompany'  => ['exists:post/child-company/meta/rv_urecruiter_child_company_uuid']
            ];
        } else {
            return [
                'childCompany'  => ['exists:post/child-company/slug']
            ];
        }
    }

    public function messages(): array
    {
        return [
            "childCompany.exists"  => __("Child company not found!", THEME_DOMAIN)
        ];
    }

    public function sanitizer(): array
    {
        return [
            "childCompany"  => "text",
        ];
    }
}
