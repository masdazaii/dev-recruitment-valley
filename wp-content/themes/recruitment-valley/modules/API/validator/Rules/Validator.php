<?php

namespace V\Rules;

use V\Rule;
use V\Rules\RequiredRule;
use V\Rules\EmailRule;
use V\Rules\MinRule;
use V\Rules\MaxRule;
use V\Rules\NumericRule;
use V\Rules\ExistsRule;
use V\Rules\NotExistsRule;
use Exception;

class Validator
{
    private $data;
    private $rules;
    private $errors;

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];
    }

    public function validate()
    {
        /** Old Validate start here */
        // foreach ($this->rules as $field => $rules) {
        //     $value = isset($this->data[$field]) ? $this->data[$field] : null;

        //     foreach ($rules as $rule) {
        //         list($ruleName, $parameters) = $this->parseRule($rule);
        //         $ruleInstance = $this->getRuleInstance($ruleName);

        //         if (!$ruleInstance->validate($field, $value, $parameters)) {
        //             $this->addError($field, $ruleName, $parameters);
        //         }
        //     }
        // }
        // return empty($this->errors);

        /** Changes start here */
        foreach ($this->rules as $field => $rules) {
            if (strpos($field, '.*') !== false) {
                $value = isset($this->data[substr($field, 0, -2)]) ? $this->data[substr($field, 0, -2)] : null;
                if ($value && !is_array($value)) {
                    $value = explode(',', $value);
                }
            } else {
                $value = isset($this->data[$field]) ? $this->data[$field] : null;
            }

            foreach ($rules as $rule) {
                list($ruleName, $parameters) = $this->parseRule($rule);
                $ruleInstance = $this->getRuleInstance($ruleName);

                if (!$ruleInstance->validate($field, $value, $parameters)) {
                    $this->addError($field, $ruleName, $parameters);
                }
            }
        }

        return empty($this->errors);
    }

    private function parseRule($rule)
    {
        $parameters = [];
        if (strpos($rule, ':') !== false) {
            list($rule, $parameterString) = explode(':', $rule, 2);
            $parameters = explode(',', $parameterString);
        }

        return [$rule, $parameters];
    }

    private function getRuleInstance($ruleName)
    {
        switch ($ruleName) {
            case 'required':
                return new RequiredRule();
            case 'email':
                return new EmailRule();
            case 'min':
                return new MinRule();
            case 'max':
                return new MaxRule();
            case 'numeric':
                return new NumericRule();
            case 'in':
                return new In();
            case 'exists':
                return new ExistsRule();
            case 'not_exists':
                return new NotExistsRule();
            default:
                throw new Exception("Rule '{$ruleName}' not supported.");
        }
    }



    private function addError($field, $rule, $parameters)
    {
        $message = $this->getErrorMessage($field, $rule, $parameters);
        $this->errors[$field][] = $message;
    }

    private function getErrorMessage($field, $rule, $parameters)
    {
        $ruleInstance = $this->getRuleInstance($rule);
        return $ruleInstance->getErrorMessage($field, $parameters);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getData()
    {
        return $this->data;
    }

    public function sanitize()
    {
        /** Old sanitize start here */
        // foreach ($this->data as $key => $value) {
        //     if (!isset($this->rules[$key])) {
        //         unset($this->data[$key]);
        //         continue;
        //     }

        //     $this->data[$key] = sanitize_text_field($value);
        // }

        /** Changes start here */
        $sanitizedData = [
            "user_id" => $this->data['user_id']
        ];
        foreach ($this->rules as $field => $rules) {
            /** Set rule real field */
            if (strpos($field, '.*') !== false) {
                $theField = substr($field, 0, -2);
            } else {
                $theField = $field;
            }

            /** Check if request key exist */
            if (strpos($field, '.*') !== false) {
                $theField = substr($field, 0, -2);
                if (array_key_exists($theField, $this->data) && !is_array($this->data[$theField])) {
                    $arrayValue = explode(',', $this->data[$theField]);
                    foreach ($arrayValue as $value) {
                        if ($value) {
                            $sanitizedData[$theField][] = sanitize_text_field($value);
                        }
                    }
                }
            } else {
                $sanitizedData[$field] = isset($this->data[$field]) ? sanitize_text_field($this->data[$field]) : null;
            }
        }
        $this->data = $sanitizedData;
    }
}
