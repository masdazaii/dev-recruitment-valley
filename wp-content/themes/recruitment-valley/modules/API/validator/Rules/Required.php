<?php

namespace V\Rules;

use V\Rule;

class RequiredRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return !empty($value);
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "Het veld: '{$field}' is verplicht.";
    }
}
