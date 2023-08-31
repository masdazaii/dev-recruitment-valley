<?php

namespace Request;

use V\Rules\Validator;
use WP_REST_Request;

class CreateFreeJobRequest implements MiRequest
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
            "description" => ["required", "wywsig"],
            "city" => ["required"],
            "placementAddress" => ["required"],
            // "salaryStart" => ["required", "numeric"],
            // "salaryEnd" => ["required", "numeric"],
            "sector.*" => ["required", "numeric"],
            "role.*" => ["required", "numeric"],
            "workingHours.*" => ["required", "numeric"],
            "location.*" => ["required", "numeric"],
            "education.*" => ["required", "numeric"],
            "employmentType.*" => ["required", "numeric"],
            "externalUrl" => ["url"],
            "experiences.*" => ["numeric"] // Added Line
        ];
    }

    public function sanitizeRules()
    {
        return [
            "name" => "text",
            "description" => "ksespost",
            "city" => "text",
            "placementAddress" => "text",
            // "salaryStart" => ["required", "numeric"],
            // "salaryEnd" => ["required", "numeric"],
            "sector.*" => "text",
            "role.*" => "text",
            "workingHours.*" => "text",
            "location.*" => "text",
            "education.*" => "text",
            "employmentType.*" => "text",
            "externalUrl" => "url",
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
