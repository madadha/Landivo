<?php

namespace App\Contracts;

use App\Enums\FieldType;

interface FormFieldType
{
    public function type(): FieldType;

    /** @return array<int, string> */
    public function rules(array $field): array;
}
