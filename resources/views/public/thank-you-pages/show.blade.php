<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    @php
        $isArabic = app()->getLocale() === 'ar';
        $title = $thankYouPage->localized('title') ?: ($isArabic ? 'شكرًا لك، تم استلام طلبك' : 'Thank you, your request was received');
        $message = $thankYouPage->localized('message') ?: ($isArabic ? 'سيتواصل معك فريقنا في أقرب وقت لتأكيد التفاصيل.' : 'Our team will contact you shortly to confirm the details.');
        $button = $thankYouPage->localized('button_text') ?: ($isArabic ? 'متابعة' : 'Continue');
        $image = $thankYouPage->{$isArabic ? 'image_ar' : 'image_en'} ?: $thankYouPage->image_ar ?: $thankYouPage->image_en;
        $countdown = max(0, (int) $thankYouPage->countdown_seconds);
        $font = match ($thankYouPage->font_family) {
            'tajawal' => 'Tajawal, Cairo, sans-serif',
            'inter' => 'Inter, Arial, sans-serif',
            'noto' => '"Noto Sans Arabic", Cairo, sans-serif',
            default => 'Cairo, Tahoma, sans-serif',
        };
    @endphp
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ file_exists(public_path('css/fonts.css')) ? filemtime(public_path('css/fonts.css')) : time() }}">
    {!! $thankYouPage->head_code !!}
    <style>
        :root{--page-bg:{{ $thankYouPage->background_color }};--card-bg:{{ $thankYouPage->card_color }};--title:{{ $thankYouPage->title_color }};--text:{{ $thankYouPage->text_color }};--button:{{ $thankYouPage->button_color }};--button-text:{{ $thankYouPage->button_text_color }};--radius:{{ $thankYouPage->border_radius }}px}
        *{box-sizing:border-box}html,body{min-height:100%}body{margin:0;background:var(--page-bg);font-family:{!! $font !!};color:var(--text)}
        .thank-page{min-height:100vh;display:grid;place-items:center;padding:clamp(20px,5vw,72px);position:relative;overflow:hidden}
        .thank-page:before,.thank-page:after{content:"";position:absolute;width:380px;height:380px;border-radius:50%;filter:blur(10px);opacity:.12;pointer-events:none}.thank-page:before{background:var(--button);inset:-180px auto auto -130px}.thank-page:after{background:var(--title);inset:auto -170px -210px auto}
        .thank-card{position:relative;z-index:1;width:min(760px,100%);padding:clamp(28px,6vw,64px);background:var(--card-bg);border:1px solid color-mix(in srgb,var(--text) 12%,transparent);border-radius:var(--radius);box-shadow:0 30px 90px rgba(15,23,42,.12);text-align:{{ $thankYouPage->alignment }}}
        .thank-card--celebration{background:linear-gradient(145deg,var(--card-bg),color-mix(in srgb,var(--button) 7%,var(--card-bg)))}.thank-card--minimal{box-shadow:none;border-width:2px}
        .thank-icon{width:76px;height:76px;margin:0 {{ $thankYouPage->alignment === 'center' ? 'auto' : '0' }} 24px;border-radius:50%;display:grid;place-items:center;background:color-mix(in srgb,var(--button) 12%,transparent);color:var(--button)}
        .thank-icon svg{width:38px;height:38px}h1{margin:0;color:var(--title);font-size:clamp(30px,5vw,52px);line-height:1.25;letter-spacing:-.03em}p{margin:18px 0 0;white-space:pre-line;font-size:clamp(16px,2.4vw,20px);line-height:1.9}
        .thank-image{display:block;max-width:100%;max-height:330px;object-fit:contain;margin:28px auto 0;border-radius:calc(var(--radius) * .6)}
        .thank-timer{display:inline-flex;align-items:center;gap:9px;margin-top:24px;padding:10px 16px;border-radius:999px;background:color-mix(in srgb,var(--button) 9%,transparent);color:var(--title);font-weight:800}.thank-timer strong{color:var(--button);font-size:22px}
        .thank-button{display:inline-flex;align-items:center;justify-content:center;min-width:190px;margin-top:28px;padding:15px 26px;border-radius:14px;background:var(--button);color:var(--button-text);font-weight:800;text-decoration:none;box-shadow:0 12px 28px color-mix(in srgb,var(--button) 25%,transparent);transition:.2s ease}.thank-button:hover{transform:translateY(-2px);filter:brightness(1.04)}
        .language-switch{position:absolute;z-index:2;top:20px;inset-inline-end:20px;display:flex;gap:8px}.language-switch a{padding:8px 12px;border:1px solid color-mix(in srgb,var(--text) 15%,transparent);border-radius:999px;background:var(--card-bg);color:var(--title);text-decoration:none;font-size:13px;font-weight:800}
        @media(max-width:520px){.thank-page{padding:74px 14px 24px}.thank-card{padding:32px 20px}.thank-icon{width:62px;height:62px}.thank-button{width:100%}}
        {!! $thankYouPage->custom_css !!}
    </style>
</head>
<body>
    {!! $thankYouPage->body_code !!}
    <main class="thank-page">
        <nav class="language-switch" aria-label="Language">
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'ar']) }}">العربية</a>
            <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}">English</a>
        </nav>
        <section class="thank-card thank-card--{{ $thankYouPage->template }}" data-countdown="{{ $countdown }}" data-redirect="{{ $thankYouPage->redirect_url }}">
            <div class="thank-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6 9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            @if($image)<img class="thank-image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="{{ $title }}">@endif
            @if($countdown > 0 && filled($thankYouPage->redirect_url))
                <div class="thank-timer"><span>{{ $isArabic ? 'سيتم تحويلك خلال' : 'Redirecting in' }}</span><strong data-timer>{{ $countdown }}</strong><span>{{ $isArabic ? 'ثانية' : 'seconds' }}</span></div>
            @endif
            @if(filled($thankYouPage->redirect_url))<div><a class="thank-button" href="{{ $thankYouPage->redirect_url }}">{{ $button }}</a></div>@endif
        </section>
    </main>
    <script>
        (()=>{const box=document.querySelector('[data-countdown]');if(!box)return;let seconds=Number(box.dataset.countdown||0);const redirect=box.dataset.redirect||'';const timer=box.querySelector('[data-timer]');if(seconds>0&&redirect){const tick=setInterval(()=>{seconds-=1;if(timer)timer.textContent=seconds;if(seconds<=0){clearInterval(tick);window.location.assign(redirect)}},1000)}})();
    </script>
</body>
</html>
