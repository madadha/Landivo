@php
    $isArabic = app()->getLocale() === 'ar';
    $pageTitle = trim($__env->yieldContent('title')) ?: ($account?->name ?? 'Landivo');
    $pageDescription = trim($__env->yieldContent('description')) ?: ($isArabic ? ($settings['seo_description_ar'] ?? $account?->description) : ($settings['seo_description_en'] ?? $settings['description_en'] ?? $account?->description));
    $socialLinks = collect($settings['social_links'] ?? [])->filter(fn ($link) => filled($link['url'] ?? null));
    $menuUrl = fn (string $url): string => str_starts_with($url, '/') ? url($url) : $url;
    $configuredMenu = function (string $key) use ($settings, $isArabic, $menuUrl) {
        return collect($settings[$key] ?? [])->filter(fn ($item) => ($item['is_active'] ?? true) && filled($item['url'] ?? null))->map(fn ($item) => [
            'label' => $item[$isArabic ? 'label_ar' : 'label_en'] ?? $item['label_ar'] ?? $item['label_en'] ?? '',
            'url' => $menuUrl($item['url']),
            'new_tab' => (bool) ($item['new_tab'] ?? false),
        ])->filter(fn ($item) => filled($item['label']));
    };
    $headerMenu = $configuredMenu('header_menu');
    $footerMenu = $configuredMenu('footer_menu');
    if ($headerMenu->isEmpty()) {
        $headerMenu = collect([['label' => $isArabic ? 'الرئيسية' : 'Home', 'url' => route('site.home'), 'new_tab' => false]])->concat($sitePages->where('show_in_header', true)->map(fn ($page) => ['label' => $page->translation()?->navigation_label ?: $page->translation()?->title ?: $page->slug, 'url' => route('site.pages.show', $page->slug), 'new_tab' => false]));
    }
    if ($footerMenu->isEmpty()) {
        $footerMenu = collect([['label' => $isArabic ? 'الرئيسية' : 'Home', 'url' => route('site.home'), 'new_tab' => false]])->concat($sitePages->where('show_in_footer', true)->map(fn ($page) => ['label' => $page->translation()?->navigation_label ?: $page->translation()?->title ?: $page->slug, 'url' => route('site.pages.show', $page->slug), 'new_tab' => false]));
    }
@endphp
<!doctype html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $pageTitle }} | {{ $account?->name ?? 'Landivo' }}</title>
    @if($pageDescription)<meta name="description" content="{{ $pageDescription }}">@endif
    @if($account?->favicon_path)<link rel="icon" href="{{ Storage::disk('public')->url($account->favicon_path) }}">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    @stack('styles')
    {!! $settings['seo_head_code'] ?? '' !!}
