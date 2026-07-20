<?php

namespace App\Filament\Resources\SitePages\Pages;

use App\Filament\Resources\SitePages\SitePageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSitePage extends CreateRecord
{
    protected static string $resource = SitePageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['account_id'] = auth()->user()?->account_id;

        return $data;
    }
}
