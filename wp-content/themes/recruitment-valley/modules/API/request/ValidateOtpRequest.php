<?php

namespace Request;
use WP_REST_Request;
use V\Rules\Validator;

class ValidateOtpRequest implements MiRequest
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
            "email" => ['email', 'required'],
            "otp" => ['required', 'numeric'],
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