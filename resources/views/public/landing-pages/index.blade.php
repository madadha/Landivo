@php($account = $pages->first()?->account)
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ app()->getLocale() === 'ar' ? 'صفحات الهبوط | Landivo' : 'Landing Pages | Landivo' }}</title>
    @if($account?->favicon_path)<link rel="icon" href="{{ Storage::disk('public')->url($account->favicon_path) }}">@endif
    <link rel="stylesheet" href="{{ asset('css/landing-index.css') }}">
</head>
<body>
    @php($account = $pages->first()?->account)
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="{{ route('landing-pages.index') }}">
                @if($account?->logo_path)
                    <img src="{{ Storage::disk('public')->url($account->logo_path) }}" alt="{{ $account->name }}">
                @else
                    <span class="brand-mark">L</span>
                @endif
                <span>{{ $account?->name ?? 'Landivo' }}</span>
            </a>
            <a class="language-switch" href="{{ route('locale.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}">
                {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}
            </a>
        </div>
    </header>

    <main class="container">
        <section class="intro">
            <span class="eyebrow">{{ app()->getLocale() === 'ar' ? 'اكتشف عروضنا' : 'Discover our offers' }}</span>
            <h1>{{ app()->getLocale() === 'ar' ? 'صفحات الهبوط' : 'Landing Pages' }}</h1>
            <p>{{ app()->getLocale() === 'ar' ? 'اختر العرض المناسب لك وتعرّف على التفاصيل.' : 'Choose the offer that suits you and explore the details.' }}</p>
        </section>

        @if($pages->isEmpty())
            <section class="empty-state">
                <div class="empty-icon">⌁</div>
                <h2>{{ app()->getLocale() === 'ar' ? 'لا توجد صفحات منشورة حالياً' : 'No published pages yet' }}</h2>
                <p>{{ app()->getLocale() === 'ar' ? 'ستظهر صفحات الهبوط هنا بعد نشرها من لوحة التحكم.' : 'Published landing pages will appear here.' }}</p>
            </section>
        @else
            <section class="landing-grid">
                @foreach($pages as $page)
                    @php($translation = $page->translations->firstWhere('locale', app()->getLocale()) ?? $page->translations->first())
                    @php($image = data_get($page->settings, app()->getLocale() === 'ar' ? 'product_image_ar' : 'product_image_en') ?: data_get($page->settings, 'product_image_ar') ?: data_get($page->settings, 'product_image_en'))
                    <article class="landing-card">
                        <div class="card-media">
                            @if($image)
                                <img src="{{ Storage::disk('public')->url($image) }}" alt="{{ $translation?->title ?? $page->slug }}">
                            @else
                                <div class="media-placeholder"><span>Landivo</span></div>
                            @endif
                            <span class="status-badge">{{ app()->getLocale() === 'ar' ? 'متاح الآن' : 'Available now' }}</span>
                        </div>
                        <div class="card-body">
                            <span class="card-slug">/{{ $page->slug }}</span>
                            <h2>{{ $translation?->title ?? $page->slug }}</h2>
                            @if($translation?->description)<p>{{ Str::limit($translation->description, 130) }}</p>@endif
                            <a class="card-action" href="{{ route('landing-pages.show', $page->slug) }}">
                                {{ app()->getLocale() === 'ar' ? 'عرض الصفحة' : 'View page' }}
                                <span aria-hidden="true">←</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </section>
        @endif
    </main>

    <footer class="site-footer">
        <p>{{ $account?->company_details ?: ($account?->description ?: (app()->getLocale() === 'ar' ? 'حلول احترافية لإنشاء وإدارة صفحات الهبوط.' : 'Professional landing page solutions.')) }}</p>
        <small>{{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة' : 'All rights reserved' }} © {{ now()->year }} {{ $account?->name ?? 'Landivo' }}</small>
    </footer>
</body>
</html>
