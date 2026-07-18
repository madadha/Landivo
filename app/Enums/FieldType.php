<?php

namespace App\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Email = 'email';
    case Phone = 'phone';
    case Number = 'number';
    case Textarea = 'textarea';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Hidden = 'hidden';
    case Quantity = 'quantity';
    case ProductVariant = 'product_variant';
    case Address = 'address';
    case City = 'city';
    case Country = 'country';
    case File = 'file';
    case Image = 'image';

    public function label(): string
    {
        return __('landivo.field_types.'.$this->value);
    }

    public function isChoice(): bool
    {
        return in_array($this, [self::Select, self::Radio, self::Checkbox], true);
    }

    public function isUpload(): bool
    {
        return in_array($this, [self::File, self::Image], true);
    }
}
