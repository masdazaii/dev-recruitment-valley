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
        $this->_validator = new Validator($this->_request->get_params(), $this->rules());
    }

    public function rules(): array
    {
        return [
            "name" => [ 'required'],
            "description" => ["required"],
            "city" => ["required"],
            "salaryStart" => ["required", "numeric"],
            "salaryEnd" => ["required", "numeric"],
            "sector.*" => ["required", "numeric"],
            "role.*" => ["required", "numeric"],
            "workingHours.*" => ["required", "numeric"],
            "location.*" => ["required", "numeric"],
            "education.*" => ["required", "numeric"],
            "employmentType.*" => ["required", "numeric"],
            "externalUrl" => ["url"]
        ];
    }

    public function validate() : bool
    {
        return $this->_validator->validate();
    }

    public function sanitize()
    {
        return $this->_validator->sanitize();
    }

    public function getData() : array
    {
        return $this->_validator->getData();
    }

    public function getErrors() : array
    {
        return [
            "status" => 400,
            "message" => $this->_validator->getErrors(), 
        ];
    }
}