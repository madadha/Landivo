<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\CustomerSearchReport;
use App\Filament\Pages\LandingPageGallery;
use App\LandingPageStatus;
use App\Models\Account;
use App\Models\Customer;
use App\Models\LandingPage;
use App\Models\LandingPageTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->account = Account::create(['name' => 'Landivo Account', 'slug' => 'landivo-account']);
        $this->user = User::factory()->create(['account_id' => $this->account->id]);
        $this->user->assignRole('Account Owner');

        $this->actingAs($this->user);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_landing_page_gallery_displays_only_the_current_accounts_pages(): void
    {
        $page = LandingPage::create([
            'account_id' => $this->account->id,
            'slug' => 'account-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
        ]);
        LandingPageTranslation::create([
            'landing_page_id' => $page->id,
            'locale' => 'ar',
            'title' => 'عرض الحساب الخاص',
        ]);

        $otherAccount = Account::create(['name' => 'Other Account', 'slug' => 'other-account']);
        $otherPage = LandingPage::create([
            'account_id' => $otherAccount->id,
            'slug' => 'private-other-offer',
            'status' => LandingPageStatus::Published,
            'default_locale' => 'ar',
        ]);
        LandingPageTranslation::create([
            'landing_page_id' => $otherPage->id,
            'locale' => 'ar',
            'title' => 'عرض حساب آخر',
        ]);

        Livewire::test(LandingPageGallery::class)
            ->assertSee('عرض الحساب الخاص')
            ->assertDontSee('عرض حساب آخر');
    }

    public function test_customer_search_finds_partial_name_phone_and_email_and_keeps_account_isolation(): void
    {
        Customer::create([
            'account_id' => $this->account->id,
            'name' => 'ريان المدادحة',
            'phone' => '0522150779',
            'email' => 'rayan@example.com',
        ]);

        $otherAccount = Account::create(['name' => 'Other Customers', 'slug' => 'other-customers']);
        Customer::create([
            'account_id' => $otherAccount->id,
            'name' => 'ريان من حساب آخر',
            'phone' => '0529999999',
            'email' => 'hidden@example.com',
        ]);

        Livewire::test(CustomerSearchReport::class)
            ->set('search', '05221')
            ->assertSee('ريان المدادحة')
            ->assertDontSee('ريان من حساب آخر')
            ->set('search', 'rayan@')
            ->assertSee('ريان المدادحة')
            ->assertDontSee('hidden@example.com');
    }

    public function test_products_page_renders_the_multilingual_product_and_professional_filters(): void
    {
        $product = Product::create([
            'account_id' => $this->account->id,
            'sku' => 'FILTER-001',
            'price' => 125,
            'currency' => 'AED',
            'quantity' => 3,
            'status' => 'active',
        ]);
        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'ar',
            'name' => 'منتج اختبار الفلاتر',
        ]);

        $this->get('/admin/products')
            ->assertOk()
            ->assertSee('منتج اختبار الفلاتر')
            ->assertSee('FILTER-001')
            ->assertSee('حالة المنتج')
            ->assertSee('حالة المخزون');
    }

    public function test_dashboard_contains_a_direct_public_site_button(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('فتح الموقع')
            ->assertSee('href="'.url('/').'"', false);
    }
}
