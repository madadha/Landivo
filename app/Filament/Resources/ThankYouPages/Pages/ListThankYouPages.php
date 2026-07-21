<?php

namespace App\Filament\Resources\ThankYouPages\Pages;

use App\Filament\Resources\ThankYouPages\ThankYouPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListThankYouPages extends ListRecords
{
    protected static string $resource = ThankYouPageResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إنشاء صفحة شكر')];
    }
}
