<?php

namespace App\Filament\Resources\MediaAssets\Tables;

use App\Models\MediaAsset;
use App\Services\MediaLibraryService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MediaAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->contentGrid(['md' => 2, 'xl' => 3, '2xl' => 4])
            ->columns([
                Stack::make([
                    ViewColumn::make('preview')->view('filament.tables.columns.media-preview'),
                    TextColumn::make('original_name')->label('الاسم')->weight('bold')->searchable()->wrap()->icon(fn (MediaAsset $record): string => $record->is_image ? 'heroicon-o-photo' : 'heroicon-o-document'),
                    TextColumn::make('title')->label('العنوان')->placeholder('بدون عنوان')->color('gray')->searchable()->wrap(),
                    TextColumn::make('category')->label('النوع')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                        'image' => 'صورة', 'video' => 'فيديو', 'audio' => 'صوت', 'document' => 'مستند', 'archive' => 'ملف مضغوط', default => 'ملف',
                    })->color(fn (string $state): string => match ($state) {
                        'image' => 'success', 'video' => 'danger', 'audio' => 'warning', 'document' => 'info', 'archive' => 'gray', default => 'gray',
                    }),
                    TextColumn::make('human_size')->label('الحجم')->icon('heroicon-o-circle-stack')->color('gray'),
                    TextColumn::make('usage_count')->label('الاستخدام')->badge()->formatStateUsing(fn (int $state): string => $state > 0 ? "مستخدم {$state} مرة" : 'غير مستخدم')->color(fn (int $state): string => $state > 0 ? 'success' : 'warning')->icon(fn (int $state): string => $state > 0 ? 'heroicon-o-link' : 'heroicon-o-link-slash'),
                    TextColumn::make('path')->label('المسار')->copyable()->copyMessage('تم نسخ المسار')->limit(42)->tooltip(fn (MediaAsset $record): string => $record->path)->icon('heroicon-o-code-bracket')->color('gray'),
                    TextColumn::make('public_url')->label('الرابط العام')->copyable()->copyMessage('تم نسخ الرابط')->limit(42)->tooltip(fn (MediaAsset $record): string => $record->public_url)->icon('heroicon-o-clipboard-document')->color('primary'),
                ])->space(2),
            ])
            ->filters([
                SelectFilter::make('category')->label('نوع الملف')->options(['image' => 'صور', 'video' => 'فيديو', 'audio' => 'صوت', 'document' => 'مستندات', 'archive' => 'ملفات مضغوطة', 'other' => 'أخرى']),
                TernaryFilter::make('used')->label('حالة الاستخدام')->placeholder('الكل')->trueLabel('مستخدم')->falseLabel('غير مستخدم')->queries(
                    true: fn (Builder $query) => $query->where('usage_count', '>', 0),
                    false: fn (Builder $query) => $query->where('usage_count', 0),
                ),
                TernaryFilter::make('file_exists')->label('وجود الملف')->placeholder('الكل')->trueLabel('موجود')->falseLabel('مفقود'),
                SelectFilter::make('extension')->label('الامتداد')->options(fn (): array => MediaAsset::query()->where('account_id', auth()->user()?->account_id)->whereNotNull('extension')->distinct()->orderBy('extension')->pluck('extension', 'extension')->all()),
            ])
            ->recordActions([
                Action::make('open')->label('فتح')->icon('heroicon-o-arrow-top-right-on-square')->url(fn (MediaAsset $record): string => $record->public_url)->openUrlInNewTab()->visible(fn (MediaAsset $record): bool => $record->file_exists),
                Action::make('usage')->label('أماكن الاستخدام')->icon('heroicon-o-magnifying-glass')->modalHeading('أماكن استخدام الملف')->modalContent(fn (MediaAsset $record) => view('filament.media.usage', ['record' => $record]))->modalSubmitAction(false)->modalCancelActionLabel('إغلاق'),
                EditAction::make()->label('بيانات الملف'),
                Action::make('delete_unused')->label('حذف آمن')->icon('heroicon-o-trash')->color('danger')->requiresConfirmation()->modalDescription('سيُحذف الملف من التخزين نهائيًا. هذا الإجراء متاح فقط عندما لا يكون الملف مستخدمًا.')->visible(fn (MediaAsset $record): bool => $record->usage_count === 0)->action(function (MediaAsset $record): void {
                    app(MediaLibraryService::class)->deleteUnused($record);
                    Notification::make()->title('تم حذف الملف غير المستخدم')->success()->send();
                }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete_unused')->label('حذف الملفات غير المستخدمة المحددة')->icon('heroicon-o-trash')->color('danger')->requiresConfirmation()->deselectRecordsAfterCompletion()->action(function (Collection $records): void {
                        $deleted = 0;
                        foreach ($records as $record) {
                            $deleted += app(MediaLibraryService::class)->deleteUnused($record) ? 1 : 0;
                        }
                        Notification::make()->title("تم حذف {$deleted} ملف غير مستخدم")->success()->send();
                    }),
                ]),
            ])
            ->emptyStateHeading('مكتبة الوسائط فارغة')
            ->emptyStateDescription('ارفع أول صورة أو ملف، أو اضغط فحص التخزين لاستيراد الملفات الموجودة.')
            ->emptyStateIcon('heroicon-o-photo');
    }
}
