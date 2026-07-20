<?php

namespace App;

enum MarketingPopupTemplate: string
{
    case SplitOffer = 'split_offer';
    case ImageFocus = 'image_focus';
    case Coupon = 'coupon';
    case Minimal = 'minimal';
    case Announcement = 'announcement';

    public function label(): string
    {
        return match ($this) {
            self::SplitOffer => 'عرض مقسوم: صورة ومحتوى',
            self::ImageFocus => 'صورة كبيرة مع دعوة للإجراء',
            self::Coupon => 'قسيمة خصم بارزة',
            self::Minimal => 'نافذة نصية أنيقة',
            self::Announcement => 'إعلان سريع ومدمج',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $template): array => [
            $template->value => $template->label(),
        ])->all();
    }
}
