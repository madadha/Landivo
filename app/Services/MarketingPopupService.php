<?php

namespace App\Services;

use App\Models\MarketingPopup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MarketingPopupService
{
    /** @return Collection<int, MarketingPopup> */
    public function forPage(?int $accountId, string $path, string $locale): Collection
    {
        if (! $accountId || ! Schema::hasTable('marketing_popups')) {
            return collect();
        }

        return MarketingPopup::query()
            ->where('account_id', $accountId)
            ->currentlyActive()
            ->whereIn('locale', ['all', $locale])
            ->orderByDesc('priority')
            ->latest('id')
            ->limit(20)
            ->get()
            ->filter(fn (MarketingPopup $popup): bool => $popup->matchesPath($path))
            ->values();
    }
}
