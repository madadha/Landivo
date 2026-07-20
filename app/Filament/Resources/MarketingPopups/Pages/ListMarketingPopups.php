<?php

namespace App\Filament\Resources\MarketingPopups\Pages;

use App\Filament\Resources\MarketingPopups\MarketingPopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketingPopups extends ListRecords
{
    protected static string $resource = MarketingPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة نافذة تسويقية')];
    }
}
