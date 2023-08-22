<?php

namespace V\Rules;

use V\Rule;

class MimeRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        $theField = strpos($field, '.*') !== false ? explode('.*.', $field)[0] : $field;

        if (!array_key_exists($theField, $_FILES) || count($_FILES[$theField]['name']) <= 0) {
            return true;
        } else {
            // $check = $this->_check(count($_FILES[$theField]['name']), $table, $type, $column, $selector, $limit);
            // return $check;

            return false;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "{$field} mime-type is not allowed. Allowed mime : " . implode(',', $parameters) . ".";
    }
}
