<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\MarketingPopup;
use App\Services\MarketingPopupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MarketingPopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_popup_is_rendered_on_its_target_page_in_the_current_language(): void
    {
        $account = Account::create(['name' => 'Almwasem', 'slug' => 'almwasem-popup']);
        MarketingPopup::create([
            'account_id' => $account->id,
            'internal_name' => 'Welcome offer',
            'template' => 'split_offer',
            'title_ar' => 'عرض خاص لزوار المواسم',
            'title_en' => 'A special offer for Almwasem visitors',
            'page_scope' => 'homepage',
            'locale' => 'ar',
            'device' => 'all',
            'trigger_type' => 'delay',
            'frequency' => 'once_session',
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('عرض خاص لزوار المواسم')
            ->assertSee('data-marketing-popup', false)
            ->assertDontSee('A special offer for Almwasem visitors');
    }

    public function test_popup_service_respects_schedule_path_and_locale(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'popup-targeting']);
        MarketingPopup::create([
            'account_id' => $account->id,
            'internal_name' => 'Selected page',
            'template' => 'minimal',
            'title_en' => 'Selected page offer',
            'page_scope' => 'selected',
            'target_paths' => ['/l/summer'],
            'locale' => 'en',
            'device' => 'all',
            'trigger_type' => 'delay',
            'frequency' => 'once_day',
            'starts_at' => now()->subMinute(),
            'ends_at' => now()->addHour(),
            'is_active' => true,
        ]);
        MarketingPopup::create([
            'account_id' => $account->id,
            'internal_name' => 'Expired',
            'template' => 'minimal',
            'title_en' => 'Expired popup',
            'page_scope' => 'all',
            'locale' => 'all',
            'device' => 'all',
            'trigger_type' => 'delay',
            'frequency' => 'always',
            'ends_at' => now()->subMinute(),
            'is_active' => true,
        ]);

        $service = app(MarketingPopupService::class);

        self::assertCount(1, $service->forPage($account->id, 'l/summer', 'en'));
        self::assertCount(0, $service->forPage($account->id, 'l/summer', 'ar'));
        self::assertCount(0, $service->forPage($account->id, 'l/other', 'en'));
    }

    public function test_popup_events_increment_impressions_and_clicks(): void
    {
        $account = Account::create(['name' => 'Acme', 'slug' => 'popup-events']);
        $popup = MarketingPopup::create([
            'account_id' => $account->id,
            'internal_name' => 'Analytics',
            'template' => 'announcement',
            'title_ar' => 'تحليل النافذة',
            'page_scope' => 'all',
            'locale' => 'all',
            'device' => 'all',
            'trigger_type' => 'delay',
            'frequency' => 'always',
            'is_active' => true,
        ]);

        $this->postJson(route('marketing-popups.event', $popup), ['event' => 'impression'])->assertOk();
        $this->postJson(route('marketing-popups.event', $popup), ['event' => 'click'])->assertOk();

        $popup->refresh();
        self::assertSame(1, $popup->impressions_count);
        self::assertSame(1, $popup->clicks_count);
    }
}
