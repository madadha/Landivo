<?php

namespace App\Filament\Resources\ThankYouPages;

use App\Filament\Resources\ThankYouPages\Pages\CreateThankYouPage;
use App\Filament\Resources\ThankYouPages\Pages\EditThankYouPage;
use App\Filament\Resources\ThankYouPages\Pages\ListThankYouPages;
use App\Filament\Resources\ThankYouPages\Schemas\ThankYouPageForm;
use App\Filament\Resources\ThankYouPages\Tables\ThankYouPagesTable;
use App\Models\ThankYouPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ThankYouPageResource extends Resource
{
    protected static ?string $model = ThankYouPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'صفحات الشكر';
    }

    public static function getModelLabel(): string
    {
        return 'صفحة شكر';
    }

    public static function getPluralModelLabel(): string
    {
        return 'صفحات الشكر';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('account_id', auth()->user()?->account_id);
    }

    public static function form(Schema $schema): Schema
    {
        return ThankYouPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThankYouPagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThankYouPages::route('/'),
            'create' => CreateThankYouPage::route('/create'),
            'edit' => EditThankYouPage::route('/{record}/edit'),
        ];
    }
}
