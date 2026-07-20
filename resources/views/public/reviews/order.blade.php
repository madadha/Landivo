<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ app()->getLocale() === 'ar' ? 'قيّم تجربتك' : 'Rate your experience' }}</title>
    <link rel="stylesheet" href="{{ asset('css/reviews.css') }}?v={{ filemtime(public_path('css/reviews.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/reviews-overrides.css') }}?v={{ filemtime(public_path('css/reviews-overrides.css')) }}">
</head>
<body class="review-request-page">
    @php($isArabic = app()->getLocale() === 'ar')
    @php($translation = $order->landingPage?->translations?->firstWhere('locale', app()->getLocale()) ?? $order->landingPage?->translations?->first())
    <main class="review-request-shell">
        <section class="review-request-card">
            @if($order->account?->logo_path)<img class="review-request-logo" src="{{ Storage::disk('public')->url($order->account->logo_path) }}" alt="{{ $order->account->name }}">@endif
            <span class="review-request-mark" aria-hidden="true">★</span>
            <p class="review-request-kicker">{{ $isArabic ? 'رأيك يصنع فرقًا' : 'Your feedback matters' }}</p>
            <h1>{{ $isArabic ? 'كيف كانت تجربتك؟' : 'How was your experience?' }}</h1>
            <p>{{ $isArabic ? 'ساعدنا على تحسين خدمتنا وشارك تجربتك مع العملاء.' : 'Help us improve and share your experience with other customers.' }}</p>
            @if($translation?->title)<div class="review-order-product">{{ $translation->title }}</div>@endif

            @if($order->review)
                <div class="review-success"><strong>{{ $isArabic ? 'شكرًا لك' : 'Thank you' }}</strong><span>{{ $isArabic ? 'تم تسجيل تقييمك لهذا الطلب.' : 'Your review for this order has been recorded.' }}</span></div>
            @elseif(session('review_success'))
                <div class="review-success"><strong>{{ $isArabic ? 'تم بنجاح' : 'Success' }}</strong><span>{{ session('review_success') }}</span></div>
            @else
                <form method="POST" action="{{ url()->full() }}" class="review-public-form">
                    @csrf
                    <input type="hidden" name="name" value="{{ $order->customer?->name }}">
                    <fieldset class="rating-picker">
                        <legend>{{ $isArabic ? 'تقييمك' : 'Your rating' }}</legend>
                        <div class="rating-stars" dir="ltr">
                            @for($rating = 5; $rating >= 1; $rating--)
                                <input id="rating-{{ $rating }}" type="radio" name="rating" value="{{ $rating }}" @checked(old('rating', 5) == $rating) required>
                                <label for="rating-{{ $rating }}" title="{{ $rating }}/5">★</label>
                            @endfor
                        </div>
                    </fieldset>
                    <label>{{ $isArabic ? 'اكتب رأيك (اختياري)' : 'Write your review (optional)' }}
                        <textarea name="content" rows="4" maxlength="2000" placeholder="{{ $isArabic ? 'أخبرنا عن جودة المنتج والتوصيل والخدمة إن رغبت...' : 'Tell us about the product, delivery, and service if you wish...' }}">{{ old('content') }}</textarea>
                    </label>
                    @if($errors->any())<div class="review-error">{{ $errors->first() }}</div>@endif
                    <button type="submit">{{ $isArabic ? 'إرسال التقييم' : 'Submit review' }}</button>
                </form>
            @endif
            <small class="review-order-number">{{ $isArabic ? 'رقم الطلب' : 'Order' }}: {{ $order->order_number }}</small>
        </section>
    </main>
</body>
</html>
