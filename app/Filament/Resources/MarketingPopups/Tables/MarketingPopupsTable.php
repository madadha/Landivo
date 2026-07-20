<?php

namespace App\Filament\Resources\MarketingPopups\Tables;

use App\MarketingPopupTemplate;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MarketingPopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('priority', 'desc')
            ->columns([
                ImageColumn::make('desktop_image')->label('الصورة')->disk('public')->square(),
                TextColumn::make('internal_name')->label('الاسم الداخلي')->searchable()->sortable()->description(fn ($record): ?string => $record->title_ar ?: $record->title_en),
                TextColumn::make('template')->label('التصميم')->badge()->formatStateUsing(fn ($state): string => $state instanceof MarketingPopupTemplate ? $state->label() : (MarketingPopupTemplate::tryFrom((string) $state)?->label() ?? (string) $state)),
                TextColumn::make('page_scope')->label('الجمهور')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'homepage' => 'الرئيسية', 'landing_pages' => 'صفحات الهبوط', 'site_pages' => 'صفحات الموقع', 'selected' => 'صفحات محددة', default => 'الجميع',
                }),
                TextColumn::make('priority')->label('الأولوية')->sortable(),
                TextColumn::make('impressions_count')->label('الظهور')->numeric()->sortable(),
                TextColumn::make('clicks_count')->label('النقرات')->numeric()->sortable(),
                TextColumn::make('ctr')->label('CTR')->state(fn ($record): string => $record->impressions_count > 0 ? number_format(($record->clicks_count / $record->impressions_count) * 100, 1).'%' : '0%'),
                IconColumn::make('is_active')->label('مفعّلة')->boolean()->sortable(),
                TextColumn::make('starts_at')->label('البداية')->dateTime('d/m/Y H:i')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ends_at')->label('النهاية')->dateTime('d/m/Y H:i')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('template')->label('التصميم')->options(MarketingPopupTemplate::options()),
                SelectFilter::make('is_active')->label('الحالة')->options(['1' => 'مفعّلة', '0' => 'متوقفة']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
