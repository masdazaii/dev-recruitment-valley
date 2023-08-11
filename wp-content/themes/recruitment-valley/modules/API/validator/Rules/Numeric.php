<?php

namespace V\Rules;

use V\Rule;

class NumericRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null) {
            return true;
        } else if (is_array($value)) {
            // Loop through value
            foreach ($value as $val) {
                return is_numeric($val);
            }
        } else {
            return is_numeric($value);
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be a number.";
    }
}
