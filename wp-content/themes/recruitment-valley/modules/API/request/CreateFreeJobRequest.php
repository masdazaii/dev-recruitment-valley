<?php

namespace Request;

use V\Rules\Validator;
use WP_REST_Request;
use Constant\Message;

class CreateFreeJobRequest implements MiRequest
{
    private $_request;
    private $_validator;
    private $_message;

    public function __construct(WP_REST_Request $request)
    {
        $this->_request = $request;
        $this->_validator = new Validator($this->_request->get_params(), $this->rules(), $this->sanitizeRules());
        $this->_message = new Message();
    }

    public function rules(): array
    {
        return [
            "name" => ['required'],
            "description" => ["required", "wywsig"],
            "country" => ["required"], // Added Line
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
            "experiences.*" => ["numeric"], // Added Line,
            "countryCode" => [], // Added Line
            "language"  => ["in:nl,en,de,fr,es"] // Added feedback 01 Nov 2023
        ];
    }

    public function sanitizeRules()
    {
        return [
            "name" => "text",
            "description" => "ksespost",
            "country" => "text", // Added Line
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
            "experiences.*" => "text",
            "countryCode" => "text",
            "language"  => "text" // Added feedback 01 Nov 2023
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
        // return [
        //     "status" => 400,
        //     "message" => $this->_validator->getErrors(),
        // ];

        $errors = $this->_validator->getErrors();
        $keys = array_keys($errors);
        $message = $errors[$keys[0]][0] ?? $this->_message->get('vacancy.create.free.free');
        return [
            "status" => 400,
            "message" => $message
        ];
    }
}
