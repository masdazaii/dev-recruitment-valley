<?php

namespace V\Rules;

use Exception;
use V\RuleWithRequest;
use WP_REST_Request;

class GreaterThanRule implements RuleWithRequest
{
    /**
     * Validatin Function
     *
     * @param [string] $field
     * @param [mixed] $value
     * @param [array] $request -> all request payload that came
     * @param [mixed] $parameters
     * @return boolean
     */
    public function validate($field, $value, $request, $parameters): bool
    {
        if ($value == "" || $value == null) {
            return true;
        } else {
            if (is_numeric($parameters)) {
                return (int)$value > (int)$parameters;
            } else {
                if (in_array($parameters, $request)) {
                    if (is_numeric($request[$parameters])) {
                        return (int)$value > (int)$request[$parameters];
                    } else {
                        throw new Exception("Field {$parameters} not comparable!");
                    }
                } else {
                    throw new Exception("Field {$parameters} didn't exists!");
                }
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be greater than {$parameters[0]}.";
    }
}
