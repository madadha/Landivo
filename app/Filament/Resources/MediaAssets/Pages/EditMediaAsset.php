<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMediaAsset extends EditRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')->label('فتح الملف')->icon('heroicon-o-arrow-top-right-on-square')->url(fn () => $this->record->public_url)->openUrlInNewTab(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->refreshFileMetadata();
    }
}
