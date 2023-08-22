<?php

namespace V\Rules;

use V\Rule;

class GreaterThanRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null) {
            return true;
        } else {
            return false;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be greater than {$parameters[0]}.";
    }
}
