<?php

use App\Http\Controllers\PublicLandingPageController;
use App\LandingPageStatus;
use App\Models\LandingPage;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\OrderInvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Schema::hasTable('landing_pages')) {
        return view('public.landing-pages.index', ['pages' => collect()]);
    }

    $pages = LandingPage::with(['translations', 'account'])
        ->where('status', LandingPageStatus::Published->value)
        ->latest('published_at')
        ->latest()
        ->get();

    return view('public.landing-pages.index', compact('pages'));
})->name('landing-pages.index');

Route::get('/landing', fn () => redirect()->route('landing-pages.index'));
Route::get('/landing/', fn () => redirect()->route('landing-pages.index'));

Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);

    session(['locale' => $locale]);

    return back();
})->name('locale.switch');

Route::get('/l/{slug}', [PublicLandingPageController::class, 'show'])->name('landing-pages.show');
Route::post('/l/{slug}/orders', [PublicLandingPageController::class, 'submit'])->name('landing-pages.submit');
Route::get('/orders/{order}/invoice', OrderInvoiceController::class)->middleware('signed')->name('orders.invoice');
