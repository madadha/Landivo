<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\LandingPageStatus;
use App\Models\Account;
use App\Models\LandingPage;
use App\Models\LandingPageSection;
use App\Models\LandingPageTranslation;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
