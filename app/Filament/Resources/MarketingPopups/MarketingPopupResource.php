<?php

namespace App\Filament\Resources\MarketingPopups;

use App\Filament\Resources\MarketingPopups\Pages\CreateMarketingPopup;
use App\Filament\Resources\MarketingPopups\Pages\EditMarketingPopup;
use App\Filament\Resources\MarketingPopups\Pages\ListMarketingPopups;
use App\Filament\Resources\MarketingPopups\Schemas\MarketingPopupForm;
use App\Filament\Resources\MarketingPopups\Tables\MarketingPopupsTable;
use App\Models\MarketingPopup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MarketingPopupResource extends Resource
{
    protected static ?string $model = MarketingPopup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'النوافذ التسويقية';

    protected static ?string $modelLabel = 'نافذة تسويقية';

    protected static ?string $pluralModelLabel = 'النوافذ التسويقية';

    protected static string|UnitEnum|null $navigationGroup = 'التسويق';

    protected static ?int $navigationSort = 12;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('account_id', auth()->user()?->account_id);
    }

    public static function form(Schema $schema): Schema
    {
        return MarketingPopupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketingPopupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketingPopups::route('/'),
            'create' => CreateMarketingPopup::route('/create'),
            'edit' => EditMarketingPopup::route('/{record}/edit'),
        ];
    }
}
