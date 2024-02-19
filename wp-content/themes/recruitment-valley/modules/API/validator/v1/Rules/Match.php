<?php

namespace ValidifyMI\Rules;

use Exception;
use ValidifyMI\RuleWithRequest;
use WP_REST_Request;

class MatchRule implements RuleWithRequest
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
            if (!empty($parameters)) {
                $parameters = is_array($parameters) ? $parameters[0] : $parameters;

                if (array_key_exists($parameters, $request)) {
                    if (strpos($field, '.*') !== false) {
                        $checkValue = [];

                        foreach ($value as $key => $values) {
                            $checkValue[$key] = (string)$values === (string)$request[$parameters];
                        }

                        return in_array(false, $checkValue) ? false : true;
                    } else {
                        return (string)$value === (string)$request[$parameters];
                    }
                } else {
                    throw new Exception("Field {$parameters} didn't exists!");
                }
            } else {
                throw new Exception("{$parameters} not provided!");
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        $parameters = is_array($parameters) ? $parameters[0] : $parameters;
        return "The {$field} should has same value with field {$parameters}.";
    }
}
