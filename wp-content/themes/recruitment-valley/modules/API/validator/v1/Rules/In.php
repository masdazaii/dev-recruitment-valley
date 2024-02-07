<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class InRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value === "" || $value === null) {
            return true;
        }


        if (is_array($value)) {
            if (count($value) >= 1) {
                foreach ($value as $val) {
                    return in_array(strtolower($val), $parameters);
                    break;
                }

                return true;
            }
            return in_array($value, $parameters);
        } else {
            return in_array(strtolower($value), $parameters);
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The value must be a in " . implode(",", $parameters);
    }
}
