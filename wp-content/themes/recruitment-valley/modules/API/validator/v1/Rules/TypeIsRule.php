<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;
use Exception;

class TypeIsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value === "" || $value === null) {
            return true;
        } else {
            if (!empty($parameters)) {
                $parameters = is_array($parameters) ? $parameters[0] : $parameters;

                if (strpos($field, '.') !== false) {
                    foreach ($value as $key => $values) {
                        if (!(gettype($values) === $parameters)) {
                            return false;
                        }

                        $checkValue[$key] = (gettype($values) === $parameters);
                    }
                } else {
                    return gettype($value) === $parameters;
                }
            } else {
                throw new Exception("{$parameters} not provided!");
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.') !== false) {
            $fields = explode('.', $field);
            return "One of the '{$fields[0]}' value type must be : {$parameters}.";
        } else {
            return "The {$field} type must be : {$parameters}.";
        }
    }
}
