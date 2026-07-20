<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPasswordNotification;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Facades\Filament;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

final class AdminAuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_password_reset_request_page_is_available(): void
    {
        $this->get('/admin/password-reset/request')
            ->assertOk()
            ->assertSee('email', false);
    }

    public function test_admin_panel_has_password_reset_and_email_mfa_configured(): void
    {
        $panel = Filament::getPanel('admin');

        self::assertTrue($panel->hasPasswordReset());
        self::assertArrayHasKey('email_code', $panel->getMultiFactorAuthenticationProviders());
    }

    public function test_admin_password_reset_form_sends_a_filament_reset_link(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();

        $account = Account::create(['name' => 'Reset Account', 'slug' => 'reset-account']);
        $user = User::factory()->create(['account_id' => $account->id]);
        $user->assignRole('Account Owner');

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $user->email])
            ->call('request')
            ->assertHasNoFormErrors();

        Notification::assertSentTo(
            $user,
            FilamentResetPasswordNotification::class,
            fn (FilamentResetPasswordNotification $notification): bool => str_contains(
                $notification->url,
                '/admin/password-reset/reset',
            ),
        );
    }

    public function test_account_can_enable_and_disable_email_login_verification(): void
    {
        $account = Account::create([
            'name' => 'Secure Account',
            'slug' => 'secure-account',
            'settings' => ['admin_email_mfa_enabled' => false],
        ]);
        $user = User::factory()->create(['account_id' => $account->id]);

        self::assertInstanceOf(HasEmailAuthentication::class, $user);
        self::assertFalse($user->hasEmailAuthentication());

        $user->toggleEmailAuthentication(true);
        self::assertTrue($user->fresh()->hasEmailAuthentication());

        $user->fresh()->toggleEmailAuthentication(false);
        self::assertFalse($user->fresh()->hasEmailAuthentication());
    }
}
