<?php

namespace App\Filament\Resources\MediaAssets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaAssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('رفع وإدارة الملف')
                ->description('ارفع الصور والمستندات والفيديو والصوت والملفات المضغوطة ضمن تخزين منظم وآمن.')
                ->schema([
                    FileUpload::make('path')
                        ->label('الملف / File')
                        ->disk('public')
                        ->directory(fn (): string => 'media-library/account-'.auth()->user()?->account_id.'/'.now()->format('Y/m'))
                        ->storeFileNamesIn('original_name')
                        ->acceptedFileTypes([
                            'image/*', 'application/pdf', 'text/plain', 'text/csv',
                            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/zip', 'application/x-7z-compressed', 'video/*', 'audio/*',
                        ])
                        ->maxSize(51200)
                        ->openable()->downloadable()->previewable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->helperText('الحد الأقصى 50MB. بعد الرفع يمكنك نسخ الرابط أو المسار من مكتبة الوسائط.')
                        ->columnSpanFull(),
                    TextInput::make('title')->label('عنوان داخلي / Internal title')->maxLength(255),
                    TextInput::make('folder')->label('تصنيف أو مجلد / Collection')->placeholder('مثال: منتجات، بانرات، فواتير')->maxLength(255),
                    Textarea::make('alt_text')->label('النص البديل للصورة / Alt text')->rows(3)->helperText('مفيد لمحركات البحث وإمكانية الوصول.')->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
