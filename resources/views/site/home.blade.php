@extends('site.layouts.app')
@section('title', app()->getLocale()==='ar' ? ($settings['seo_title_ar'] ?? $settings['home_title_ar'] ?? 'الرئيسية') : ($settings['seo_title_en'] ?? $settings['home_title_en'] ?? 'Home'))
@section('description', app()->getLocale()==='ar' ? ($settings['seo_description_ar'] ?? $settings['home_description_ar'] ?? '') : ($settings['seo_description_en'] ?? $settings['home_description_en'] ?? ''))
@push('styles')<link rel="stylesheet" href="{{ asset('css/home-premium.css') }}">@endpush
@section('content')
@php
    $isArabic = app()->getLocale() === 'ar';
    $slides = collect($settings['home_slides'] ?? [])
        ->filter(fn ($slide) => ($slide['is_active'] ?? true))
        ->map(function (array $slide) use ($isArabic, $slideProducts) {
            $product = filled($slide['product_id'] ?? null)
                ? $slideProducts->get((int) $slide['product_id'])
                : null;
            $translation = $product?->translations->firstWhere('locale', $isArabic ? 'ar' : 'en')
                ?: $product?->translations->first();
            $productImage = $product?->localizedMedia($isArabic ? 'ar' : 'en')?->file_path
                ?: data_get($product?->metadata, $isArabic ? 'image_ar' : 'image_en')
                ?: $product?->primary_image_path;
            $image = $slide['image'] ?? $productImage;

            if (blank($image)) {
                return null;
            }

            $slide['image'] = $image;
            $slide['mobile_image'] = $slide['mobile_image'] ?? $image;
            $slide['resolved_title'] = $slide[$isArabic ? 'title_ar' : 'title_en'] ?? $translation?->name;
            $slide['resolved_description'] = $slide[$isArabic ? 'description_ar' : 'description_en'] ?? $translation?->description;
            $slide['resolved_button'] = $slide[$isArabic ? 'button_ar' : 'button_en'] ?? ($isArabic ? 'اكتشف الآن' : 'Explore now');
            $slide['resolved_url'] = $slide['url'] ?? ($product ? route('site.products.show', $product) : null);

            return $slide;
        })
        ->filter();

    if (($settings['home_slider_enabled'] ?? true) && $slides->isEmpty()) {
        $slides = $products->take(4)->map(function ($product) use ($isArabic) {
            $translation = $product->translations->firstWhere('locale', $isArabic ? 'ar' : 'en')
                ?: $product->translations->first();
            $image = $product->localizedMedia($isArabic ? 'ar' : 'en')?->file_path
                ?: data_get($product->metadata, $isArabic ? 'image_ar' : 'image_en')
                ?: $product->primary_image_path;

            return [
                'image' => $image,
                'mobile_image' => $image,
                'resolved_title' => $translation?->name,
                'resolved_description' => $translation?->description,
                'resolved_button' => $isArabic ? 'عرض المنتج' : 'View product',
                'resolved_url' => route('site.products.show', $product),
                'new_tab' => false,
            ];
        })->filter(fn ($slide) => filled($slide['image']));
    }

    if (! ($settings['home_slider_enabled'] ?? true)) {
        $slides = collect();
    }

    $sliderInterval = max(2, min(30, (int) ($settings['home_slider_interval'] ?? 6))) * 1000;
    $productColumns = max(2, min(4, (int) ($settings['home_products_desktop_columns'] ?? 4)));
    $productMobileColumns = max(1, min(2, (int) ($settings['home_products_mobile_columns'] ?? 2)));
    $productsTitle = $isArabic
        ? ($settings['home_products_title_ar'] ?? 'منتجاتنا')
        : ($settings['home_products_title_en'] ?? 'Our products');
    $productsDescription = $isArabic
        ? ($settings['home_products_description_ar'] ?? 'تعرّف على أحدث المنتجات والعروض المتوفرة.')
        : ($settings['home_products_description_en'] ?? 'Explore our latest products and available offers.');
    $productsKicker = $isArabic ? ($settings['home_products_kicker_ar'] ?? 'مختارة لك') : ($settings['home_products_kicker_en'] ?? 'Selected for you');
    $productsViewAll = $isArabic ? ($settings['home_products_view_all_ar'] ?? 'عرض الكل') : ($settings['home_products_view_all_en'] ?? 'View all');
    $features = collect($settings['home_features'] ?? [
        ['icon' => 'quality', 'title_ar' => 'جودة مختارة', 'title_en' => 'Selected quality', 'subtitle_ar' => 'منتجات نفخر بتقديمها', 'subtitle_en' => 'Products we are proud to offer', 'is_active' => true],
        ['icon' => 'delivery', 'title_ar' => 'توصيل سريع', 'title_en' => 'Fast delivery', 'subtitle_ar' => 'حتى باب منزلك', 'subtitle_en' => 'Straight to your doorstep', 'is_active' => true],
        ['icon' => 'support', 'title_ar' => 'خدمة متواصلة', 'title_en' => 'Continuous support', 'subtitle_ar' => 'نحن هنا لمساعدتك', 'subtitle_en' => 'We are here to help', 'is_active' => true],
        ['icon' => 'secure', 'title_ar' => 'شراء آمن', 'title_en' => 'Secure shopping', 'subtitle_ar' => 'تجربة واضحة وموثوقة', 'subtitle_en' => 'A clear and trusted experience', 'is_active' => true],
    ])->filter(fn ($feature) => $feature['is_active'] ?? true);
    $featureIcons = [
        'quality' => 'M12 3l2.3 4.7L19.5 9l-3.8 3.7.9 5.3-4.6-2.5L7.4 18l.9-5.3L4.5 9l5.2-1.3L12 3z',
        'delivery' => 'M3 6h11v10H3V6zm11 4h3l3 3v3h-6v-6zM7 19a2 2 0 110-4 2 2 0 010 4zm10 0a2 2 0 110-4 2 2 0 010 4z',
        'support' => 'M12 3a8 8 0 00-8 8v5a3 3 0 003 3h2v-7H6v-1a6 6 0 0112 0v1h-3v7h2a3 3 0 003-3v-5a8 8 0 00-8-8z',
        'secure' => 'M12 2l8 4v6c0 5-3.4 8.7-8 10-4.6-1.3-8-5-8-10V6l8-4zm-1.1 13.5l5-5-1.4-1.4-3.6 3.6-1.7-1.7-1.4 1.4 3.1 3.1z',
    ];
    $aboutImage = $settings['home_about_image'] ?? null;
    $aboutKicker = $isArabic ? ($settings['home_about_kicker_ar'] ?? 'قصتنا') : ($settings['home_about_kicker_en'] ?? 'Our story');
    $aboutTitle = $isArabic ? ($settings['home_about_title_ar'] ?? 'نختار الأفضل لنقدمه لك') : ($settings['home_about_title_en'] ?? 'We select the best for you');
    $aboutDescription = $isArabic
        ? ($settings['home_about_description_ar'] ?? $account?->company_details ?? $account?->description)
        : ($settings['home_about_description_en'] ?? $settings['company_details_en'] ?? $settings['description_en'] ?? $account?->company_details ?? $account?->description);
    $aboutButton = $isArabic ? ($settings['home_about_button_ar'] ?? 'اعرف المزيد عنا') : ($settings['home_about_button_en'] ?? 'Learn more about us');
    $campaignsKicker = $isArabic ? ($settings['home_campaigns_kicker_ar'] ?? 'عروض مباشرة') : ($settings['home_campaigns_kicker_en'] ?? 'Live offers');
    $campaignsTitle = $isArabic ? ($settings['home_campaigns_title_ar'] ?? 'اكتشف عروضنا') : ($settings['home_campaigns_title_en'] ?? 'Discover our offers');
    $campaignCardLabel = $isArabic ? ($settings['home_campaign_card_label_ar'] ?? 'عرض متاح') : ($settings['home_campaign_card_label_en'] ?? 'Available offer');
    $campaignButton = $isArabic ? ($settings['home_campaign_button_ar'] ?? 'مشاهدة العرض') : ($settings['home_campaign_button_en'] ?? 'View offer');
    $campaignColumns = max(2, min(3, (int) ($settings['home_campaigns_columns'] ?? 3)));
