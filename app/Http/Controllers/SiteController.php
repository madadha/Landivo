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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        if (! Schema::hasTable('accounts')) {
            return view('site.home', ['account' => null, 'settings' => [], 'sitePages' => collect(), 'products' => collect(), 'campaigns' => collect(), 'slideProducts' => collect()]);
        }

        $account = Account::query()->first();
        $settings = (array) ($account?->settings ?? []);
        $configuredProducts = collect($settings['home_products'] ?? [])
            ->filter(fn (array $item): bool => ($item['is_active'] ?? true) && filled($item['product_id'] ?? null));
        $configuredProductIds = $configuredProducts->pluck('product_id')->map(fn ($id): int => (int) $id)->unique()->values();
        $productLimit = max(1, min(24, (int) ($settings['home_products_limit'] ?? 8)));

        $productQuery = Product::query()
            ->with(['translations', 'media'])
            ->where('account_id', $account?->id)
            ->where('status', ProductStatus::Active->value)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($configuredProductIds->isNotEmpty()) {
            $productsById = (clone $productQuery)->whereKey($configuredProductIds)->get()->keyBy('id');
            $products = $configuredProductIds
                ->map(fn (int $id) => $productsById->get($id))
                ->filter()
                ->sortBy(fn (Product $product): string => sprintf('%010d-%010d', $product->sort_order, $product->id))
                ->take($productLimit)
                ->values();
        } else {
            $products = $productQuery->limit($productLimit)->get();
        }

        $slideProductIds = collect($settings['home_slides'] ?? [])
            ->filter(fn (array $slide): bool => ($slide['is_active'] ?? true) && filled($slide['product_id'] ?? null))
            ->pluck('product_id')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();
        $slideProducts = $slideProductIds->isEmpty()
            ? collect()
            : Product::query()
                ->with(['translations', 'media'])
                ->where('account_id', $account?->id)
                ->where('status', ProductStatus::Active->value)
                ->whereKey($slideProductIds)
                ->get()
                ->keyBy('id');
        $campaignLimit = max(1, min(12, (int) ($settings['home_campaigns_limit'] ?? 6)));
        $campaigns = LandingPage::query()->with('translations')->where('account_id', $account?->id)->where('status', LandingPageStatus::Published->value)->latest('published_at')->limit($campaignLimit)->get();

        return view('site.home', $this->shared($account) + compact('products', 'campaigns', 'slideProducts'));
    }

    public function show(SitePage $sitePage): View|JsonResponse
    {
        abort_unless($sitePage->status === 'published', 404);
        $sitePage->load('translations');
        $account = $sitePage->account;
        $settings = (array) ($account->settings ?? []);
        $productsLoadMode = in_array($settings['products_load_mode'] ?? null, ['pagination', 'infinite'], true)
            ? $settings['products_load_mode']
            : 'pagination';
        $productsPerPage = max(4, min(48, (int) ($settings['products_per_page'] ?? 12)));
        $products = $sitePage->template === 'products'
            ? Product::query()
                ->with(['translations', 'media'])
                ->where('account_id', $account->id)
                ->where('status', ProductStatus::Active->value)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->paginate($productsPerPage)
            : collect();

        if ($sitePage->template === 'products' && request()->boolean('products_partial')) {
            return response()->json([
                'html' => view('site.partials.product-cards', compact('products'))->render(),
                'next_url' => $products->nextPageUrl(),
            ]);
        }

        return view('site.page', $this->shared($account) + compact('sitePage', 'products', 'productsLoadMode'));
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
