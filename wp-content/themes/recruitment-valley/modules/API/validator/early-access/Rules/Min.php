<?php

namespace V\Rules;

use V\Rule;

class MinRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return strlen($value) >= (int)$parameters[0];
    }

    public function getErrorMessage($field, $parameters): string
    {
        // return "The {$field} must be at least {$parameters[0]} characters.";
        return "Het veld: {$field} moet minimaal {$parameters[0]} tekens bevatten.";
    }
}
