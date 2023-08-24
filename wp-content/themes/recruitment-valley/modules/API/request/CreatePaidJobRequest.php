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
        $this->_validator = new Validator($this->_request->get_params(), $this->rules());
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
            "applicationProcedureTitle" => ["required"],
            "applicationProcedureText" => ["required"],
            "applicationProcedureSteps.*" => ["required"],
            "video" => ["url"],
            "facebook" => ["url"],
            "linkedin" => ["url"],
            "instagram" => ["url"],
            "twitter" => ["url"],
            "review" => [],
            "experience.*" => ["numeric"], // Added Line
            "galleryJob.*" => [], // Added Line
            "galleryCompany.*" => [] // Added Line
        ];
    }

    public function sanitizes()
    {
        return [
            "sector" => ["array:1"],
            "role" => ["array:1"],
            "workingHours" => ["array:1"],
            "location" => ["array:1"],
            "education" => ["array:1"],
            "applicationProcedureSteps" => ["array:1"],
            "review" => ["arrayofobject:name,role,text"],
        ];
    }

    public function validate(): bool
    {
        return $this->_validator->validate();
    }

    public function sanitize()
    {
        return $this->_validator->sanitize($this->sanitizes());
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
