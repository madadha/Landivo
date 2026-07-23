<?php

use App\Http\Controllers\DataTransferController;
use App\Http\Controllers\MarketingPopupEventController;
use App\Http\Controllers\OrderBatchInvoiceController;
use App\Http\Controllers\OrderInvoiceController;
use App\Http\Controllers\PublicLandingPageController;
use App\Http\Controllers\PublicThankYouPageController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SiteController;
use App\LandingPageStatus;
use App\Models\Account;
use App\Models\LandingPage;
use App\Models\Product;
use App\Models\SitePage;
use App\ProductStatus;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', [SiteController::class, 'home'])->name('site.home');

Route::get('/landing', fn () => redirect()->route('site.home'));
Route::get('/landing/', fn () => redirect()->route('site.home'));

Route::get('/sitemap.xml', function () {
    if (! Schema::hasTable('landing_pages')) {
        return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><url><loc>'.e(url('/')).'</loc></url></urlset>', 200, ['Content-Type' => 'application/xml']);
    }

    $pages = LandingPage::query()->where('status', LandingPageStatus::Published->value)->get(['slug', 'updated_at']);
    $sitePages = Schema::hasTable('site_pages') ? SitePage::query()->where('status', 'published')->get(['slug', 'updated_at']) : collect();
    $products = Schema::hasTable('products') ? Product::query()->where('status', ProductStatus::Active->value)->get(['id', 'updated_at']) : collect();
    $urls = collect([['loc' => url('/'), 'lastmod' => now()->toAtomString()]])
        ->merge($sitePages->map(fn (SitePage $page): array => ['loc' => route('site.pages.show', $page->slug), 'lastmod' => $page->updated_at?->toAtomString()]))
        ->merge($products->map(fn (Product $product): array => ['loc' => route('site.products.show', $product), 'lastmod' => $product->updated_at?->toAtomString()]))
        ->merge($pages->map(fn (LandingPage $page): array => ['loc' => route('landing-pages.show', $page->slug), 'lastmod' => $page->updated_at?->toAtomString()]));
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $item) {
        $xml .= '<url><loc>'.e($item['loc']).'</loc>'.($item['lastmod'] ? '<lastmod>'.e($item['lastmod']).'</lastmod>' : '').'</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::get('/robots.txt', function () {
    $account = Schema::hasTable('accounts') ? Account::query()->first(['settings']) : null;
    $settings = (array) ($account?->settings ?? []);
    $indexable = ! array_key_exists('seo_indexable', $settings) || (bool) $settings['seo_indexable'];

    return response("User-agent: *\n".($indexable ? "Allow: /\n" : "Disallow: /\n")."Disallow: /admin\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);

    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

Route::post('/marketing-popups/{marketingPopup}/event', MarketingPopupEventController::class)
    ->middleware('throttle:60,1')
    ->name('marketing-popups.event');

Route::get('/l/{slug}', [PublicLandingPageController::class, 'show'])->name('landing-pages.show');
Route::get('/thank-you/{thankYouPage:slug}', PublicThankYouPageController::class)->name('thank-you-pages.show');
Route::get('/l/{slug}/viewers', [PublicLandingPageController::class, 'viewers'])->name('landing-pages.viewers');
Route::post('/l/{slug}/orders', [PublicLandingPageController::class, 'submit'])->name('landing-pages.submit');
Route::post('/l/{slug}/reviews', [ReviewController::class, 'storeLandingPage'])->middleware('throttle:5,10')->name('landing-pages.reviews.store');
Route::get('/reviews/order/{order}', [ReviewController::class, 'orderForm'])->middleware('signed')->name('reviews.order.form');
Route::post('/reviews/order/{order}', [ReviewController::class, 'storeOrder'])->middleware(['signed', 'throttle:5,10'])->name('reviews.order.store');
Route::get('/orders/{order}/invoice', OrderInvoiceController::class)->middleware('signed')->name('orders.invoice');
Route::middleware('auth')->group(function (): void {
    Route::get('/data-transfers/{dataTransfer}/download', [DataTransferController::class, 'download'])->name('data-transfers.download');
    Route::get('/data-transfers/templates/{entity}', [DataTransferController::class, 'template'])->name('data-transfers.template');
    Route::post('/reports/order-status/invoices', OrderBatchInvoiceController::class)->name('reports.order-status.invoices');
    Route::get('/reports/orders/export', [ReportExportController::class, 'orders'])->name('reports.orders.export');
    Route::get('/reports/reviews/export', [ReportExportController::class, 'reviews'])->name('reports.reviews.export');
});
Route::get('/products/{product}', [SiteController::class, 'product'])->name('site.products.show');
Route::post('/{sitePage:slug}/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::get('/{sitePage:slug}', [SiteController::class, 'show'])->where('sitePage', '^(?!admin$|api$|storage$|build$|css$|js$|images$|icons$|locale$|landing$|orders$)[A-Za-z0-9_-]+$')->name('site.pages.show');
