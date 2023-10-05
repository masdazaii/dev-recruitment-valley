<?php

namespace Request;

interface MiRequest
{
    public function rules(): array;
    public function validate(): bool;
    public function sanitize();
    public function getData(): array;
    public function getErrors(): array;
}