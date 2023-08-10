<?php

use V\Rules\Validator;

class RegisterRequest
{
    private $_request;
    private $_validator;

    public function __construct(WP_REST_Request $request)
    {
        $this->_request = $request;
        $this->_validator = new Validator($this->_request->get_params(), $this->rules());
    }

    public function rules()
    {
        return [
            "email" => ["required", "email"],
            "password" => ["required"],
            "accountType" => ["required", "in:candidate,company"]
        ];
    }

    public function validate()
    {
        return $this->_validator->validate();
    }

    public function sanitize()
    {
        return $this->_validator->sanitize();
    }

    public function getData()
    {
        return $this->_validator->getData();
    }

    public function getErrors()
    {
        return [
            "status" => 400,
            "message" => $this->_validator->getErrors(), 
        ];
    }
}