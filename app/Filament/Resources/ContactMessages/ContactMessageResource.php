<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\EditContactMessage;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'رسائل التواصل';

    protected static ?string $modelLabel = 'رسالة';

    protected static ?string $pluralModelLabel = 'رسائل التواصل';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('account_id', auth()->user()?->account_id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('بيانات الرسالة')->schema([TextInput::make('name')->label('الاسم')->disabled(), TextInput::make('email')->label('البريد')->disabled(), TextInput::make('phone')->label('الهاتف')->disabled(), TextInput::make('subject')->label('الموضوع')->disabled(), Textarea::make('message')->label('الرسالة')->rows(8)->disabled(), Toggle::make('is_read')->label('تمت قراءة الرسالة')])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([IconColumn::make('is_read')->label('مقروءة')->boolean(), TextColumn::make('name')->label('الاسم')->searchable(), TextColumn::make('email')->label('البريد')->searchable(), TextColumn::make('phone')->label('الهاتف')->searchable(), TextColumn::make('subject')->label('الموضوع')->limit(35), TextColumn::make('created_at')->label('التاريخ')->since()])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListContactMessages::route('/'), 'edit' => EditContactMessage::route('/{record}/edit')];
    }
}