@endphp
@if($settings['home_slider_enabled'] ?? true)
<section class="web-hero" data-slider data-interval="{{ $sliderInterval }}">
    @if($slides->isNotEmpty())
        @foreach($slides as $slide)<article class="web-slide" data-slide style="--slide-image:url('{{ Storage::disk('public')->url($slide['image']) }}');--slide-mobile-image:url('{{ Storage::disk('public')->url($slide['mobile_image']) }}')"><div class="web-container web-slide-content"><div class="web-slide-copy"><span class="web-kicker"><i></i>{{ $isArabic?'منتجات أصلية مختارة بعناية':'Authentic products, carefully selected' }}</span><h1>{{ $slide['resolved_title'] ?? ($isArabic ? ($settings['home_title_ar'] ?? 'اكتشف منتجاتنا') : ($settings['home_title_en'] ?? 'Discover our products')) }}</h1><p>{{ $slide['resolved_description'] ?? ($isArabic ? ($settings['home_description_ar'] ?? '') : ($settings['home_description_en'] ?? '')) }}</p><div class="web-hero-actions">@if(!empty($slide['resolved_url']))<a class="web-hero-primary" href="{{ $slide['resolved_url'] }}" @if($slide['new_tab'] ?? false) target="_blank" rel="noopener" @endif>{{ $slide['resolved_button'] }} <span>←</span></a>@endif<a class="web-hero-secondary" href="{{ route('site.pages.show', optional($sitePages->firstWhere('template','contact'))->slug ?? 'contact-us') }}">{{ $isArabic?'تواصل معنا':'Contact us' }}</a></div></div><aside class="web-slide-brand-card">@if($account?->logo_path)<div class="web-hero-brand-logo"><img src="{{ Storage::disk('public')->url($account->logo_path) }}" alt="{{ $account->name }}"></div>@endif<div class="web-hero-brand-copy"><small>{{ $isArabic?'لماذا المواسم؟':'Why Almwasem?' }}</small><strong>{{ $isArabic?'جودة تشعر بها من أول تجربة':'Quality you notice from the first experience' }}</strong></div><div class="web-hero-mini-features"><span><b>✓</b>{{ $isArabic?'اختيار بعناية':'Carefully selected' }}</span><span><b>✓</b>{{ $isArabic?'توصيل سريع':'Fast delivery' }}</span><span><b>✓</b>{{ $isArabic?'دعم موثوق':'Trusted support' }}</span></div></aside></div></article>@endforeach
        <button class="web-slider-arrow prev" data-prev aria-label="Previous">‹</button><button class="web-slider-arrow next" data-next aria-label="Next">›</button><div class="web-slider-dots">@foreach($slides as $slide)<button data-dot aria-label="Slide {{ $loop->iteration }}"></button>@endforeach</div>
    @else
        <article class="web-slide active web-slide-fallback" data-slide><div class="web-container web-slide-content"><div class="web-slide-copy"><span class="web-kicker">{{ $account?->name??'Landivo' }}</span><h1>{{ $isArabic?($settings['home_title_ar']??'منتجات مختارة بعناية'):($settings['home_title_en']??'Carefully selected products') }}</h1><p>{{ $isArabic?($settings['home_description_ar']??'اكتشف مجموعتنا وعروضنا المميزة.'):($settings['home_description_en']??'Discover our collection and special offers.') }}</p><a href="/products">{{ $isArabic?'تصفح المنتجات':'Browse products' }} <span>←</span></a></div></div></article>
    @endif
