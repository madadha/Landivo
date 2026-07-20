<?php

namespace App\Http\Controllers;

use App\LandingPageStatus;
use App\Models\Account;
use App\Models\ContactMessage;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\SitePage;
use App\ProductStatus;
use App\Services\MarketingPopupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        if (! Schema::hasTable('accounts')) {
            return view('site.home', ['account' => null, 'settings' => [], 'sitePages' => collect(), 'products' => collect(), 'campaigns' => collect()]);
        }

        $account = Account::query()->first();
        $products = Product::query()->with(['translations', 'media'])->where('account_id', $account?->id)->where('status', ProductStatus::Active->value)->latest()->limit(12)->get();
        $campaigns = LandingPage::query()->with('translations')->where('account_id', $account?->id)->where('status', LandingPageStatus::Published->value)->latest('published_at')->limit(6)->get();

        return view('site.home', $this->shared($account) + compact('products', 'campaigns'));
    }

    public function show(SitePage $sitePage): View
    {
        abort_unless($sitePage->status === 'published', 404);
        $sitePage->load('translations');
        $account = $sitePage->account;
        $products = $sitePage->template === 'products'
            ? Product::query()->with(['translations', 'media'])->where('account_id', $account->id)->where('status', ProductStatus::Active->value)->latest()->paginate(12)
            : collect();

        return view('site.page', $this->shared($account) + compact('sitePage', 'products'));
    }

    public function product(Product $product): View
    {
        abort_unless($product->status === ProductStatus::Active, 404);

        $product->load([
            'translations',
            'media' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            'variants' => fn ($query) => $query->where('is_active', true)->with('translations')->orderBy('sort_order'),
            'reviews' => fn ($query) => $query->where('is_approved', true)->latest(),
        ]);
        $account = $product->account;
        $relatedProducts = Product::query()
            ->with(['translations', 'media'])
            ->where('account_id', $account->id)
            ->where('status', ProductStatus::Active->value)
            ->whereKeyNot($product->getKey())
            ->latest()
            ->limit(4)
            ->get();

        return view('site.product', $this->shared($account) + compact('product', 'relatedProducts'));
    }

    public function contact(Request $request, SitePage $sitePage): RedirectResponse
    {
        abort_unless($sitePage->status === 'published' && $sitePage->template === 'contact', 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
        ]);
        $data['account_id'] = $sitePage->account_id;
        $data['ip_address'] = $request->ip();
        ContactMessage::create($data);

        return back()->with('contact_success', app()->getLocale() === 'ar' ? 'شكرًا لك، تم إرسال رسالتك بنجاح.' : 'Thank you. Your message has been sent successfully.');
    }

    private function shared(?Account $account): array
    {
        $sitePages = Schema::hasTable('site_pages')
            ? SitePage::query()->with('translations')->where('account_id', $account?->id)->where('status', 'published')->orderBy('sort_order')->get()
            : collect();

        return [
            'account' => $account,
            'settings' => (array) ($account?->settings ?? []),
            'sitePages' => $sitePages,
            'marketingPopups' => app(MarketingPopupService::class)->forPage(
                $account?->id,
                request()->path() === '/' ? '/' : request()->path(),
                app()->getLocale(),
            ),
        ];
    }
}
