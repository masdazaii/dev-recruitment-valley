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
use V\Rules\MaxStoredRule;
use Exception;

class Validator
{
    private $data;
    private $rules;
    private $_sanitizeRules;
    private $errors;

    public function __construct(array $data, array $rules, array $sanitizeRules = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->_sanitizeRules = $sanitizeRules;
        $this->errors = [];
    }

    public function validate()
    {
        /** Old Validate start here */
        // die;

        foreach ($this->rules as $field => $rules) {
            $is_array = str_contains($field, "*");
            if ($is_array) {
                $field = substr($field, 0, -2);
            }

            $value = isset($this->data[$field]) ? $this->data[$field] : null;

            if ($is_array && !is_array($value) && $value) {
                $value = explode(",", $value);
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
            $parameters = explode(',', strtolower($parameterString));
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
            case 'url':
                return new UrlRule();
            case 'exists':
                return new ExistsRule();
            case 'not_exists':
                return new NotExistsRule();
            case 'max_stored':
                return new MaxStoredRule();
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

    public function sanitize($sanitize = false)
    {
        /** Old sanitize start here */
        foreach ($this->data as $key => $value) {
            if (!isset($this->rules[$key])) {
                if (!isset($this->rules[$key . ".*"])) {
                    unset($this->data[$key]);
                    continue;
                }
            }

            if (is_array($value) || is_object($value) || file_exists($value)) {
                // $sanitizeName = $sanitize[$key];
                // switch ($sanitizeName) :
                //     case "array" :

                //     case "arrayofobject" :
                //         default break;
            } else {
                $this->data[$key] = sanitize_text_field($value);
            }
        }
    }

    /** Temporary validation only for setup company */
    public function tempValidate()
    {
        foreach ($this->rules as $field => $rules) {
            /** Set rule real field */
            if (strpos($field, '.*') !== false) {
                $theField = substr($field, 0, -2);
                $value = isset($this->data[$theField]) ? $this->data[$theField] : null;
                if (!is_array($value) && $value !== null) {
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

    /** Temporary sanitize only for setup company */
    public function tempSanitize()
    {
        /** Old Sanitize start here */
        // $sanitizedData = [];

        // if (array_key_exists('user_id', $this->data)) {
        //     $sanitizedData['user_id'] = $this->data['user_id'];
        // }

        // foreach ($this->rules as $field => $rules) {
        //     /** Set rule real field */
        //     if (strpos($field, '.*') !== false) {
        //         $theField = substr($field, 0, -2);
        //     } else {
        //         $theField = $field;
        //     }

        //     /** Check if request key exist */
        //     if (strpos($field, '.*') !== false) {
        //         $theField = substr($field, 0, -2);
        //         if (array_key_exists($theField, $this->data)) {
        //             if (!is_array($this->data[$theField]) && $this->data[$theField] !== null) {
        //                 $arrayValue = explode(',', $this->data[$theField]);
        //             } else {
        //                 $arrayValue = isset($this->data[$theField]) ? $this->data[$theField] : null;
        //             }

        //             foreach ($arrayValue as $value) {
        //                 if ($value) {
        //                     $sanitizedData[$theField][] = sanitize_text_field($value);
        //                 }
        //             }
        //         }
        //     } else {
        //         $sanitizedData[$field] = isset($this->data[$field]) ? sanitize_text_field($this->data[$field]) : null;
        //     }
        // }
        // $this->data = $sanitizedData;
        /** Old Sanitize End here */

        /** Changed Sanitize start here */
        if (!empty($this->_sanitizeRules)) {
            foreach ($this->_sanitizeRules as $field => $rule) {
                if (is_string($rule) && $rule !== null && !empty($rule)) {
                    if (strpos($field, '.*') !== false) {
                        $theField = explode('.*', $field)[0];

                        if (array_key_exists($theField, $this->data)) {
                            $this->data[$theField] = $this->_sanitizeArray($rule, $this->data[$theField]);
                            // $this->data[$theField][] = $this->_doSanitize($rule, $this->data[$theField]);
                        } else {
                            $this->data[$theField] = null;
                        }
                    } else {
                        if (array_key_exists($field, $this->data)) {
                            $this->data[$field] = $this->_doSanitize($rule, $this->data[$field]);
                        } else {
                            $this->data[$field] = null;
                        }
                    }
                }
            }
        }
    }

    private function _sanitizeArray(String $rule, mixed $data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data = $this->_sanitizeArray($rule, $value);
                } else {
                    $data = $this->_doSanitize($rule, $value);
                }
            }
        }
        return $data;
    }

    private function _doSanitize(String $rule, Mixed $data, String $parameter = '')
    {
        switch ($rule):
            case 'email':
                return sanitize_text_field($data);
                break;
            case 'textarea':
                return sanitize_textarea_field($data);
                break;
            case 'text':
                return sanitize_text_field($data);
                break;
            default:
                return $data;
                break;
        endswitch;
    }
}
