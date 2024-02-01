<?php

namespace ValidifyMI;

interface Rule
{
    public function validate($field, $value, $parameters): bool;
    public function getErrorMessage($field, $parameters): string;
}

interface RuleWithRequest
{
    public function validate($field, $value, $request, $parameters): bool;
    public function getErrorMessage($field, $parameters): string;
}

interface RuleWithOtherRules
{
    public function validate($field, $value, $request, $parameters): bool;
    public function getErrorMessage($field, $parameters): string;
}
