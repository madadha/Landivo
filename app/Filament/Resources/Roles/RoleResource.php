<?php
namespace App\Filament\Resources\Roles;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static string|\UnitEnum|null $navigationGroup = 'إدارة النظام';
    protected static ?int $navigationSort = 20;
    public static function getNavigationLabel(): string { return 'الصلاحيات والأدوار'; }
    public static function getModelLabel(): string { return 'دور'; }
    public static function getPluralModelLabel(): string { return 'الصلاحيات والأدوار'; }
    public static function form(Schema $schema): Schema { return $schema->components([TextInput::make('name')->label('اسم الدور')->required(), Select::make('permissions')->label('الصلاحيات')->relationship('permissions', 'name')->multiple()->preload()->searchable()]); }
    public static function table(Table $table): Table { return $table->columns([TextColumn::make('name')->label('الدور')->searchable(), TextColumn::make('permissions.name')->label('الصلاحيات')->badge(), TextColumn::make('users_count')->counts('users')->label('عدد المستخدمين')])->recordActions([\Filament\Actions\EditAction::make()]); }
    public static function getPages(): array { return ['index' => ListRoles::route('/'), 'create' => CreateRole::route('/create'), 'edit' => EditRole::route('/{record}/edit')]; }
}
