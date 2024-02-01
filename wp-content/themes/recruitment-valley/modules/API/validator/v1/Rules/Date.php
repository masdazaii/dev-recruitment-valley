<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;
use ValidifyMI\RuleWithRequest;
use DateTimeImmutable;

class DateRule implements RuleWithRequest
{
    public function validate($field, $value, $request, $parameters): bool
    {
        return self::validateDate($value, $parameters[0]);
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} must be a valid date wwith format {$parameters[0]}.";
    }

    protected function validateDate(String $date, String $format = 'Y-m-d H:i:s'): bool
    {
        $newDate = DateTimeImmutable::createFromFormat($format, $date);
        return $newDate && $newDate->format($format) === $date;
    }
}
