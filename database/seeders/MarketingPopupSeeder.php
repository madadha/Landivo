<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\MarketingPopup;
use Illuminate\Database\Seeder;

class MarketingPopupSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::query()->first();

        if (! $account) {
            return;
        }

        MarketingPopup::query()->updateOrCreate(
            [
                'account_id' => $account->id,
                'internal_name' => 'عرض ترحيبي - زيت الزيتون',
            ],
            [
                'template' => 'split_offer',
                'eyebrow_ar' => 'عرض خاص لزوار المواسم',
                'eyebrow_en' => 'A special offer for Almwasem visitors',
                'title_ar' => 'زيت زيتون فلسطيني أصلي يصل إلى بابك',
                'title_en' => 'Authentic Palestinian olive oil delivered to your door',
                'description_ar' => 'استفد من عرض المواسم المختار مع جودة موثوقة وتوصيل داخل الإمارات.',
                'description_en' => 'Enjoy a selected Almwasem offer with trusted quality and delivery across the UAE.',
                'button_text_ar' => 'اكتشف العرض الآن',
                'button_text_en' => 'Discover the offer',
                'button_url' => '/l/extravirgin2',
                'desktop_image' => 'products/catalog/ALMWASEM-SPECIAL-OFFER.webp',
                'mobile_image' => 'products/catalog/ALMWASEM-SPECIAL-OFFER.webp',
                'page_scope' => 'homepage',
                'locale' => 'all',
                'device' => 'all',
                'trigger_type' => 'delay',
                'delay_seconds' => 3,
                'frequency' => 'once_session',
                'priority' => 100,
                'is_active' => true,
                'allow_close' => true,
                'close_on_backdrop' => true,
                'background_color' => '#ffffff',
                'text_color' => '#111827',
                'button_color' => '#8aa50c',
                'button_text_color' => '#ffffff',
                'overlay_color' => '#0b1635',
                'max_width' => 920,
            ],
        );
    }
}
