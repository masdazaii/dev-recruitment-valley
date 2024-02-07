<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class RequiredRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (empty($val)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return !empty($value);
            } else {
                return !empty($value);
            }
        } else {
            return !empty($value);
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "Field '{$field}' is required.";
    }
}
