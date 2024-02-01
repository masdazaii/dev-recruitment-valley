<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class MinRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return strlen($value) >= (int)$parameters[0];
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be at least {$parameters[0]} characters.";
    }
}
