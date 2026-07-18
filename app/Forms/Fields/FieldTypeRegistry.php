<?php

namespace App\Forms\Fields;

use App\Contracts\FormFieldType;
use App\Enums\FieldType;

final class FieldTypeRegistry
{
    public static function make(string|FieldType $type): FormFieldType
    {
        return new StandardFieldType($type instanceof FieldType ? $type : (FieldType::tryFrom($type) ?? FieldType::Text));
    }
}
