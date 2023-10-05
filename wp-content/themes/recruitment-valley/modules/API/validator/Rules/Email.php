<?php

namespace V\Rules;

use V\Rule;

class EmailRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getErrorMessage($field, $parameters): string
    {
        // return "The {$field} must be a valid email address.";
        return "Het veld: {$field} moet een geldig e-mailadres zijn.";
    }
}
