<?php
namespace V\Rules;

use V\Rule;

class FileRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "File type not allowed.";
    }
}