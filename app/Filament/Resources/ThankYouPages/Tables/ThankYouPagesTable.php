<?php

namespace App\Filament\Resources\ThankYouPages\Tables;

use App\Models\ThankYouPage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ThankYouPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('internal_name')->label('الاسم الداخلي')->searchable()->sortable()->description(fn (ThankYouPage $record): ?string => $record->title_ar ?: $record->title_en),
                TextColumn::make('slug')->label('المعرّف')->searchable()->copyable()->copyMessage('تم نسخ المعرّف'),
                TextColumn::make('public_url')->label('الرابط المباشر')->state(fn (ThankYouPage $record): string => $record->publicUrl())->copyable()->copyMessage('تم نسخ رابط صفحة الشكر')->limit(38)->tooltip(fn (ThankYouPage $record): string => $record->publicUrl()),
                TextColumn::make('template')->label('القالب')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'celebration' => 'احتفالي', 'minimal' => 'بسيط', default => 'احترافي'
                }),
                TextColumn::make('default_locale')->label('اللغة')->badge()->formatStateUsing(fn (string $state): string => $state === 'en' ? 'English' : 'العربية'),
                IconColumn::make('is_active')->label('مفعّلة')->boolean()->sortable(),
                TextColumn::make('created_at')->label('تاريخ الإنشاء')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_active')->label('الحالة')->options(['1' => 'مفعّلة', '0' => 'موقوفة']),
                SelectFilter::make('template')->label('القالب')->options(['premium' => 'احترافي', 'celebration' => 'احتفالي', 'minimal' => 'بسيط']),
            ])
            ->recordActions([
                Action::make('open')->label('فتح')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (ThankYouPage $record): string => $record->publicUrl())->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
