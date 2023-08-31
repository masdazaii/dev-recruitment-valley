<?php

namespace Request;

use V\Rules\Validator;
use WP_REST_Request;

class CreatePaidJobRequest implements MiRequest
{
    private $_request;
    private $_validator;

    public function __construct(WP_REST_Request $request)
    {
        $this->_request = $request;
        $this->_validator = new Validator($this->_request->get_params(), $this->rules(), $this->sanitizeRules());
    }

    public function rules(): array
    {
        return [
            "name" => ['required'],
            "description" => ["required"],
            "city" => ['required'],
            "placementAddress" => ["required"],
            "terms" => ["required"],
            "salaryStart" => ["numeric"],
            "salaryEnd" => ["numeric"],
            "sector.*" => ["required", "numeric"],
            "role.*" => ["required", "numeric"],
            "workingHours.*" => ["required", "numeric"],
            "location.*" => ["required", "numeric"],
            "education.*" => ["required", "numeric"],
            "employmentType.*" => ["required", "numeric"],
            "externalUrl" => ["url"],
            "applicationProcedureTitle" => [],
            "applicationProcedureText" => [],
            "applicationProcedureSteps.*" => [],
            "video" => ["url"],
            "facebook" => ["url"],
            "linkedin" => ["url"],
            "instagram" => ["url"],
            "twitter" => ["url"],
            "review" => [],
            "experiences.*" => ["numeric"], // Added Line
            "galleryJob.*" => [], // Added Line
            "galleryCompany.*" => [] // Added Line
        ];
    }

    public function sanitizeRules()
    {
        return [
            "name" => "text",
            "description" => "ksespost",
            "city" => "text",
            "placementAddress" => "text",
            "terms" => "ksespost",
            "salaryStart" => "text",
            "salaryEnd" => "text",
            "sector.*" => "text",
            "role.*" => "text",
            "workingHours.*" => "text",
            "location.*" => "text",
            "education.*" => "text",
            "employmentType.*" => "text",
            "externalUrl" => "text",
            "applicationProcedureTitle" => "text",
            "applicationProcedureText" => "text",
            "applicationProcedureSteps.*" => "text",
            "video" => "",
            "facebook" => "",
            "linkedin" => "",
            "instagram" => "",
            "twitter" => "",
            "review" => "",
            "experiences.*" => "text"
        ];
    }

    public function validate(): bool
    {
        return $this->_validator->validate();
    }

    public function sanitize()
    {
        return $this->_validator->tempSanitize();
    }

    public function getData(): array
    {
        return $this->_validator->getData();
    }

    public function getErrors(): array
    {
        return [
            "status" => 400,
            "message" => $this->_validator->getErrors(),
        ];
    }
}
