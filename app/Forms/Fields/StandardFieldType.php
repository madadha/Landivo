<?php

namespace App\Forms\Fields;

use App\Contracts\FormFieldType;
use App\Enums\FieldType;

final class StandardFieldType implements FormFieldType
{
    public function __construct(private readonly FieldType $fieldType) {}

    public function type(): FieldType
    {
        return $this->fieldType;
    }

    public function rules(array $field): array
    {
        $rules = [! empty($field['required']) ? 'required' : 'nullable'];

        return match ($this->fieldType) {
            FieldType::Email => [...$rules, 'email', 'max:255'],
            FieldType::Phone => [...$rules, 'string', 'max:40'],
            FieldType::Number, FieldType::Quantity => [...$rules, 'numeric', 'min:0'],
            FieldType::Date => [...$rules, 'date'], FieldType::Checkbox => [...$rules, 'array'],
            FieldType::File => [...$rules, 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip', 'max:10240'],
            FieldType::Image => [...$rules, 'image', 'max:10240'], FieldType::Hidden => [...$rules, 'string', 'max:255'],
            default => [...$rules, 'string', 'max:'.($this->fieldType === FieldType::Textarea ? '2000' : '255')],
        };
    }
}
