<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'ar' ? (data_get($landingPage->settings, 'thank_you_title_ar') ?: __('landivo.public.thank_you')) : (data_get($landingPage->settings, 'thank_you_title_en') ?: __('landivo.public.thank_you')) }}</title>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ filemtime(public_path('css/fonts.css')) }}">
    {!! data_get($landingPage->settings, 'thank_you_head_code') !!}
    <style>body{margin:0;background:#f7f8fc;font-family:Tahoma,sans-serif}.box{max-width:700px;margin:8vh auto;padding:42px 24px;text-align:center;background:#fff;border-radius:24px;box-shadow:0 18px 50px #17203312}.box img{max-width:100%;max-height:260px;object-fit:contain;border-radius:16px}h1{color:#16a34a}p{color:#667085;line-height:1.9;white-space:pre-line}.timer{font-size:34px;font-weight:900;color:#4f46e5;margin:20px}.order{color:#667085}a{display:inline-block;margin-top:18px;padding:14px 22px;background:#4f46e5;color:#fff;border-radius:10px;text-decoration:none}</style>
</head>
<body>
    {!! data_get($landingPage->settings, 'thank_you_body_code') !!}
    @php($isArabic = app()->getLocale() === 'ar')
    @php($title = data_get($landingPage->settings, $isArabic ? 'thank_you_title_ar' : 'thank_you_title_en') ?: __('landivo.public.thank_you'))
    @php($message = data_get($landingPage->settings, $isArabic ? 'thank_you_message_ar' : 'thank_you_message_en') ?: __('landivo.public.order_number'))
    @php($button = data_get($landingPage->settings, $isArabic ? 'thank_you_button_ar' : 'thank_you_button_en') ?: __('landivo.public.back'))
    @php($image = data_get($landingPage->settings, $isArabic ? 'thank_you_image_ar' : 'thank_you_image_en'))
    @php($isExternal = data_get($landingPage->settings, 'thank_you_mode') === 'external')
    @php($redirectUrl = data_get($landingPage->settings, 'thank_you_redirect_url'))
    @php($countdown = max(0, (int) data_get($landingPage->settings, 'thank_you_countdown', 0)))
    <main class="box" data-countdown="{{ $countdown }}" data-redirect="{{ $isExternal ? $redirectUrl : route('landing-pages.show', $landingPage->slug) }}">
        @if($image)<img src="{{ Storage::url($image) }}" alt="{{ $title }}">@endif
        <h1>{{ $title }}</h1><p>{{ $message }}</p>
        <p class="order">{{ __('landivo.public.order_number') }}: <strong>{{ $order->order_number }}</strong></p>
        @if($countdown > 0)<div class="timer" data-timer>{{ $countdown }}</div>@endif
        <a href="{{ $isExternal && $redirectUrl ? $redirectUrl : route('landing-pages.show', $landingPage->slug) }}">{{ $button }}</a>
    </main>
    <script>var box=document.querySelector('[data-countdown]'),seconds=Number(box.dataset.countdown||0),timer=box.querySelector('[data-timer]');if(seconds>0&&box.dataset.redirect){var tick=setInterval(function(){seconds--;if(timer)timer.textContent=seconds;if(seconds<=0){clearInterval(tick);window.location.href=box.dataset.redirect}},1000)}</script>
</body>
</html>
