<?php

namespace Request;

use V\Rules\Validator;
use WP_REST_Request;

class CandidateVacanciesRequest implements MiRequest
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
            "search" => [],
            "city" => [],
            "employmentType.*" => ["numeric"],
            "location.*" => ["numeric"],
            "role.*" => ["numeric"],
            "sector.*" => ["numeric"],
            "hoursPerWeek.*" => ["numeric"],
            "salaryEnd" => ["numeric"],
            "salaryStart" => ["numeric"],
            "perPage" => ["numeric"],
            "page" => ["numeric"]
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