</section>
@endif

@if(($settings['home_features_enabled'] ?? true) && $features->isNotEmpty())
<section class="web-trust"><div class="web-container web-trust-grid" style="--feature-count:{{ min(4, $features->count()) }}">
    @foreach($features as $feature)
        <div><i><svg viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $featureIcons[$feature['icon'] ?? 'quality'] ?? $featureIcons['quality'] }}"/></svg></i><span><strong>{{ $feature[$isArabic ? 'title_ar' : 'title_en'] ?? '' }}</strong><small>{{ $feature[$isArabic ? 'subtitle_ar' : 'subtitle_en'] ?? '' }}</small></span></div>
    @endforeach
</div></section>
@endif

@if(($settings['home_show_products']??true)&&$products->isNotEmpty())<section class="web-section web-products-section"><div class="web-container"><header class="web-section-head"><div><span>{{ $productsKicker }}</span><h2>{{ $productsTitle }}</h2><p>{{ $productsDescription }}</p></div><a href="{{ optional($sitePages->firstWhere('template','products'))->slug ? route('site.pages.show',$sitePages->firstWhere('template','products')->slug) : url('/products') }}">{{ $productsViewAll }} ←</a></header><div class="web-products-grid" style="--home-product-columns:{{ $productColumns }};--home-product-mobile-columns:{{ $productMobileColumns }}">@foreach($products as $product)@include('site.partials.product-card',['product'=>$product])@endforeach</div></div></section>@endif

