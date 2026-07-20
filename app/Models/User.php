<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasEmailAuthentication
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = ['account_id', 'name', 'email', 'avatar_url', 'password'];

    public function getFilamentAvatarUrl(): ?string
    {
        return filled($this->avatar_url)
            ? Storage::disk('public')->url($this->avatar_url)
            : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->account_id !== null
            && $this->can('view dashboard');
    }

    public function hasEmailAuthentication(): bool
    {
        return (bool) data_get($this->account?->settings, 'admin_email_mfa_enabled', false);
    }

    public function toggleEmailAuthentication(bool $condition): void
    {
        $account = $this->account;

        if (! $account) {
            return;
        }

        $settings = $account->settings ?? [];
        data_set($settings, 'admin_email_mfa_enabled', $condition);

        $account->forceFill(['settings' => $settings])->save();
        $this->setRelation('account', $account->fresh());
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
