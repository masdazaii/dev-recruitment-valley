<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class MaxRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return strlen($value) <= (int)$parameters[0];
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} may not be greater than {$parameters[0]} characters.";
    }
}
