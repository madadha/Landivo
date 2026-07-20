<?php

namespace App\Http\Controllers;

use App\Models\MarketingPopup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingPopupEventController extends Controller
{
    public function __invoke(Request $request, MarketingPopup $marketingPopup): JsonResponse
    {
        $data = $request->validate(['event' => ['required', 'in:impression,click']]);
        $marketingPopup->increment($data['event'] === 'click' ? 'clicks_count' : 'impressions_count');

        return response()->json(['ok' => true]);
    }
}
