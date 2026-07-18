<?php

namespace App;

enum LandingPageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';

    public function label(): string
    {
        return __('landivo.statuses.'.$this->value);
    }
}
