<?php

namespace App\Http\Controllers;

use App\Models\ThankYouPage;
use Illuminate\View\View;

class PublicThankYouPageController extends Controller
{
    public function __invoke(ThankYouPage $thankYouPage): View
    {
        abort_unless($thankYouPage->is_active, 404);

        if (in_array(request('lang'), ['ar', 'en'], true)) {
            app()->setLocale(request('lang'));
        }

        return view('public.thank-you-pages.show', compact('thankYouPage'));
    }
}
