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
        // die;

        foreach ($this->rules as $field => $rules) {
            $is_array = str_contains($field, "*");
            if($is_array)
            {
                $field = substr($field, 0, -2);
            }

            $value = isset($this->data[$field]) ? $this->data[$field] : null;

            if($is_array && !is_array($value) && $value)
            {
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
            case 'url':
                return new UrlRule();
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

    public function sanitize( $sanitize = false )
    {
        foreach ($this->data as $key => $value) {
            if(!isset($this->rules[$key]))
            {
                if(!isset($this->rules[$key.".*"]))
                {
                    unset($this->data[$key]);
                    continue;
                }
            }

            if(is_array($value) || is_object($value) ||file_exists($value))
            {
                // $sanitizeName = $sanitize[$key];
                // switch ($sanitizeName) :
                //     case "array" :
                        
                //     case "arrayofobject" :
                //         default break;
            }else{
                $this->data[$key] = sanitize_text_field($value);
            }

        }
    }
}
