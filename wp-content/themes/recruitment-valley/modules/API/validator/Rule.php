<?php

namespace V;

interface Rule
{
    public function validate($field, $value, $parameters): bool;
    public function getErrorMessage($field, $parameters): string;
}
