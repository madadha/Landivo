<?php

namespace App\Filament\Resources\MarketingPopups\Pages;

use App\Filament\Resources\MarketingPopups\MarketingPopupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketingPopup extends CreateRecord
{
    protected static string $resource = MarketingPopupResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['account_id'] = auth()->user()?->account_id;

        return $data;
    }
}
