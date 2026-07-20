<?php

namespace App\Http\Controllers;

use App\LandingPageStatus;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function storeLandingPage(Request $request, string $slug): RedirectResponse
    {
        $landingPage = LandingPage::query()
            ->where('slug', $slug)
            ->where('status', LandingPageStatus::Published->value)
            ->firstOrFail();

        abort_unless((bool) data_get($landingPage->settings, 'reviews_enabled', false), 404);
        abort_unless((bool) data_get($landingPage->settings, 'reviews_allow_submission', true), 403);

        $data = $this->validateReview($request);

        Review::create([
            'account_id' => $landingPage->account_id,
            'landing_page_id' => $landingPage->id,
            'product_id' => $landingPage->product_id,
            'name' => $data['name'],
            'rating' => $data['rating'],
            'content' => $data['content'] ?? '',
            'source' => 'landing_page',
            'is_verified_purchase' => false,
            'is_approved' => false,
            'is_featured' => false,
        ]);

        return back()->with('review_success', app()->getLocale() === 'ar'
            ? 'شكرًا لك، تم إرسال تقييمك وسيظهر بعد مراجعته.'
            : 'Thank you. Your review was submitted and will appear after approval.');
    }

    public function orderForm(Order $order): View
    {
        $order->load(['customer', 'landingPage.translations', 'items.product.translations', 'review']);

        return view('public.reviews.order', compact('order'));
    }

    public function storeOrder(Request $request, Order $order): RedirectResponse
    {
        abort_if($order->review()->exists(), 409, app()->getLocale() === 'ar' ? 'تم تقييم هذا الطلب مسبقًا.' : 'This order was already reviewed.');

        $data = $this->validateReview($request, false);

        Review::create([
            'account_id' => $order->account_id,
            'landing_page_id' => $order->landing_page_id,
            'product_id' => $order->items()->value('product_id'),
            'order_id' => $order->id,
            'name' => $order->customer?->name ?: $data['name'],
            'customer_email' => $order->customer?->email,
            'customer_phone' => $order->customer?->phone,
            'rating' => $data['rating'],
            'content' => $data['content'] ?? '',
            'source' => 'order_link',
            'is_verified_purchase' => true,
            'is_approved' => false,
            'is_featured' => false,
        ]);

        return back()->with('review_success', app()->getLocale() === 'ar'
            ? 'شكرًا لتقييمك. تم استلام رأيك بنجاح.'
            : 'Thank you. Your verified review was received successfully.');
    }

    private function validateReview(Request $request, bool $nameRequired = true): array
    {
        return $request->validate([
            'name' => [$nameRequired ? 'required' : 'nullable', 'string', 'max:150'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
