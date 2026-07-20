<?php

namespace App\Filament\Widgets;

use App\Models\MediaAsset;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MediaLibraryStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $query = MediaAsset::query()->where('account_id', auth()->user()?->account_id);
        $total = (clone $query)->count();
        $used = (clone $query)->where('usage_count', '>', 0)->count();
        $unused = (clone $query)->where('usage_count', 0)->count();
        $bytes = (int) (clone $query)->sum('size');

        return [
            Stat::make('إجمالي الملفات', number_format($total))->description('الملفات المفهرسة في المكتبة')->icon('heroicon-o-photo')->color('primary'),
            Stat::make('ملفات مستخدمة', number_format($used))->description('مرتبطة بالمنتجات أو الصفحات')->icon('heroicon-o-link')->color('success'),
            Stat::make('غير مستخدمة', number_format($unused))->description('يمكن مراجعتها أو تنظيفها')->icon('heroicon-o-trash')->color($unused ? 'warning' : 'success'),
            Stat::make('حجم التخزين', $this->formatBytes($bytes))->description('إجمالي مساحة الوسائط')->icon('heroicon-o-circle-stack')->color('info'),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }
        if ($bytes < 1073741824) {
            return number_format($bytes / 1048576, 1).' MB';
        }

        return number_format($bytes / 1073741824, 2).' GB';
    }
}
