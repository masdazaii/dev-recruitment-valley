<?php

namespace Helper;

use Exception;
use Constant\RequestRules;
use V\Rules\Validator;

class ValidationHelper
{
    /** Old one */
    public static function doValidate($request, $rules)
    {
        $response = [
            'is_valid'  => true,
            'errors'    => []
        ];

        if (gettype($rules) === 'string') {
            $rules = array_unique(explode('|', $rules));
        }

        foreach ($rules as $key => $value) {
            switch ($value) {
                case 'required':
                    $check = self::_require($request[$key], $key);
                    if (!$check['is_valid']) {
                        $response['is_valid'] = false;
                        $response['fields'][$key][] = $check['message'];
                    }
                    break;
            }
        }

        return $response;
    }

    private static function _require($data, $key = null)
    {
        if (!isset($data) || $data == '') {
            $key_message = $key ?? '';
            return [
                'is_valid'  => false,
                'message'   => 'Field ' . $key_message . ' is required.'
            ];
        }

        return [
            'is_valid'  => true,
        ];
    }

    /** New validation props (using new validator thingy) continued here */
    protected $_rules;
    protected $_validator;

    public function __construct(String $rule, array $data, array $extraRules = [])
    {
        if (empty($rule)) {
            throw new Exception('please spesify rule');
        }

        $this->_rules = new RequestRules;
        $rules = $this->_rules->get($rule);
        if (!empty($extraRules)) {
            foreach ($extraRules as $rule => $value) {
                $rules[$rule] = array_merge($rules[$rule], $value);
            }
        }

        $this->_validator = new Validator($data, $rules);
    }
    public function validate()
    {
        return $this->_validator->validate();
    }

    public function getErrors()
    {
        return $this->_validator->getErrors();
    }

    public function sanitize()
    {
        return $this->_validator->sanitize();
    }

    public function getData()
    {
        return $this->_validator->getData();
    }
}
