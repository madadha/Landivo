<?php

namespace App\Filament\Resources\ThankYouPages\Pages;

use App\Filament\Resources\ThankYouPages\ThankYouPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateThankYouPage extends CreateRecord
{
    protected static string $resource = ThankYouPageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['account_id'] = auth()->user()?->account_id;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
