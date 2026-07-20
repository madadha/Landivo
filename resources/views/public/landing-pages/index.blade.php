@php($account = $pages->first()?->account)
@php($settings = (array) ($account?->settings ?? []))
@php($isArabic = app()->getLocale() === 'ar')
@php($socialLinks = collect($settings['social_links'] ?? [])->filter(fn ($link) => filled($link['url'] ?? null)))
<!doctype html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    @php($seoTitle = $isArabic ? ($settings['seo_title_ar'] ?? $settings['home_title_ar'] ?? 'صفحات الهبوط') : ($settings['seo_title_en'] ?? $settings['home_title_en'] ?? 'Landing Pages'))
    @php($seoDescription = $isArabic ? ($settings['seo_description_ar'] ?? $settings['home_description_ar'] ?? null) : ($settings['seo_description_en'] ?? $settings['home_description_en'] ?? null))
    <title>{{ $seoTitle }} | {{ $account?->name ?? 'Landivo' }}</title>
    @if($seoDescription)<meta name="description" content="{{ $seoDescription }}">@endif
    @if(!empty($settings['seo_keywords']))<meta name="keywords" content="{{ $settings['seo_keywords'] }}">@endif
    @if(!empty($settings['google_site_verification']))<meta name="google-site-verification" content="{{ $settings['google_site_verification'] }}">@endif
    @if(array_key_exists('seo_indexable', $settings) && ! $settings['seo_indexable'])<meta name="robots" content="noindex,nofollow">@endif
    <link rel="canonical" href="{{ $settings['seo_canonical_url'] ?? url('/') }}">
    @if(!empty($settings['google_analytics_id']))<script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($settings['google_analytics_id']) }}"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','{{ addslashes($settings['google_analytics_id']) }}');</script>@endif
    {!! $settings['seo_head_code'] ?? '' !!}
    @if($account?->favicon_path)<link rel="icon" href="{{ Storage::disk('public')->url($account->favicon_path) }}">@endif
    <link rel="stylesheet" href="{{ asset('css/landing-index.css') }}"><link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
</head>
<body>
    <header class="site-header"><div class="header-inner"><a class="brand" href="{{ route('landing-pages.index') }}">@if($account?->logo_path)<img src="{{ Storage::disk('public')->url($account->logo_path) }}" alt="{{ $account->name }}">@else<span class="brand-mark">L</span>@endif<span>{{ $account?->name ?? 'Landivo' }}</span></a><a class="language-switch" href="{{ route('locale.switch', $isArabic ? 'en' : 'ar') }}">{{ $isArabic ? 'English' : 'العربية' }}</a></div></header>
    <main class="container">
        <section class="intro"><span class="eyebrow">{{ $isArabic ? 'اكتشف عروضنا' : 'Discover our offers' }}</span><h1>{{ $isArabic ? ($settings['home_title_ar'] ?? 'صفحات الهبوط') : ($settings['home_title_en'] ?? 'Landing Pages') }}</h1><p>{{ $isArabic ? ($settings['home_description_ar'] ?? 'اختر العرض المناسب لك وتعرّف على التفاصيل.') : ($settings['home_description_en'] ?? 'Choose the offer that suits you and explore the details.') }}</p></section>
        @if($pages->isEmpty())
            <section class="empty-state"><div class="empty-icon">⌁</div><h2>{{ $isArabic ? 'لا توجد صفحات منشورة حالياً' : 'No published pages yet' }}</h2><p>{{ $isArabic ? 'ستظهر صفحات الهبوط هنا بعد نشرها من لوحة التحكم.' : 'Published landing pages will appear here.' }}</p></section>
        @else
            <section class="landing-grid">@foreach($pages as $page) @php($translation = $page->translations->firstWhere('locale', app()->getLocale()) ?? $page->translations->first()) @php($image = data_get($page->settings, $isArabic ? 'product_image_ar' : 'product_image_en') ?: data_get($page->settings, 'product_image_ar') ?: data_get($page->settings, 'product_image_en'))<article class="landing-card"><div class="card-media">@if($image)<img src="{{ Storage::disk('public')->url($image) }}" alt="{{ $translation?->title ?? $page->slug }}">@else<div class="media-placeholder"><span>Landivo</span></div>@endif<span class="status-badge">{{ $isArabic ? 'متاح الآن' : 'Available now' }}</span></div><div class="card-body"><span class="card-slug">/{{ $page->slug }}</span><h2>{{ $translation?->title ?? $page->slug }}</h2>@if($translation?->description)<p>{{ Str::limit($translation->description, 130) }}</p>@endif<a class="card-action" href="{{ route('landing-pages.show', $page->slug) }}">{{ $isArabic ? 'عرض الصفحة' : 'View page' }}<span>←</span></a></div></article>@endforeach</section>
        @endif
        @if($socialLinks->isNotEmpty())<section class="home-social"><h2>{{ $isArabic ? 'تواصل معنا' : 'Connect with us' }}</h2><div class="home-social-grid">@foreach($socialLinks as $link)<a href="{{ $link['url'] }}" target="_blank" rel="noopener"><span class="social-symbol">{{ strtoupper(substr((string) ($link['platform'] ?? 'S'), 0, 1)) }}</span><span>{{ $link['label'] }}</span></a>@endforeach</div></section>@endif
    </main>
    <footer class="site-footer">@php($footer = $isArabic ? ($settings['home_footer_ar'] ?? null) : ($settings['home_footer_en'] ?? null)) @if($footer)<div class="home-footer-rich">{!! $footer !!}</div>@else<p>{{ $account?->company_details ?: ($account?->description ?: ($isArabic ? 'حلول احترافية لإنشاء وإدارة صفحات الهبوط.' : 'Professional landing page solutions.')) }}</p>@endif<small>{{ $isArabic ? 'جميع الحقوق محفوظة' : 'All rights reserved' }} © {{ now()->year }} {{ $account?->name ?? 'Landivo' }}</small></footer>
</body>
</html>
