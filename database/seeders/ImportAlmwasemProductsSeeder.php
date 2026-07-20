<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Product;
use App\ProductStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportAlmwasemProductsSeeder extends Seeder
{
    public function run(): void
    {
        $account = Account::query()->where('slug', 'almwasem')->first() ?? Account::query()->firstOrFail();

        $products = [
            ['sku' => 'ALMWASEM-SPECIAL-OFFER', 'ar' => 'العرض المميز', 'en' => 'Premium Offer', 'ar_description' => 'زيت زيتون فلسطيني العصرة الأولى على البارد مع هدايا العرض الكامل.', 'en_description' => 'Premium Palestinian cold-pressed olive oil offer with the complete gift package.', 'price' => 650, 'compare' => 800, 'image' => '01KTEMQC095PMHT1RD9XGMGGPY.webp'],
            ['sku' => 'ALMWASEM-OLIVE-10L', 'ar' => 'عرض تنكة زيت زيتون 10 لتر', 'en' => '10-Litre Olive Oil Tin Offer', 'ar_description' => 'زيت زيتون فلسطيني بكر ممتاز، عبوة 10 لتر.', 'en_description' => 'Premium Palestinian extra virgin olive oil in a 10-litre tin.', 'price' => 399, 'image' => '01KTEKTCVDJ9X6D3T19A43HQXQ.webp'],
            ['sku' => 'ALMWASEM-OLIVE-5L', 'ar' => 'عرض زيت زيتون 5 لتر', 'en' => '5-Litre Olive Oil Offer', 'ar_description' => 'زيت زيتون فلسطيني بكر ممتاز، عبوة 5 لتر.', 'en_description' => 'Premium Palestinian extra virgin olive oil in a 5-litre tin.', 'price' => 275, 'image' => '01KTEM23J0D7J0904ZB982PSQA.webp'],
            ['sku' => 'ALMWASEM-NABULSI-CHEESE-TIN', 'ar' => 'جبنة نابلسية مع حبة البركة – نصية 4 كيلو', 'en' => 'Nabulsi Cheese with Black Seed – 4 kg Tin', 'ar_description' => 'جبنة نابلسية فلسطينية أصلية مع حبة البركة محفوظة في التنكة التقليدية.', 'en_description' => 'Authentic Palestinian Nabulsi cheese with black seed, traditionally preserved in a tin.', 'price' => 250, 'image' => '01KTHE2VZBYYCK7YYGCA00TFAD.webp'],
            ['sku' => 'ALMWASEM-GREEN-OLIVES', 'ar' => 'زيتون أخضر بلدي فاخر – كيلو', 'en' => 'Premium Local Green Olives – 1 kg', 'ar_description' => 'زيتون أخضر بلدي فاخر بمذاق أصيل وقوام طازج.', 'en_description' => 'Premium local green olives with an authentic taste and fresh texture.', 'price' => 50, 'image' => '01KTETEHXAQNHPG52HZM78S7VC.webp'],
            ['sku' => 'ALMWASEM-GRILLED-GREEN-OLIVES', 'ar' => 'زيتون أخضر مشوي بلدي – كيلو', 'en' => 'Local Grilled Green Olives – 1 kg', 'ar_description' => 'زيتون أخضر مشوي ومخلل بطريقة فاخرة مع توابل طبيعية.', 'en_description' => 'Premium grilled and pickled green olives with natural spices.', 'price' => 55, 'image' => '01KTEVGBT7GZ5KQFNT6QCMQR0W.webp'],
            ['sku' => 'ALMWASEM-BLACK-OLIVES', 'ar' => 'زيتون أسود بلدي فاخر – كيلو', 'en' => 'Premium Local Black Olives – 1 kg', 'ar_description' => 'زيتون أسود بلدي طبيعي مختار بعناية بمذاق أصيل.', 'en_description' => 'Naturally cured local black olives selected for their authentic taste.', 'price' => 50, 'image' => '01KTETSRZ81YHRHKW16VTXHHJZ.webp'],
            ['sku' => 'ALMWASEM-MAKDOUS', 'ar' => 'مكدوس باذنجان بيتوتي فاخر – كيلو', 'en' => 'Premium Homemade Makdous – 1 kg', 'ar_description' => 'مكدوس باذنجان محشو بالجوز والفلفل والثوم ومحفوظ بزيت الزيتون.', 'en_description' => 'Homemade eggplant makdous stuffed with walnuts, pepper and garlic, preserved in olive oil.', 'price' => 60, 'image' => '01KTEVS5N83QTQTM5J32847ZPG.webp'],
            ['sku' => 'ALMWASEM-LABNEH-BALLS', 'ar' => 'لبنة كرات بلدية بزيت الزيتون – كيلو', 'en' => 'Local Labneh Balls in Olive Oil – 1 kg', 'ar_description' => 'كرات لبنة بلدية طازجة محفوظة بزيت الزيتون الطبيعي.', 'en_description' => 'Fresh local labneh balls preserved in natural olive oil.', 'price' => 65, 'image' => '01KTEY49GGTCCKBJ1PQ68KEWWV.webp'],
            ['sku' => 'ALMWASEM-CHILI', 'ar' => 'شطة فلسطينية بلدية – كيلو', 'en' => 'Local Palestinian Chili – 1 kg', 'ar_description' => 'شطة فلسطينية من الفلفل الأحمر الطازج بنكهة أصيلة وحدّة متوازنة.', 'en_description' => 'Local Palestinian chili made from fresh red peppers with balanced heat.', 'price' => 50, 'image' => '01KTEYHBC1EN1DJR083M24HG30.webp'],
            ['sku' => 'ALMWASEM-TAHINI', 'ar' => 'طحينية بلدية فاخرة – كيلو', 'en' => 'Premium Local Tahini – 1 kg', 'ar_description' => 'طحينية بلدية ناعمة من أجود أنواع السمسم المحمص.', 'en_description' => 'Smooth local tahini made from premium roasted sesame seeds.', 'price' => 55, 'image' => '01KTEZ1F4C075GFVMV54QG3C7N.webp'],
            ['sku' => 'ALMWASEM-DRIED-FIGS', 'ar' => 'تين مجفف فاخر بزيت الزيتون – كيلو', 'en' => 'Premium Dried Figs in Olive Oil – 1 kg', 'ar_description' => 'تين مجفف طبيعي محفوظ بزيت الزيتون البكر.', 'en_description' => 'Naturally dried figs preserved in extra virgin olive oil.', 'price' => 65, 'image' => '01KTEZ9BMDZEW4ZY53H1PP337V.webp'],
            ['sku' => 'ALMWASEM-PEPPER-PASTE', 'ar' => 'دبس فليفلة بلدي – كيلو', 'en' => 'Local Red Pepper Paste – 1 kg', 'ar_description' => 'دبس فليفلة فلسطيني من الفليفلة الحمراء الطازجة بقوام كثيف.', 'en_description' => 'Palestinian red pepper paste made from fresh peppers with a rich texture.', 'price' => 55, 'image' => '01KTF09DBPPWJQFR62SS2YQ21Y.webp'],
            ['sku' => 'ALMWASEM-NABULSI-CHEESE', 'ar' => 'جبنة نابلسية مع حبة البركة', 'en' => 'Nabulsi Cheese with Black Seed', 'ar_description' => 'جبنة نابلسية فلسطينية أصيلة ممزوجة بحبة البركة.', 'en_description' => 'Authentic Palestinian Nabulsi cheese blended with black seed.', 'price' => 55, 'image' => '01KTF0HME5H9Y0XNYRDTVF1VEG.webp'],
            ['sku' => 'ALMWASEM-PICKLED-CUCUMBER', 'ar' => 'مخلل خيار بلدي', 'en' => 'Local Pickled Cucumber', 'ar_description' => 'مخلل خيار بلدي مقرمش بنكهة متوازنة وطعم أصيل.', 'en_description' => 'Crisp local pickled cucumber with a balanced authentic taste.', 'price' => 45, 'image' => '01KTF49N827QC6NB4H13WD13FX.webp'],
            ['sku' => 'ALMWASEM-ZAATAR', 'ar' => 'زعتر فلسطيني أخضر بلدي', 'en' => 'Local Palestinian Green Za’atar', 'ar_description' => 'خلطة زعتر بلدي مع السمسم والتوابل المختارة بعناية.', 'en_description' => 'Traditional green za’atar blend with sesame and selected spices.', 'price' => 50, 'image' => '01KTF4RJQPGM1BAJ1VX8M3H7HZ.webp'],
            ['sku' => 'ALMWASEM-SUMAC', 'ar' => 'سماق بلدي فلسطيني', 'en' => 'Local Palestinian Sumac', 'ar_description' => 'سماق بلدي فلسطيني فاخر بنكهة حامضة طبيعية.', 'en_description' => 'Premium local Palestinian sumac with a naturally tangy flavour.', 'price' => 70, 'image' => '01KTF569FY5JFV39WK5FF7PXF0.webp'],
        ];

        foreach ($products as $data) {
            $path = 'products/catalog/'.$data['sku'].'.webp';
            $imageUrl = 'https://scancatalog.com/storage/items/'.$data['image'];

            if (! Storage::disk('public')->exists($path)) {
                $response = Http::timeout(30)->get($imageUrl);
                if ($response->successful()) {
                    Storage::disk('public')->put($path, $response->body());
                }
            }

            $product = Product::query()->updateOrCreate(
                ['account_id' => $account->id, 'sku' => $data['sku']],
                [
                    'price' => $data['price'],
                    'compare_at_price' => $data['compare'] ?? null,
                    'currency' => 'AED',
                    'quantity' => 0,
                    'status' => ProductStatus::Active,
                    'primary_image_path' => $path,
                    'metadata' => ['image_ar' => $path, 'image_en' => $path, 'source' => 'almwasem-catalog'],
                ],
            );

            $product->translations()->updateOrCreate(['locale' => 'ar'], ['name' => $data['ar'], 'description' => $data['ar_description']]);
            $product->translations()->updateOrCreate(['locale' => 'en'], ['name' => $data['en'], 'description' => $data['en_description']]);
        }
    }
}
