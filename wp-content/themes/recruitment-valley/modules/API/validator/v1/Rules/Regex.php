<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class RegexRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == '' || $value == null || empty($value)) {
            return true;
        } else {
            $parameters = implode('', $parameters);
            $parameters = preg_replace('/\s+/', ' ', $parameters); // Trim multiple whitespace
            $parameters = '/' . $parameters . '/';

            if (strpos($field, '.*') !== false) {
                $checkValue = [];

                foreach ($value as $key => $values) {
                    $checkValue[$key] = preg_match($parameters, $values);
                }

                return in_array(false, $checkValue) ? false : true;
            } else {
                return preg_match($parameters, $value);
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value didn't match the pattern.";
        } else {
            return "The {$field} didn't match the pattern.";
        }
    }
}
