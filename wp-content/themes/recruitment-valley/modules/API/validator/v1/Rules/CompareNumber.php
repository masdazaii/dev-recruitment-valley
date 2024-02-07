<?php

namespace ValidifyMI\Rules;

use Exception;
use ValidifyMI\RuleWithRequest;
use WP_REST_Request;

class CompareNumberRule implements RuleWithRequest
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
            if (is_numeric($parameters[1])) {
                switch ($parameters[0]) {
                    case '=':
                        return (int)$value == (int)$parameters[1];
                        break;
                    case '<':
                        return (int)$value < (int)$parameters[1];
                        break;
                    case '>':
                        return (int)$value > (int)$parameters[1];
                        break;
                    case '<=':
                        return (int)$value <= (int)$parameters[1];
                        break;
                    case '>=':
                        return (int)$value >= (int)$parameters[1];
                        break;
                    case '<>':
                        return (int)$value !== (int)$parameters[1];
                        break;
                    default:
                        throw new Exception("Compare rule {$parameters[0]} not allowed!");
                }
            } else {
                if (is_numeric($request[$parameters[1]])) {
                    if (array_key_exists($parameters[1], $request)) {
                        switch ($parameters[0]) {
                            case '=':
                                return (int)$value == (int)$parameters[1];
                                break;
                            case '<':
                                return (int)$value < (int)$parameters[1];
                                break;
                            case '>':
                                return (int)$value > (int)$parameters[1];
                                break;
                            case '<=':
                                return (int)$value <= (int)$parameters[1];
                                break;
                            case '>=':
                                return (int)$value >= (int)$parameters[1];
                                break;
                            case '<>':
                                return (int)$value !== (int)$parameters[1];
                                break;
                            default:
                                throw new Exception("Compare rule {$parameters[0]} not allowed!");
                        }
                    } else {
                        throw new Exception("Field {$parameters[1]} didn't exists!");
                    }
                } else {
                    throw new Exception("Field {$parameters} not comparable!");
                }
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be greater than {$parameters[0]}.";
    }
}