@if($settings['home_about_enabled'] ?? true)
<section class="web-about-band"><div class="web-container web-about-grid"><div class="web-about-visual">@if($aboutImage)<img class="web-about-photo" src="{{ Storage::disk('public')->url($aboutImage) }}" alt="{{ $aboutTitle }}">@endif<div>@if(!$aboutImage && $account?->logo_path)<img src="{{ Storage::disk('public')->url($account->logo_path) }}" alt="{{ $account->name }}">@endif@if(!$aboutImage)<strong>{{ $account?->name }}</strong><span>{{ $isArabic?'ثقة تبدأ من الجودة':'Trust begins with quality' }}</span>@endif</div></div><div class="web-about-copy"><span>{{ $aboutKicker }}</span><h2>{{ $aboutTitle }}</h2><p>{{ $aboutDescription }}</p>@php($aboutUrl=$settings['home_about_url']??(($about=$sitePages->firstWhere('template','about'))?route('site.pages.show',$about->slug):null))@if($aboutUrl)<a href="{{ $aboutUrl }}" @if($settings['home_about_new_tab']??false) target="_blank" rel="noopener" @endif>{{ $aboutButton }} ←</a>@endif</div></div></section>
@endif

@if(($settings['home_show_campaigns']??true)&&$campaigns->isNotEmpty())<section class="web-section web-campaigns"><div class="web-container"><header class="web-section-head"><div><span>{{ $campaignsKicker }}</span><h2>{{ $campaignsTitle }}</h2></div></header><div class="web-campaign-grid" style="--campaign-columns:{{ $campaignColumns }}">@foreach($campaigns as $campaign)@php($tr=$campaign->translations->firstWhere('locale',app()->getLocale())?:$campaign->translations->first()) @php($image=data_get($campaign->settings,$isArabic?'product_image_ar':'product_image_en')?:data_get($campaign->settings,'product_image_ar'))<a href="{{ route('landing-pages.show',$campaign->slug) }}" class="web-campaign-card">@if($image)<img src="{{ Storage::disk('public')->url($image) }}" alt="{{ $tr?->title }}">@endif<div><span>{{ $campaignCardLabel }}</span><h3>{{ $tr?->title??$campaign->slug }}</h3><b>{{ $campaignButton }} ←</b></div></a>@endforeach</div></div></section>@endif
@endsection
