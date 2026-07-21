<?php

namespace App\Filament\Resources\ThankYouPages\Pages;

use App\Filament\Resources\ThankYouPages\ThankYouPageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditThankYouPage extends EditRecord
{
    protected static string $resource = ThankYouPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')->label('فتح صفحة الشكر')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (): string => $this->record->publicUrl())->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
