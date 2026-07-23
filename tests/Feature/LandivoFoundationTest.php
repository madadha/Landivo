<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\LandingPageStatus;
use App\Models\Account;
use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use App\Models\LandingPageTranslation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Notifications\VerifyEmailAuthentication;
use App\Support\OrderMessageTemplate;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SitePagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class LandivoFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_are_scoped_to_an_account_and_can_have_roles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $account = Account::create([
            'name' => 'Acme',
            'slug' => 'acme',
        ]);

        $user = User::factory()->create(['account_id' => $account->id]);
        $user->assignRole('Account Owner');

        self::assertSame($account->id, $user->account->id);
        self::assertTrue($user->hasRole('Account Owner'));
        self::assertDatabaseHas('accounts', ['slug' => 'acme']);
    }

    public function test_order_statuses_are_customizable_per_account(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-statuses']);

        $status = OrderStatus::create([
            'account_id' => $account->id,
            'name_ar' => 'بانتظار الدفع',
            'name_en' => 'Awaiting Payment',
            'slug' => 'awaiting-payment',
            'color' => 'orange',
        ]);

        self::assertSame('بانتظار الدفع', $status->label());
        self::assertTrue($account->orderStatuses->contains($status));
    }

    public function test_published_landing_page_captures_an_order(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-public']);
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'new']);
        $product = Product::create(['account_id' => $account->id, 'sku' => 'SKU-1', 'price' => 25, 'currency' => 'USD', 'status' => 'active']);
        ProductTranslation::create(['product_id' => $product->id, 'locale' => 'ar', 'name' => 'منتج تجريبي']);
        $page = LandingPage::create(['account_id' => $account->id, 'product_id' => $product->id, 'slug' => 'demo-offer', 'status' => LandingPageStatus::Published, 'default_locale' => 'ar']);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'عرض تجريبي']);

        $this->get(route('landing-pages.show', 'demo-offer'))->assertOk()->assertSee('عرض تجريبي');
        $this->post(route('landing-pages.submit', 'demo-offer'), ['name' => 'عميل', 'phone' => '0590000000', 'quantity' => 2, 'city' => 'رام الله'])->assertOk();

        self::assertDatabaseHas('orders', ['account_id' => $account->id, 'total' => 50]);
        self::assertDatabaseHas('customers', ['phone' => '0590000000']);
        self::assertDatabaseHas('order_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_dynamic_order_form_does_not_inject_an_unconfigured_name_field(): void
    {
        $account = Account::create(['name' => 'No Name Form', 'slug' => 'no-name-form']);
        OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'new']);
        $product = Product::create(['account_id' => $account->id, 'sku' => 'NO-NAME-1', 'price' => 50, 'currency' => 'AED', 'status' => 'active']);
        ProductTranslation::create(['product_id' => $product->id, 'locale' => 'ar', 'name' => 'منتج تجريبي']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'slug' => 'no-name-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'show_order_form' => true,
                'order_form_fields' => [[
                    'internal_name' => 'phone',
                    'type' => 'phone',
                    'required' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                    'translations' => [['locale' => 'ar', 'label' => 'رقم الهاتف', 'placeholder' => '05xxxxxxxx']],
                ]],
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'عرض دون اسم']);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('name="custom[phone]"', false)
            ->assertDontSee('<input name="name"', false)
            ->assertDontSee('name="city"', false)
            ->assertDontSee('name="quantity"', false);

        $this->post(route('landing-pages.submit', $page->slug), [
            'custom' => ['phone' => '0500000001'],
        ])->assertOk();

        self::assertDatabaseHas('customers', [
            'phone' => '0500000001',
            'name' => 'عميل صفحة الهبوط',
        ]);
    }

    public function test_an_option_badge_can_render_an_optional_image(): void
    {
        $account = Account::create(['name' => 'Badge Image Account', 'slug' => 'badge-image-account']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'badge-image-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'show_order_form' => true,
                'order_form_fields' => [[
                    'internal_name' => 'offer',
                    'type' => 'radio',
                    'options' => "Basic offer\nPremium offer",
                    'is_active' => true,
                    'translations' => [['locale' => 'ar', 'label' => 'Choose offer']],
                    'option_badges' => [[
                        'option_number' => 2,
                        'badge_text_ar' => 'Best seller',
                        'image_path' => 'landing-pages/form-option-badges/premium.webp',
                        'image_size' => 'large',
                        'image_shape' => 'circle',
                    ]],
                ]],
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'Badge Image Offer']);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('class="option-adornment"', false)
            ->assertSee('class="option-badge-image image-shape-circle"', false)
            ->assertSee('/storage/landing-pages/form-option-badges/premium.webp', false)
            ->assertSee('--badge-image-size:64px', false)
            ->assertSee('Best seller');

        self::assertStringContainsString(
            '.order-form-card .field-options .option-choice>.option-adornment{display:inline-flex!important}',
            file_get_contents(public_path('css/landing-page.css')),
        );
    }

    public function test_hidden_social_media_section_is_not_rendered(): void
    {
        $account = Account::create(['name' => 'Hidden Social', 'slug' => 'hidden-social']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'hidden-social-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'social_media' => [[
                    'platform' => 'instagram',
                    'url' => 'https://instagram.com/hidden-social-test',
                    'is_active' => true,
                ]],
                'section_order' => [[
                    'type' => 'hero',
                    'is_visible' => true,
                ]],
            ],
        ]);
        LandingPageTranslation::create([
            'landing_page_id' => $page->id,
            'locale' => 'ar',
            'title' => 'Hidden Social Offer',
        ]);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertDontSee('<section class="social-section"', false)
            ->assertDontSee('hidden-social-test', false);
    }

    public function test_social_media_icon_shape_overrides_the_base_link_radius(): void
    {
        $account = Account::create(['name' => 'Social Shape', 'slug' => 'social-shape']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'social-shape-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'social_icon_shape' => 'square',
                'social_media' => [[
                    'platform' => 'instagram',
                    'url' => 'https://instagram.com/social-shape-test',
                    'is_active' => true,
                ]],
                'section_order' => [[
                    'type' => 'social_media',
                    'is_visible' => true,
                ]],
            ],
        ]);
        LandingPageTranslation::create([
            'landing_page_id' => $page->id,
            'locale' => 'ar',
            'title' => 'Social Shape Offer',
        ]);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('.social-section .social-grid .social-icon{width:54px;height:54px;padding:0;border-radius:4px', false)
            ->assertSee('social-shape-test', false);
    }

    public function test_saved_section_order_controls_gallery_social_and_unlisted_sections(): void
    {
        $account = Account::create(['name' => 'Ordered Sections', 'slug' => 'ordered-sections']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'ordered-sections-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'show_order_form' => true,
                'gallery_images_ar' => ['landing-pages/gallery/example.jpg'],
                'social_media' => [[
                    'platform' => 'instagram',
                    'url' => 'https://instagram.com/ordered-sections-test',
                    'is_active' => true,
                ]],
                'accordion_enabled' => true,
                'accordion_items' => [[
                    'title_ar' => 'Unlisted accordion',
                    'content_ar' => 'This section must stay hidden.',
                    'is_active' => true,
                ]],
                'footer_enabled' => true,
                'footer_html_ar' => 'Ordered footer',
                'section_order' => [
                    ['type' => 'hero', 'is_visible' => true],
                    ['type' => 'order_form', 'is_visible' => true],
                    ['type' => 'product_gallery', 'is_visible' => true],
                    ['type' => 'social_media', 'is_visible' => true],
                    ['type' => 'footer', 'is_visible' => true],
                ],
            ],
        ]);
        LandingPageTranslation::create([
            'landing_page_id' => $page->id,
            'locale' => 'ar',
            'title' => 'Ordered Sections Offer',
        ]);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('id="order-form"', false)
            ->assertSee('gallery-section" style="order:2', false)
            ->assertSee('social-section" style="order:3', false)
            ->assertSee('Ordered footer')
            ->assertDontSee('<section class="section accordion-section', false);
    }

    public function test_standard_utm_parameters_are_preserved_by_the_form_and_saved_with_the_order(): void
    {
        $account = Account::create(['name' => 'Campaign Account', 'slug' => 'campaign-account']);
        OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'New', 'name_en' => 'New', 'slug' => 'new']);
        $product = Product::create(['account_id' => $account->id, 'sku' => 'CAMPAIGN-1', 'price' => 100, 'currency' => 'AED', 'status' => 'active']);
        ProductTranslation::create(['product_id' => $product->id, 'locale' => 'ar', 'name' => 'Campaign Product']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'slug' => 'campaign-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'Campaign Offer']);

        $this->get(route('landing-pages.show', ['slug' => $page->slug, 'utm_source' => 'facebook', 'utm_campaign' => 'summer']))
            ->assertOk()
            ->assertSee('utm_source=facebook', false)
            ->assertSee('utm_campaign=summer', false);

        $this->post(route('landing-pages.submit', ['slug' => $page->slug, 'utm_source' => 'facebook', 'utm_campaign' => 'summer']), [
            'name' => 'Campaign Customer',
            'phone' => '0500000099',
            'quantity' => 1,
        ])->assertOk();

        $order = Order::query()->latest('id')->firstOrFail();
        self::assertSame('facebook', $order->source);
        self::assertSame('facebook', data_get($order->utm_parameters, 'utm_source'));
        self::assertSame('summer', data_get($order->utm_parameters, 'utm_campaign'));
    }

    public function test_admin_can_download_selected_order_invoices_as_one_pdf_scoped_to_their_account(): void
    {
        $account = Account::create(['name' => 'Invoice Account', 'slug' => 'invoice-account']);
        $otherAccount = Account::create(['name' => 'Other Invoice Account', 'slug' => 'other-invoice-account']);
        $user = User::factory()->create(['account_id' => $account->id]);
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'invoice-new']);
        $otherStatus = OrderStatus::create(['account_id' => $otherAccount->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'other-invoice-new']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'عميل الفاتورة', 'phone' => '0500000000', 'email' => 'invoice@example.com']);
        $otherCustomer = Customer::create(['account_id' => $otherAccount->id, 'name' => 'عميل آخر', 'phone' => '0511111111']);

        $firstOrder = Order::create(['account_id' => $account->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'INV-001', 'subtotal' => 100, 'total' => 100, 'currency' => 'AED', 'notes' => 'اتصل قبل التوصيل']);
        $secondOrder = Order::create(['account_id' => $account->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'INV-002', 'subtotal' => 200, 'total' => 200, 'currency' => 'AED', 'notes' => 'التوصيل مساءً']);
        $otherOrder = Order::create(['account_id' => $otherAccount->id, 'customer_id' => $otherCustomer->id, 'order_status_id' => $otherStatus->id, 'order_number' => 'INV-OTHER', 'subtotal' => 300, 'total' => 300, 'currency' => 'AED']);
        OrderItem::create(['order_id' => $firstOrder->id, 'product_name' => 'العرض الأول', 'quantity' => 1, 'unit_price' => 100, 'total' => 100]);
        OrderItem::create(['order_id' => $secondOrder->id, 'product_name' => 'العرض الثاني', 'quantity' => 1, 'unit_price' => 200, 'total' => 200]);

        $response = $this->actingAs($user)->post(route('reports.order-status.invoices'), [
            'order_ids' => [$firstOrder->id, $secondOrder->id],
        ]);

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        self::assertStringStartsWith('%PDF-', $response->getContent());

        $this->actingAs($user)
            ->post(route('reports.order-status.invoices'), ['order_ids' => [$firstOrder->id, $otherOrder->id]])
            ->assertForbidden();
    }

    public function test_invoice_views_always_include_the_order_notes_section(): void
    {
        $account = Account::create(['name' => 'Notes Account', 'slug' => 'notes-account']);
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'notes-new']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'عميل الملاحظات', 'phone' => '0522222222']);
        $order = Order::create(['account_id' => $account->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'NOTES-001', 'subtotal' => 50, 'total' => 50, 'currency' => 'AED', 'notes' => 'يرجى إحضار جهاز الدفع']);

        $this->get(URL::signedRoute('orders.invoice', ['order' => $order->id]))
            ->assertOk()
            ->assertSee('ملاحظات الطلب')
            ->assertSee('يرجى إحضار جهاز الدفع')
            ->assertSee('تم إصدار هذه الفاتورة بواسطة')
            ->assertSee('Notes Account');
    }

    public function test_login_code_email_uses_the_company_name_from_system_settings(): void
    {
        config(['mail.from.address' => 'info@example.com']);

        $account = Account::create(['name' => 'شركة المواسم', 'slug' => 'mail-company']);
        $user = User::factory()->create(['account_id' => $account->id, 'name' => 'رعد']);

        $mail = (new VerifyEmailAuthentication('123456', 5))->toMail($user);

        self::assertSame('mail.auth.login-code', $mail->view);
        self::assertSame(['info@example.com', 'شركة المواسم'], $mail->from);
        self::assertSame('شركة المواسم', $mail->viewData['companyName']);
        self::assertStringContainsString('شركة المواسم', view($mail->view, $mail->viewData)->render());
    }

    public function test_invoice_displays_the_account_logo_without_the_order_status(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('accounts/logos/invoice-logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $account = Account::create(['name' => 'Logo Account', 'slug' => 'logo-account', 'logo_path' => 'accounts/logos/invoice-logo.png']);
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'STATUS_SHOULD_NOT_APPEAR', 'name_en' => 'Hidden invoice status', 'slug' => 'invoice-hidden-status']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'Invoice Customer', 'phone' => '0501234567']);
        $order = Order::create(['account_id' => $account->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'LOGO-001', 'subtotal' => 50, 'total' => 50, 'currency' => 'AED']);

        $this->get(URL::signedRoute('orders.invoice', ['order' => $order->id]))
            ->assertOk()
            ->assertSee('data:image/png;base64,', false)
            ->assertDontSee('STATUS_SHOULD_NOT_APPEAR');

        $order->load(['account', 'customer', 'items', 'status']);
        $batchHtml = view('public.orders.batch-invoices', [
            'orders' => collect([$order]),
            'logoData' => 'data:image/png;base64,logo-data',
            'fontRegular' => '',
            'fontBold' => '',
        ])->render();

        self::assertStringContainsString('data:image/png;base64,logo-data', $batchHtml);
        self::assertStringContainsString('Logo Account', $batchHtml);
        self::assertStringNotContainsString('STATUS_SHOULD_NOT_APPEAR', $batchHtml);
    }

    public function test_order_status_report_filters_by_status_and_landing_page_together(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $account = Account::create(['name' => 'Filtered Report Account', 'slug' => 'filtered-report-account']);
        $user = User::factory()->create(['account_id' => $account->id]);
        $user->assignRole('Account Owner');
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'قيد المتابعة', 'name_en' => 'Following', 'slug' => 'following-report']);
        $firstPage = LandingPage::create(['account_id' => $account->id, 'slug' => 'first-campaign', 'status' => LandingPageStatus::Published, 'default_locale' => 'ar']);
        $secondPage = LandingPage::create(['account_id' => $account->id, 'slug' => 'second-campaign', 'status' => LandingPageStatus::Published, 'default_locale' => 'ar']);
        LandingPageTranslation::create(['landing_page_id' => $firstPage->id, 'locale' => 'ar', 'title' => 'الحملة الأولى']);
        LandingPageTranslation::create(['landing_page_id' => $secondPage->id, 'locale' => 'ar', 'title' => 'الحملة الثانية']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'Filter Customer', 'phone' => '0501112233']);
        Order::create(['account_id' => $account->id, 'landing_page_id' => $firstPage->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'FILTER-MATCH', 'subtotal' => 100, 'total' => 100, 'currency' => 'AED']);
        Order::create(['account_id' => $account->id, 'landing_page_id' => $secondPage->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'FILTER-HIDDEN', 'subtotal' => 200, 'total' => 200, 'currency' => 'AED']);

        $this->actingAs($user)
            ->get('/admin/order-status-report?status='.$status->id.'&landing_page='.$firstPage->id)
            ->assertOk()
            ->assertSee('name="landing_page"', false)
            ->assertSee('FILTER-MATCH')
            ->assertDontSee('FILTER-HIDDEN');
    }

    public function test_order_status_report_lists_dynamic_statuses_and_account_orders_for_invoice_selection(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $account = Account::create(['name' => 'Report Account', 'slug' => 'report-account']);
        $otherAccount = Account::create(['name' => 'Hidden Report Account', 'slug' => 'hidden-report-account']);
        $user = User::factory()->create(['account_id' => $account->id]);
        $user->assignRole('Account Owner');
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'حالة ديناميكية', 'name_en' => 'Dynamic Status', 'slug' => 'dynamic-report-status']);
        $otherStatus = OrderStatus::create(['account_id' => $otherAccount->id, 'name_ar' => 'حالة مخفية', 'name_en' => 'Hidden Status', 'slug' => 'hidden-report-status']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'عميل التقرير', 'phone' => '0533333333']);
        $otherCustomer = Customer::create(['account_id' => $otherAccount->id, 'name' => 'عميل مخفي', 'phone' => '0544444444']);
        $order = Order::create(['account_id' => $account->id, 'customer_id' => $customer->id, 'order_status_id' => $status->id, 'order_number' => 'REPORT-001', 'subtotal' => 70, 'total' => 70, 'currency' => 'AED', 'notes' => 'ملاحظة التقرير']);
        Order::create(['account_id' => $otherAccount->id, 'customer_id' => $otherCustomer->id, 'order_status_id' => $otherStatus->id, 'order_number' => 'REPORT-HIDDEN', 'subtotal' => 80, 'total' => 80, 'currency' => 'AED']);

        $this->actingAs($user)
            ->get('/admin/order-status-report?status='.$status->id)
            ->assertOk()
            ->assertSee('حالة ديناميكية')
            ->assertSee('REPORT-001')
            ->assertSee('name="order_ids[]" value="'.$order->id.'"', false)
            ->assertSee('ملاحظة التقرير')
            ->assertDontSee('REPORT-HIDDEN')
            ->assertDontSee('حالة مخفية');
    }

    public function test_featured_approved_reviews_are_rendered_on_a_landing_page(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-reviews']);
        $page = LandingPage::create(['account_id' => $account->id, 'slug' => 'review-demo', 'status' => LandingPageStatus::Published, 'default_locale' => 'ar']);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'Review Demo']);
        LandingPageSection::create(['landing_page_id' => $page->id, 'type' => 'testimonials', 'sort_order' => 1, 'is_visible' => true]);
        Review::create(['account_id' => $account->id, 'landing_page_id' => $page->id, 'name' => 'Sara', 'rating' => 5, 'content' => 'Excellent product', 'is_approved' => true, 'is_featured' => true]);
        Review::create(['account_id' => $account->id, 'landing_page_id' => $page->id, 'name' => 'Hidden', 'rating' => 2, 'content' => 'Not visible', 'is_approved' => false, 'is_featured' => true]);

        $this->get(route('landing-pages.show', 'review-demo'))->assertOk()->assertSee('Sara')->assertDontSee('Hidden');
    }

    public function test_landing_page_can_render_only_selected_approved_reviews_in_the_showcase(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-curated-reviews']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'curated-reviews',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'reviews_enabled' => true,
                'reviews_showcase_enabled' => true,
                'reviews_showcase_title_ar' => 'تجارب عملائنا المختارة',
                'reviews_showcase_style' => 'soft',
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'Curated Reviews']);

        $selected = Review::create([
            'account_id' => $account->id,
            'name' => 'Selected Customer',
            'rating' => 5,
            'content' => 'Selected review content',
            'is_approved' => true,
        ]);
        Review::create([
            'account_id' => $account->id,
            'landing_page_id' => $page->id,
            'name' => 'Unselected Customer',
            'rating' => 4,
            'content' => 'Should not be rendered',
            'is_approved' => true,
        ]);

        $page->update(['settings' => array_merge($page->settings, [
            'reviews_showcase_review_ids' => [$selected->id],
        ])]);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('data-reviews-showcase', false)
            ->assertSee('reviews-showcase--soft', false)
            ->assertSee('تجارب عملائنا المختارة')
            ->assertSee('Selected Customer')
            ->assertDontSee('Unselected Customer');
    }

    public function test_customer_can_submit_a_review_from_an_enabled_landing_page(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-public-reviews']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'public-review-demo',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => ['reviews_enabled' => true, 'reviews_allow_submission' => true],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'Review Demo']);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('data-review-open', false)
            ->assertSee('المواسم منذ 10 سنوات')
            ->assertSee('13 ألف عميل');

        $this->post(route('landing-pages.reviews.store', $page->slug), [
            'name' => 'Rayan',
            'rating' => 5,
            'content' => 'Excellent service',
        ])->assertRedirect();

        self::assertDatabaseHas('reviews', [
            'landing_page_id' => $page->id,
            'name' => 'Rayan',
            'rating' => 5,
            'source' => 'landing_page',
            'is_approved' => false,
            'is_verified_purchase' => false,
        ]);
    }

    public function test_customer_can_submit_stars_without_an_optional_comment(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-rating-only']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'rating-only',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => ['reviews_enabled' => true, 'reviews_allow_submission' => true],
        ]);

        $this->post(route('landing-pages.reviews.store', $page->slug), [
            'name' => 'Rayan',
            'rating' => 5,
        ])->assertRedirect();

        self::assertDatabaseHas('reviews', [
            'landing_page_id' => $page->id,
            'rating' => 5,
            'content' => '',
        ]);
    }

    public function test_signed_order_review_is_saved_as_a_verified_purchase_once(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-verified-reviews']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'Rayan', 'phone' => '0500000000']);
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'new']);
        $order = Order::create([
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'order_status_id' => $status->id,
            'order_number' => 'LDV-REVIEW-1',
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'AED',
        ]);
        $url = URL::temporarySignedRoute('reviews.order.form', now()->addHour(), ['order' => $order->id]);

        $this->get($url)->assertOk()->assertSee('LDV-REVIEW-1');
        $this->post($url, ['name' => 'Rayan', 'rating' => 4, 'content' => 'Very good'])->assertRedirect();

        self::assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'account_id' => $account->id,
            'rating' => 4,
            'source' => 'order_link',
            'is_verified_purchase' => true,
        ]);
        self::assertSame(1, Review::where('order_id', $order->id)->count());
    }

    public function test_professional_media_accordion_and_live_counters_are_rendered(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-media']);
        $product = Product::create(['account_id' => $account->id, 'sku' => 'MEDIA-1', 'price' => 25, 'currency' => 'AED', 'quantity' => 7, 'status' => 'active']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'slug' => 'media-demo',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'slider_enabled' => true,
                'slider_images_ar' => ['landing-pages/sliders/ar/one.jpg', 'landing-pages/sliders/ar/two.jpg'],
                'video_enabled' => true,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'accordion_enabled' => true,
                'accordion_items' => [['title_ar' => 'سؤال تجريبي', 'title_en' => 'Test question', 'content_ar' => '<p>إجابة تجريبية</p>', 'content_en' => '<p>Test answer</p>', 'is_active' => true]],
                'limited_stock_enabled' => true,
                'limited_stock_source' => 'product',
                'limited_stock_label_ar' => 'بقي {count} فقط',
                'viewer_counter_enabled' => true,
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'عرض الوسائط']);

        $this->get(route('landing-pages.show', 'media-demo'))
            ->assertOk()
            ->assertSee('data-media-slider', false)
            ->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('سؤال تجريبي')
            ->assertSee('بقي 7 فقط')
            ->assertSee('data-viewer-counter', false);

        $this->getJson(route('landing-pages.viewers', 'media-demo'))
            ->assertOk()
            ->assertJson(['count' => 1]);
    }

    public function test_multilingual_store_ticker_can_render_above_the_landing_page(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-ticker']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'ticker-demo',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'store_ticker' => [
                    'enabled' => true,
                    'placement' => 'top',
                    'style' => 'gradient',
                    'direction' => 'left',
                    'items' => [[
                        'text_ar' => 'توصيل مجاني للطلبات المختارة',
                        'text_en' => 'Free delivery on selected orders',
                        'icon' => 'truck',
                        'is_active' => true,
                    ]],
                ],
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'Ticker Demo']);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('store-ticker ticker-style-gradient', false)
            ->assertSee('توصيل مجاني للطلبات المختارة');

        $this->withSession(['locale' => 'en'])
            ->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('Free delivery on selected orders');
    }

    public function test_verification_badge_can_render_centered_above_the_title(): void
    {
        $account = Account::create(['name' => 'Badge Account', 'slug' => 'badge-account']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'centered-badge',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'title_badge_enabled' => true,
                'title_badge_text_ar' => 'منتج مفحوص وموثق',
                'title_badge_placement' => 'above',
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'عنوان العرض']);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('badge-placement-above', false)
            ->assertSee('منتج مفحوص وموثق');
    }

    public function test_landing_page_can_use_a_custom_background_and_full_width_product_image(): void
    {
        $account = Account::create(['name' => 'Visual Account', 'slug' => 'visual-account']);
        $product = Product::create([
            'account_id' => $account->id,
            'sku' => 'VISUAL-1',
            'price' => 100,
            'currency' => 'AED',
            'status' => 'active',
            'primary_image_path' => 'products/visual.jpg',
        ]);
        ProductTranslation::create(['product_id' => $product->id, 'locale' => 'ar', 'name' => 'منتج مرئي']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'slug' => 'visual-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'page_background_color' => '#EAF3E6',
                'product_image_full_width' => true,
            ],
        ]);
        LandingPageTranslation::create(['landing_page_id' => $page->id, 'locale' => 'ar', 'title' => 'عرض مرئي']);

        $this->get(route('landing-pages.show', $page->slug))
            ->assertOk()
            ->assertSee('--surface:#EAF3E6', false)
            ->assertSee('class="hero hero-image-full"', false)
            ->assertSee('class="product-image"', false);
    }

    public function test_order_follow_up_becomes_due_and_all_changes_are_logged(): void
    {
        $this->travelTo('2026-07-20 09:00:00');

        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-follow-ups']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'Rayan', 'phone' => '0500000000']);
        $newStatus = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'new']);
        $postponedStatus = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'مؤجل', 'name_en' => 'Postponed', 'slug' => 'postponed']);
        $order = Order::create([
            'account_id' => $account->id,
            'customer_id' => $customer->id,
            'order_status_id' => $newStatus->id,
            'order_number' => 'LDV-FOLLOW-1',
            'subtotal' => 100,
            'total' => 100,
            'currency' => 'AED',
        ]);

        self::assertFalse($order->isFollowUpDue());
        self::assertDatabaseHas('order_activities', ['order_id' => $order->id, 'type' => 'system']);

        $order->update([
            'order_status_id' => $postponedStatus->id,
            'follow_up_at' => now()->addDay(),
            'follow_up_note' => 'العميل طلب الاتصال غدًا.',
        ]);
        $order->refresh();

        self::assertTrue($order->hasPendingFollowUp());
        self::assertFalse($order->isFollowUpDue());
        self::assertDatabaseHas('order_activities', ['order_id' => $order->id, 'type' => 'follow_up']);

        $this->travel(2)->days();
        $order->refresh();
        self::assertTrue($order->isFollowUpDue());

        $order->update(['follow_up_completed_at' => now()]);
        $order->refresh();
        self::assertFalse($order->hasPendingFollowUp());
        self::assertStringContainsString('تم إنجاز التذكير', (string) $order->activities()->first()?->body);
    }

    public function test_whatsapp_order_template_resolves_system_and_dynamic_form_tokens(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'acme-message-template']);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'ريان المدادحة', 'phone' => '0500000000', 'email' => 'rayan@example.com']);
        $status = OrderStatus::create(['account_id' => $account->id, 'name_ar' => 'جديد', 'name_en' => 'New', 'slug' => 'new']);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'slug' => 'message-template',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'settings' => [
                'order_whatsapp_message_ar' => 'مرحبًا {full_name_ar}، طلبك {order_number} وعرضك {offer} بقيمة {total} {currency}.',
                'order_whatsapp_message_en' => 'Hello {customer_name}, your order is {order_number}.',
            ],
        ]);
        $order = Order::create([
            'account_id' => $account->id,
            'landing_page_id' => $page->id,
            'customer_id' => $customer->id,
            'order_status_id' => $status->id,
            'order_number' => 'LDV-MSG-1',
            'subtotal' => 749,
            'total' => 749,
            'currency' => 'AED',
            'form_data' => ['full_name_ar' => 'ريان المدادحة', 'offer' => 'العرض المتكامل'],
        ]);

        $message = OrderMessageTemplate::render($order, 'ar');

        self::assertStringContainsString('ريان المدادحة', $message);
        self::assertStringContainsString('LDV-MSG-1', $message);
        self::assertStringContainsString('العرض المتكامل', $message);
        self::assertStringContainsString('749.00 AED', $message);
        self::assertStringNotContainsString('{offer}', $message);
    }

    public function test_delivered_status_deducts_product_variant_and_landing_page_stock_once_and_restores_it(): void
    {
        $account = Account::create(['name' => 'Inventory Account', 'slug' => 'inventory-account']);
        $product = Product::create([
            'account_id' => $account->id,
            'sku' => 'OLIVE-16KG',
            'price' => 599,
            'currency' => 'AED',
            'quantity' => 20,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'OLIVE-16KG-PREMIUM',
            'option_values' => ['size' => '16kg'],
            'price' => 599,
            'quantity' => 10,
            'is_active' => true,
        ]);
        $page = LandingPage::create([
            'account_id' => $account->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'slug' => 'inventory-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
            'track_inventory' => true,
            'stock_quantity' => 8,
            'low_stock_threshold' => 3,
        ]);
        $customer = Customer::create(['account_id' => $account->id, 'name' => 'Customer', 'phone' => '0500000001']);
        $newStatus = OrderStatus::create([
            'account_id' => $account->id,
            'name_ar' => 'جديد',
            'name_en' => 'New',
            'slug' => 'new',
            'deduct_inventory' => false,
        ]);
        $deliveredStatus = OrderStatus::create([
            'account_id' => $account->id,
            'name_ar' => 'تم التسليم',
            'name_en' => 'Delivered',
            'slug' => 'delivered',
            'is_final' => true,
            'deduct_inventory' => true,
        ]);
        $order = Order::create([
            'account_id' => $account->id,
            'landing_page_id' => $page->id,
            'customer_id' => $customer->id,
            'order_status_id' => $newStatus->id,
            'order_number' => 'LDV-STOCK-1',
            'subtotal' => 1198,
            'total' => 1198,
            'currency' => 'AED',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Olive Oil 16kg',
            'quantity' => 2,
            'unit_price' => 599,
            'total' => 1198,
        ]);

        $order->update(['order_status_id' => $deliveredStatus->id]);

        self::assertSame(18, $product->fresh()->quantity);
        self::assertSame(8, $variant->fresh()->quantity);
        self::assertSame(6, $page->fresh()->stock_quantity);
        self::assertNotNull($order->fresh()->inventory_deducted_at);
        self::assertDatabaseHas('order_activities', ['order_id' => $order->id, 'type' => 'inventory']);

        $order->update(['notes' => 'No second deduction']);
        self::assertSame(18, $product->fresh()->quantity);
        self::assertSame(8, $variant->fresh()->quantity);
        self::assertSame(6, $page->fresh()->stock_quantity);

        $order->update(['order_status_id' => $newStatus->id]);

        self::assertSame(20, $product->fresh()->quantity);
        self::assertSame(10, $variant->fresh()->quantity);
        self::assertSame(8, $page->fresh()->stock_quantity);
        self::assertNull($order->fresh()->inventory_deducted_at);
    }

    public function test_products_support_localized_details_media_options_and_variant_translations(): void
    {
        $account = Account::create(['name' => 'Catalog Account', 'slug' => 'catalog-account']);
        $product = Product::create([
            'account_id' => $account->id,
            'sku' => 'OLIVE-MULTI',
            'price' => 599,
            'currency' => 'AED',
            'quantity' => 10,
            'options' => [[
                'name_ar' => 'الحجم',
                'name_en' => 'Size',
                'values' => [['code' => '16kg', 'label_ar' => '16 كيلو', 'label_en' => '16 KG']],
            ]],
        ]);
        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'ar',
            'name' => 'زيت زيتون',
            'description' => 'وصف مختصر',
            'details' => '<p>تفاصيل كاملة للمنتج</p>',
        ]);
        $product->media()->create([
            'locale' => null,
            'media_type' => 'image',
            'file_path' => 'products/media/shared.webp',
            'title' => 'Shared image',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $arabicMedia = $product->media()->create([
            'locale' => 'ar',
            'media_type' => 'image',
            'file_path' => 'products/media/arabic.webp',
            'title' => 'الصورة العربية',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'OLIVE-MULTI-16',
            'option_values' => ['size' => '16kg'],
            'price' => 599,
            'quantity' => 5,
            'is_active' => true,
        ]);
        $variant->translations()->createMany([
            ['locale' => 'ar', 'name' => 'عبوة 16 كيلو', 'description' => 'الوصف بالعربية'],
            ['locale' => 'en', 'name' => '16 KG Pack', 'description' => 'English description'],
        ]);

        $product->load(['translations', 'media']);
        $variant->load('translations');

        self::assertSame('Size', data_get($product->options, '0.name_en'));
        self::assertSame('<p>تفاصيل كاملة للمنتج</p>', $product->translations->firstWhere('locale', 'ar')?->details);
        self::assertSame($arabicMedia->id, $product->localizedMedia('ar')?->id);
        self::assertSame('products/media/shared.webp', $product->localizedMedia('en')?->file_path);
        self::assertSame('16 KG Pack', $variant->translation('en')?->name);
    }

    public function test_website_header_and_footer_menus_are_configurable(): void
    {
        Account::create([
            'name' => 'Dynamic Navigation',
            'slug' => 'dynamic-navigation',
            'settings' => [
                'header_menu' => [['label_ar' => 'كتالوجنا', 'label_en' => 'Catalog', 'url' => '/products', 'is_active' => true]],
                'footer_menu' => [['label_ar' => 'الشروط القانونية', 'label_en' => 'Legal terms', 'url' => '/terms-and-conditions', 'is_active' => true]],
            ],
        ]);

        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee('كتالوجنا')
            ->assertSee('الشروط القانونية')
            ->assertSee(url('/terms-and-conditions'));
    }

    public function test_homepage_sections_footer_and_product_order_are_database_driven(): void
    {
        $account = Account::create([
            'name' => 'Dynamic Store',
            'slug' => 'dynamic-store',
            'settings' => [
                'home_slider_enabled' => false,
                'home_features' => [[
                    'icon' => 'quality',
                    'title_ar' => 'ميزة ديناميكية',
                    'title_en' => 'Dynamic feature',
                    'subtitle_ar' => 'وصف من قاعدة البيانات',
                    'subtitle_en' => 'Database description',
                    'is_active' => true,
                ]],
                'home_products_kicker_ar' => 'اختيار خاص',
                'home_products_title_ar' => 'ترتيب المنتجات',
                'home_products_description_ar' => 'وصف المنتجات المخصص',
                'home_about_title_ar' => 'عنوان تعريفي مخصص',
                'home_about_description_ar' => 'وصف تعريفي مخصص',
                'home_campaigns_title_ar' => 'عروض مخصصة',
                'footer_description_ar' => 'وصف الفوتر الديناميكي',
                'footer_links_title_ar' => 'روابط المتجر',
                'footer_contact_title_ar' => 'بيانات التواصل',
                'footer_help_title_ar' => 'تحتاج مساعدة الآن؟',
                'footer_help_text_ar' => 'نحن جاهزون لخدمتك.',
                'footer_copyright_ar' => 'حقوق {company} محفوظة لعام {year}',
                'footer_trust_ar' => 'تسوق بثقة',
            ],
        ]);

        $second = Product::create([
            'account_id' => $account->id,
            'sku' => 'ORDER-2',
            'price' => 20,
            'currency' => 'AED',
            'quantity' => 5,
            'status' => 'active',
            'sort_order' => 20,
        ]);
        $first = Product::create([
            'account_id' => $account->id,
            'sku' => 'ORDER-1',
            'price' => 10,
            'currency' => 'AED',
            'quantity' => 5,
            'status' => 'active',
            'sort_order' => 10,
        ]);
        ProductTranslation::create(['product_id' => $second->id, 'locale' => 'ar', 'name' => 'المنتج الثاني']);
        ProductTranslation::create(['product_id' => $first->id, 'locale' => 'ar', 'name' => 'المنتج الأول']);

        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee('ميزة ديناميكية')
            ->assertSee('اختيار خاص')
            ->assertSee('عنوان تعريفي مخصص')
            ->assertSee('وصف الفوتر الديناميكي')
            ->assertSee('روابط المتجر')
            ->assertSee('تسوق بثقة')
            ->assertSeeInOrder(['المنتج الأول', 'المنتج الثاني']);
    }

    public function test_product_cards_link_to_a_complete_product_details_page(): void
    {
        $account = Account::create(['name' => 'Catalog', 'slug' => 'catalog-details']);
        $product = Product::create([
            'account_id' => $account->id,
            'sku' => 'DETAIL-1',
            'price' => 99,
            'compare_at_price' => 120,
            'currency' => 'AED',
            'quantity' => 8,
            'status' => 'active',
            'badge_is_active' => true,
            'badge_text_ar' => 'عرض مميز',
            'badge_text_en' => 'Featured offer',
            'badge_style' => 'ribbon',
            'badge_background_color' => '#7c3aed',
            'badge_text_color' => '#ffffff',
        ]);
        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'ar',
            'name' => 'منتج بتفاصيل كاملة',
            'description' => 'وصف المنتج المختصر',
            'details' => '<h2>المكونات</h2><p>تفاصيل موثوقة وواضحة.</p>',
        ]);

        $this->get(route('site.home'))
            ->assertOk()
            ->assertSee(route('site.products.show', $product), false)
            ->assertSee('عرض مميز')
            ->assertSee('product-custom-badge--ribbon', false);

        $this->get(route('site.products.show', $product))
            ->assertOk()
            ->assertSee('منتج بتفاصيل كاملة')
            ->assertSee('تفاصيل موثوقة وواضحة.')
            ->assertSee('عرض مميز')
            ->assertSee('99.00');

        $this->withSession(['locale' => 'en'])
            ->get(route('site.products.show', $product))
            ->assertOk()
            ->assertSee('Featured offer');

        $product->update(['badge_text_ar' => null, 'badge_text_en' => null]);
        self::assertSame('عرض مميز', $product->fresh()->badgeLabel('ar'));
        self::assertSame('Featured offer', $product->fresh()->badgeLabel('en'));
    }

    public function test_products_page_supports_numbered_and_infinite_loading_modes(): void
    {
        $account = Account::create([
            'name' => 'Catalog loading',
            'slug' => 'catalog-loading',
            'settings' => [
                'products_load_mode' => 'infinite',
                'products_per_page' => 4,
                'products_load_more_ar' => 'المزيد من المنتجات',
            ],
        ]);
        $this->seed(SitePagesSeeder::class);

        foreach (range(1, 5) as $index) {
            $product = Product::create([
                'account_id' => $account->id,
                'sku' => 'LOAD-'.$index,
                'price' => 10 + $index,
                'currency' => 'AED',
                'quantity' => 5,
                'status' => 'active',
                'sort_order' => $index,
            ]);
            ProductTranslation::create([
                'product_id' => $product->id,
                'locale' => 'ar',
                'name' => 'منتج تحميل '.$index,
            ]);
        }

        $this->get('/products')
            ->assertOk()
            ->assertSee('منتج تحميل 1')
            ->assertDontSee('منتج تحميل 5')
            ->assertSee('المزيد من المنتجات')
            ->assertSee('data-products-loader', false);

        $nextPage = $this->getJson('/products?page=2&products_partial=1')
            ->assertOk()
            ->assertJsonPath('next_url', null);
        self::assertStringContainsString('منتج تحميل 5', $nextPage->json('html'));

        $account->update([
            'settings' => array_merge($account->settings, ['products_load_mode' => 'pagination']),
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('web-pagination', false)
            ->assertDontSee('data-products-loader', false);
    }

    public function test_terms_and_conditions_page_is_seeded_in_both_languages(): void
    {
        Account::create(['name' => 'Legal', 'slug' => 'legal-pages']);
        $this->seed(SitePagesSeeder::class);

        $this->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('الأحكام والشروط')
            ->assertSee('قبول الشروط');

        $this->withSession(['locale' => 'en'])
            ->get('/terms-and-conditions')
            ->assertOk()
            ->assertSee('Terms and conditions')
            ->assertSee('Acceptance');
    }
}
