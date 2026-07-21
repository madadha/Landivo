<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ThankYouPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ThankYouPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_thank_you_page_is_public_and_localized(): void
    {
        $account = Account::create(['name' => 'Almwasem', 'slug' => 'thank-pages']);
        $page = ThankYouPage::create([
            'account_id' => $account->id,
            'internal_name' => 'Olive campaign thank you',
            'slug' => 'olive-thanks',
            'is_active' => true,
            'default_locale' => 'ar',
            'title_ar' => 'شكرًا لك، تم استلام طلبك',
            'title_en' => 'Thank you, your request was received',
            'message_ar' => 'سنتواصل معك قريبًا.',
            'message_en' => 'We will contact you soon.',
        ]);

        $this->get(route('thank-you-pages.show', $page))
            ->assertOk()
            ->assertSee('شكرًا لك، تم استلام طلبك')
            ->assertDontSee('Thank you, your request was received');

        $this->get(route('thank-you-pages.show', ['thankYouPage' => $page, 'lang' => 'en']))
            ->assertOk()
            ->assertSee('Thank you, your request was received')
            ->assertDontSee('شكرًا لك، تم استلام طلبك');
    }

    public function test_inactive_thank_you_page_is_not_public(): void
    {
        $account = Account::create(['name' => 'Almwasem', 'slug' => 'inactive-thank-page']);
        $page = ThankYouPage::create([
            'account_id' => $account->id,
            'internal_name' => 'Inactive',
            'slug' => 'inactive',
            'is_active' => false,
        ]);

        $this->get(route('thank-you-pages.show', $page))->assertNotFound();
    }

    public function test_campaign_url_contains_configured_tracking_keys(): void
    {
        $account = Account::create(['name' => 'Almwasem', 'slug' => 'tracked-thank-page']);
        $page = ThankYouPage::create([
            'account_id' => $account->id,
            'internal_name' => 'Tracked',
            'slug' => 'tracked',
            'tracking_keys' => [
                ['key' => 'utm_source', 'value' => 'facebook', 'comment' => 'Ad source'],
                ['key' => 'utm_campaign', 'value' => 'summer', 'comment' => 'Campaign'],
            ],
        ]);

        self::assertStringContainsString('utm_source=facebook', $page->campaignUrl());
        self::assertStringContainsString('utm_campaign=summer', $page->campaignUrl());
    }
}
