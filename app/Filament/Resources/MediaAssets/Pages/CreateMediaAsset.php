<?php

namespace App\Filament\Resources\MediaAssets\Pages;

use App\Filament\Resources\MediaAssets\MediaAssetResource;
use App\Services\MediaLibraryService;
use Filament\Resources\Pages\CreateRecord;

class CreateMediaAsset extends CreateRecord
{
    protected static string $resource = MediaAssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['account_id'] = auth()->user()?->account_id;
        $data['uploaded_by'] = auth()->id();
        $data['disk'] = 'public';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->refreshFileMetadata();
        app(MediaLibraryService::class)->synchronizeAccount((int) $this->record->account_id, true);
    }
}
