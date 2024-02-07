<?php

namespace ValidifyMI\Rules;

use DateTime;
use DateTimeImmutable;
use Exception;
use ValidifyMI\RuleWithRequest;
use WP_REST_Request;

class DateMoreThanRule extends DateRule implements RuleWithRequest
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
            /** Check if parameter is field or date */
            $isParameterDate = self::validateDate($parameters[0], $parameters[1] ?? 'Y-m-d H:i:s');

            if ($isParameterDate) {
                $valueDate  = DateTimeImmutable::createFromFormat($parameters[2], $value);
                $parameters = new DateTimeImmutable($parameters[0]);

                return $valueDate->getTimestamp() > $parameters->getTimestamp();
            } else {
                if (in_array($parameters, $request)) {
                    /** check if field contain date or not */
                    if (self::validateDate($request['parameters'])) {
                        // $valueDate  = new DateTimeImmutable($value);
                        $valueDate  = DateTimeImmutable::createFromFormat($parameters[2], $value);
                        $parameters = new DateTimeImmutable($request['parameters']);

                        return $valueDate->getTimestamp() > $parameters->getTimestamp();
                    } else {
                        throw new Exception("Field {$parameters} not comparable!");
                    }
                } else {
                    throw new Exception("Field {$parameters} didn't exists!");
                }
            }

            // if (is_numeric($parameters)) {
            //     return (int)$value > (int)$parameters;
            // } else {
            //     if (in_array($parameters, $request)) {
            //         if (is_numeric($request[$parameters])) {
            //             return (int)$value > (int)$request[$parameters];
            //         } else {
            //             throw new Exception("Field {$parameters} not comparable!");
            //         }
            //     } else {
            //         throw new Exception("Field {$parameters} didn't exists!");
            //     }
            // }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be greater than {$parameters[0]}.";
    }
}
