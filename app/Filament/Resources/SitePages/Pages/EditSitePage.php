<?php

namespace App\Filament\Resources\SitePages\Pages;

use App\Filament\Resources\SitePages\SitePageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSitePage extends EditRecord
{
    protected static string $resource = SitePageResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('open')->label('فتح الصفحة')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (): string => url('/'.$this->record->slug))->openUrlInNewTab(), DeleteAction::make()];
    }
}