</head>
<body>
    <header class="web-header" data-header>
        <div class="web-container web-nav">
            <a class="web-brand" href="{{ route('site.home') }}">
                @if($account?->logo_path)<img src="{{ Storage::disk('public')->url($account->logo_path) }}" alt="{{ $account->name }}">@else<span class="web-brand-mark">L</span><strong>{{ $account?->name ?? 'Landivo' }}</strong>@endif
            </a>
            <button class="web-menu-button" type="button" aria-label="Menu" data-menu-button><span></span><span></span><span></span></button>
            <nav class="web-menu" data-menu>
                @foreach($headerMenu as $item)<a href="{{ $item['url'] }}" class="{{ rtrim(request()->url(),'/') === rtrim($item['url'],'/') ? 'active' : '' }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a>@endforeach
            </nav>
            <div class="web-nav-actions"><a class="web-language" href="{{ route('locale.switch', $isArabic ? 'en' : 'ar') }}">{{ $isArabic ? 'EN' : 'عربي' }}</a></div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="web-footer">
        <div class="web-container web-footer-grid">
            <div class="web-footer-brand">
                <a class="web-brand" href="{{ route('site.home') }}">@if($account?->logo_path)<img src="{{ Storage::disk('public')->url($account->logo_path) }}" alt="{{ $account->name }}">@else<strong>{{ $account?->name ?? 'Landivo' }}</strong>@endif</a>
                <p>{{ $isArabic ? ($account?->description ?: 'منتجات مختارة بعناية وتجربة شراء موثوقة.') : ($settings['description_en'] ?? $account?->description ?? 'Carefully selected products and a trusted shopping experience.') }}</p>
                @if($socialLinks->isNotEmpty())<div class="web-socials">@foreach($socialLinks as $social)<a class="web-social web-social-{{ $social['platform'] ?? 'website' }}" href="{{ $social['url'] }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] ?? $social['platform'] ?? 'Social' }}"></a>@endforeach</div>@endif
            </div>
            <div><h3>{{ $isArabic ? 'روابط سريعة' : 'Quick links' }}</h3><div class="web-footer-links">@foreach($footerMenu as $item)<a href="{{ $item['url'] }}" @if($item['new_tab']) target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a>@endforeach</div></div>
            <div><h3>{{ $isArabic ? 'تواصل معنا' : 'Contact us' }}</h3><div class="web-contact-list">@if(!empty($settings['contact_phone']))<a href="tel:{{ $settings['contact_phone'] }}">{{ $settings['contact_phone'] }}</a>@endif @if(!empty($settings['contact_email']))<a href="mailto:{{ $settings['contact_email'] }}">{{ $settings['contact_email'] }}</a>@endif @if(!empty($settings[$isArabic ? 'contact_address_ar' : 'contact_address_en']))<span>{{ $settings[$isArabic ? 'contact_address_ar' : 'contact_address_en'] }}</span>@endif</div></div>
            <div class="web-footer-cta"><h3>{{ $isArabic ? 'هل تحتاج مساعدة؟' : 'Need help?' }}</h3><p>{{ $isArabic ? 'فريقنا جاهز لمساعدتك واختيار العرض المناسب.' : 'Our team is ready to help you choose the right offer.' }}</p>@if(!empty($settings['contact_whatsapp']))<a href="{{ \App\Support\WhatsAppUrl::make($settings['contact_whatsapp'], '', $account?->phone_country_code) }}" target="_blank">{{ $isArabic ? 'تواصل عبر واتساب' : 'Chat on WhatsApp' }}</a>@endif</div>
        </div>
        <div class="web-container web-footer-bottom"><span>{{ $isArabic ? 'جميع الحقوق محفوظة' : 'All rights reserved' }} © {{ now()->year }} {{ $account?->name ?? 'Landivo' }}</span><span>{{ $isArabic ? 'تجربة رقمية موثوقة' : 'A trusted digital experience' }}</span></div>
    </footer>
    @if(!empty($settings['contact_whatsapp']))<a class="web-floating-wa" href="{{ \App\Support\WhatsAppUrl::make($settings['contact_whatsapp'], '', $account?->phone_country_code) }}" target="_blank" aria-label="WhatsApp"><span>◔</span></a>@endif
    <script>
        (()=>{const button=document.querySelector('[data-menu-button]'),menu=document.querySelector('[data-menu]');button?.addEventListener('click',()=>{button.classList.toggle('open');menu.classList.toggle('open')});document.querySelectorAll('[data-slider]').forEach(slider=>{const slides=[...slider.querySelectorAll('[data-slide]')],dots=[...slider.querySelectorAll('[data-dot]')];if(!slides.length)return;let index=0,timer;const show=i=>{index=(i+slides.length)%slides.length;slides.forEach((s,n)=>s.classList.toggle('active',n===index));dots.forEach((d,n)=>d.classList.toggle('active',n===index))};const start=()=>{clearInterval(timer);timer=setInterval(()=>show(index+1),5500)};dots.forEach((dot,i)=>dot.addEventListener('click',()=>{show(i);start()}));slider.querySelector('[data-next]')?.addEventListener('click',()=>{show(index+1);start()});slider.querySelector('[data-prev]')?.addEventListener('click',()=>{show(index-1);start()});show(0);start()});document.querySelectorAll('[data-product-gallery]').forEach(gallery=>{const main=gallery.querySelector('[data-main-image]');gallery.querySelectorAll('[data-gallery-thumb]').forEach(thumb=>thumb.addEventListener('click',()=>{if(main)main.src=thumb.dataset.image;gallery.querySelectorAll('[data-gallery-thumb]').forEach(item=>item.classList.remove('active'));thumb.classList.add('active')}))})})();
    </script>
    @stack('scripts')
    @include('components.marketing-popups', ['marketingPopups' => $marketingPopups ?? collect()])
</body>
</html>
