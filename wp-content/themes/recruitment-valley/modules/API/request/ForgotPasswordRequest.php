<?php

namespace Request;

use V\Rules\Validator;
use WP_REST_Request;
use Constant\Message;

class ForgotPasswordRequest implements MiRequest
{
    private $_request;
    private $_validator;
    private $_message;

    public function __construct(WP_REST_Request $request)
    {
        $this->_request = $request;
        $this->_validator = new Validator($this->_request->get_params(), $this->rules());
        $this->_message = new Message();
    }

    public function rules(): array
    {
        return [
            "email" => ['email', 'required'],
        ];
    }

    public function validate(): bool
    {
        return $this->_validator->validate();
    }

    public function sanitize()
    {
        return $this->_validator->sanitize();
    }

    public function getData(): array
    {
        return $this->_validator->getData();
    }

    public function getErrors(): array
    {
        $errors = $this->_validator->getErrors();
        $keys = array_keys($errors);
        $message = $errors[$keys[0]][0] ?? $this->_message->get('auth.forgot_password.failed');
        return [
            "status" => 400,
            // "message" => $this->_validator->getErrors(),
            "message" => $message
        ];
    }
}
