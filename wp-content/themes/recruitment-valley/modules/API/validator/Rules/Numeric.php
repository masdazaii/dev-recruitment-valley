<?php

namespace V\Rules;

use V\Rule;

class NumericRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return is_numeric($value);
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be a number.";
    }
}
