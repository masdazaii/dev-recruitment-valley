<?php

namespace V\Rules;

use V\Rule;

class NumericRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if($value == "" || $value == null || $value == [""])
        {
            return true;
        }

        if(is_array($value))
        {
            foreach ($value as $val) {
                if(!is_numeric($val))
                {
                    return false;
                    break;
                }
            }

            return true;
        }else{
            return is_numeric($value);
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be a number.";
    }
}
