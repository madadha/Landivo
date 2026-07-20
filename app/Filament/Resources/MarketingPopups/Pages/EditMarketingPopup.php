<?php

namespace App\Filament\Resources\MarketingPopups\Pages;

use App\Filament\Resources\MarketingPopups\MarketingPopupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingPopup extends EditRecord
{
    protected static string $resource = MarketingPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
