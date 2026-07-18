<?php
namespace App\Filament\Resources\Users;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    public static function getNavigationLabel(): string { return 'المستخدمون'; }
    public static function getModelLabel(): string { return 'مستخدم'; }
    public static function getPluralModelLabel(): string { return 'المستخدمون'; }
    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->where('account_id', auth()->user()?->account_id); }
    public static function form(Schema $schema): Schema { return $schema->components([
        TextInput::make('name')->label('الاسم')->required(), TextInput::make('email')->label('البريد الإلكتروني')->email()->required(),
        TextInput::make('password')->label('كلمة المرور')->password()->revealable()->dehydrated(fn ($state): bool => filled($state))->required(fn (string $operation): bool => $operation === 'create'),
        Select::make('roles')->label('الصلاحيات / الدور')->relationship('roles', 'name')->multiple()->preload(),
    ]); }
    public static function table(Table $table): Table { return $table->columns([
        TextColumn::make('name')->label('الاسم')->searchable(), TextColumn::make('email')->label('البريد الإلكتروني')->searchable(),
        TextColumn::make('roles.name')->label('الأدوار')->badge(), TextColumn::make('created_at')->label('تاريخ الإضافة')->dateTime()->sortable(),
    ])->recordActions([\Filament\Actions\EditAction::make()])->toolbarActions([\Filament\Actions\BulkActionGroup::make([\Filament\Actions\DeleteBulkAction::make()])]); }
    public static function getPages(): array { return ['index' => ListUsers::route('/'), 'create' => CreateUser::route('/create'), 'edit' => EditUser::route('/{record}/edit')]; }
}
