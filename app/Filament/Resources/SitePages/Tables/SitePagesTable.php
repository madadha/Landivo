<?php

namespace App\Filament\Resources\SitePages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SitePagesTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('sort_order')->columns([TextColumn::make('translations.title')->label('العنوان')->searchable(), TextColumn::make('slug')->label('الرابط')->copyable()->searchable(), TextColumn::make('template')->label('القالب')->badge(), TextColumn::make('status')->label('الحالة')->badge()->color(fn (string $state): string => $state === 'published' ? 'success' : 'gray'), IconColumn::make('show_in_header')->label('القائمة')->boolean(), IconColumn::make('show_in_footer')->label('الفوتر')->boolean(), TextColumn::make('sort_order')->label('الترتيب')->sortable()])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
