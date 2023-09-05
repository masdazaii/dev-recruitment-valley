<?php

namespace V\Rules;

use V\Rule;

class In implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return in_array($value, $parameters);
    }

    public function getErrorMessage($field, $parameters): string
    {
        // return "The value must be a in " . implode(",",$parameters);
        return "De veld: {$field} waarde moet een van de volgende zijn: " . implode(",", $parameters);
    }
}
