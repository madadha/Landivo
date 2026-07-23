<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\ProductStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['translations', 'media']))
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('primary_image_path')
                    ->label(__('landivo.products.image'))
                    ->disk('public')
                    ->getStateUsing(fn ($record): ?string => $record->primary_image_path ?: $record->localizedMedia()?->file_path)
                    ->square()
                    ->size(56),
                TextColumn::make('product_name')
                    ->label('اسم المنتج')
                    ->state(fn (Product $record): string => $record->translations->firstWhere('locale', 'ar')?->name
                        ?? $record->translations->first()?->name
                        ?? 'منتج بدون اسم')
                    ->description(fn (Product $record): string => $record->sku)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where(function (Builder $query) use ($search): void {
                        $query->where('sku', 'like', "%{$search}%")
                            ->orWhereHas('translations', fn (Builder $translation): Builder => $translation->where('name', 'like', "%{$search}%"));
                    })),
                TextColumn::make('sku')->label(__('landivo.products.sku'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_badge')
                    ->label('الشارة')
                    ->state(fn (Product $record): ?string => $record->badge_is_active
                        ? ($record->badge_text_ar ?: $record->badge_text_en)
                        : null)
                    ->placeholder('—')
                    ->badge()
                    ->color(fn (Product $record): string => $record->badge_is_active ? 'warning' : 'gray'),
                TextColumn::make('media_count')->label('الوسائط')->counts('media')->badge()->color('info'),
                TextColumn::make('variants_count')->label('المتغيرات')->counts('variants')->badge()->color('warning'),
                TextColumn::make('price')->label(__('landivo.products.price'))->money(fn (Product $record): string => $record->currency)->sortable(),
                TextColumn::make('quantity')
                    ->label(__('landivo.products.quantity'))
                    ->badge()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success'))
                    ->formatStateUsing(fn (int $state): string => $state <= 0 ? 'نفد المخزون' : number_format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('landivo.products.status'))
                    ->badge()
                    ->formatStateUsing(fn (ProductStatus|string $state): string => $state instanceof ProductStatus ? $state->label() : $state)
                    ->color(fn (ProductStatus|string $state): string => match ($state instanceof ProductStatus ? $state : ProductStatus::tryFrom($state)) {
                        ProductStatus::Active => 'success',
                        ProductStatus::Archived => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')->label('تاريخ الإضافة')->dateTime('Y/m/d H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة المنتج')
                    ->options(collect(ProductStatus::cases())->mapWithKeys(fn (ProductStatus $status): array => [$status->value => $status->label()])->all())
                    ->multiple(),
                SelectFilter::make('stock_level')
                    ->label('حالة المخزون')
                    ->options([
                        'available' => 'متوفر (أكثر من 5)',
                        'low' => 'مخزون منخفض (1 إلى 5)',
                        'out' => 'نفد المخزون',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'available' => $query->where('quantity', '>', 5),
                        'low' => $query->whereBetween('quantity', [1, 5]),
                        'out' => $query->where('quantity', '<=', 0),
                        default => $query,
                    }),
                SelectFilter::make('currency')
                    ->label('العملة')
                    ->options(fn (): array => Product::query()
                        ->where('account_id', auth()->user()?->account_id)
                        ->distinct()
                        ->orderBy('currency')
                        ->pluck('currency', 'currency')
                        ->filter()
                        ->all()),
                TernaryFilter::make('has_media')
                    ->label('صور ووسائط المنتج')
                    ->placeholder('الكل')
                    ->trueLabel('لديه وسائط')
                    ->falseLabel('بدون وسائط')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('media'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('media'),
                    ),
                TernaryFilter::make('has_variants')
                    ->label('متغيرات المنتج')
                    ->placeholder('الكل')
                    ->trueLabel('لديه متغيرات')
                    ->falseLabel('بدون متغيرات')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('variants'),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('variants'),
                    ),
                Filter::make('price_range')
                    ->label('نطاق السعر')
                    ->form([
                        TextInput::make('from')->label('السعر من')->numeric()->minValue(0),
                        TextInput::make('until')->label('السعر إلى')->numeric()->minValue(0),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(filled($data['from'] ?? null), fn (Builder $query): Builder => $query->where('price', '>=', $data['from']))
                        ->when(filled($data['until'] ?? null), fn (Builder $query): Builder => $query->where('price', '<=', $data['until']))),
                Filter::make('created_between')
                    ->label('تاريخ الإضافة')
                    ->form([
                        DatePicker::make('from')->label('من'),
                        DatePicker::make('until')->label('إلى'),
                    ])
                    ->columns(2)
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('products.created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('products.created_at', '<=', $date))),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
