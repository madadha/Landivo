<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\LandingPageStatus;
use App\Models\Account;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use App\Models\LandingPageTranslation;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\ProductStatus;
use Illuminate\Database\Seeder;

final class DemoOliveOilSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::firstOrCreate(
            ['slug' => 'landivo-demo'],
            ['name' => 'Landivo Demo', 'description' => 'الحساب التجريبي لمنصة Landivo'],
        );

        OrderStatus::firstOrCreate(
            ['account_id' => $account->id, 'slug' => 'new'],
            ['name_ar' => 'جديد', 'name_en' => 'New', 'color' => 'blue'],
        );

        $product = Product::updateOrCreate(
            ['account_id' => $account->id, 'sku' => 'MOASIM-EXTRA-VIRGIN-16L'],
            [
                'price' => 650,
                'compare_at_price' => 800,
                'currency' => 'AED',
                'quantity' => 100,
                'status' => ProductStatus::Active,
                'primary_image_path' => 'products/olive-oil-demo.png',
            ],
        );

        ProductTranslation::updateOrCreate(
            ['product_id' => $product->id, 'locale' => 'ar'],
            ['name' => 'زيت زيتون المواسم الفلسطيني البكر', 'description' => 'زيت زيتون فلسطيني بكر ممتاز من جبال فلسطين، عصر حديث وجودة موثوقة.'],
        );
        ProductTranslation::updateOrCreate(
            ['product_id' => $product->id, 'locale' => 'en'],
            ['name' => 'Al Mawasem Palestinian Extra Virgin Olive Oil', 'description' => 'Premium Palestinian extra virgin olive oil from the mountains of Palestine.'],
        );

        $page = LandingPage::updateOrCreate(
            ['account_id' => $account->id, 'slug' => 'extravirgin2'],
            [
                'product_id' => $product->id,
                'template' => 'olive-oil-premium',
                'status' => LandingPageStatus::Published,
                'default_locale' => 'ar',
                'published_at' => now(),
            ],
        );

        LandingPageTranslation::updateOrCreate(
            ['landing_page_id' => $page->id, 'locale' => 'ar'],
            ['title' => 'زيت زيتون فلسطيني من جبال فلسطين', 'description' => 'عرض زيت زيتون المواسم البكر الممتاز بسعر خاص والتوصيل متاح.'],
        );
        LandingPageTranslation::updateOrCreate(
            ['landing_page_id' => $page->id, 'locale' => 'en'],
            ['title' => 'Premium Palestinian Olive Oil', 'description' => 'A special offer for premium extra virgin olive oil with delivery available.'],
        );

        if (false) {
        LandingPageSection::updateOrCreate(
            ['landing_page_id' => $page->id, 'type' => 'features'],
            ['sort_order' => 1, 'is_visible' => true, 'settings' => ['title' => 'مميزات العرض', 'item_1' => 'زيت بكر ممتاز', 'item_2' => 'توصيل متاح', 'item_3' => 'جودة موثوقة']],
        );
        LandingPageSection::updateOrCreate(
            ['landing_page_id' => $page->id, 'type' => 'whatsapp'],
            ['sort_order' => 2, 'is_visible' => true, 'settings' => ['number' => '971501006022', 'label' => 'تواصل معنا عبر واتساب']],
        );
        }
    }
}
