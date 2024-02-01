<?php

namespace Request;

use WP_REST_Request;

defined("ABSPATH") or die("Direct access not allowed!");

use Constant\Message;
use ValidifyMI\Validator;

abstract class BaseRequest
{
    private $data;
    protected $request;
    protected $validator;
    protected $message;
    protected $errors;

    public function __construct(WP_REST_Request $request = null)
    {
        /** Set private prop */
        if (isset($request) && $request instanceof WP_REST_Request) {
            $this->data = $request->get_params();
        }

        $this->message      = new Message();
    }

    abstract public function rules();

    abstract public function messages();

    abstract public function sanitizer();

    public function validate(): bool
    {
        return $this->validator->validate();
    }

    public function sanitize()
    {
        return $this->validator->sanitize();
    }

    public function validated(): array
    {
        return $this->validator->validated();
    }

    public function sanitized(): array
    {
        $this->sanitize();
        $sanitized = $this->validator->sanitized();

        /** Start set middleware extra request */
        if (isset($this->data) && is_array($this->data)) {
            if (array_key_exists('user_id', $this->data) && isset($this->data['user_id'])) {
                $sanitized['user_id'] = $this->data['user_id'];
            }

            if (array_key_exists('user_email', $this->data) && isset($this->data['user_email'])) {
                $sanitized['user_email'] = $this->data['user_email'];
            }

            if (array_key_exists('is_remembered', $this->data) && isset($this->data['is_remembered'])) {
                $sanitized['is_remembered'] = $this->data['is_remembered'];
            }

            if (array_key_exists('requested_email', $this->data) && isset($this->data['requested_email'])) {
                $sanitized['requested_email'] = $this->data['requested_email'];
            }

            if (array_key_exists('token', $this->data) && isset($this->data['token'])) {
                $sanitized['token'] = $this->data['token'];
            }

            if (array_key_exists('is_remember_me', $this->data) && isset($this->data['is_remember_me'])) {
                $sanitized['is_remember_me'] = $this->data['is_remember_me'];
            }
        }
        /** End Set middleware extra request */

        return $sanitized;
    }

    public function errors($return = null): mixed
    {
        if ($return == 'all') {
            return $this->validator->errors('all');
        } else {
            $this->errors = $this->validator->errors('all');
            return $this;
        }
    }

    public function firstOfAll(): mixed
    {
        /** Validator actually already has function for this necessity */
        if ($this->errors && is_array($this->errors) && !empty($this->errors)) {
            $firstKey = array_key_first($this->errors);

            if ($this->errors[$firstKey]) {
                if (is_array($this->errors[$firstKey])) {
                    return $this->errors[$firstKey][0];
                } else {
                    return $this->errors[$firstKey];
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getFirstError(): string
    {
        return $this->validator->errors()->firstOfAll() ?? "";
    }
}
