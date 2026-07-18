<?php

namespace App;

enum ProductStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return __('landivo.statuses.'.$this->value);
    }
}
