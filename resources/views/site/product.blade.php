@php
    $isArabic = app()->getLocale() === 'ar';
    $translation = $product->translations->firstWhere('locale', app()->getLocale()) ?: $product->translations->first();
    $gallery = $product->media->filter(fn ($media) => $media->media_type === 'image' && filled($media->file_path) && (blank($media->locale) || $media->locale === app()->getLocale()));
    if ($gallery->isEmpty()) $gallery = $product->media->filter(fn ($media) => $media->media_type === 'image' && filled($media->file_path));
    $fallbackImage = data_get($product->metadata, $isArabic ? 'image_ar' : 'image_en') ?: $product->primary_image_path;
    $mainImage = $gallery->first()?->file_path ?: $fallbackImage;
    $discount = $product->compare_at_price && $product->compare_at_price > $product->price ? round((1 - $product->price / $product->compare_at_price) * 100) : 0;
    $badgeLabel = $product->badgeLabel();
    $badgeStyle = in_array($product->badge_style, ['pill', 'ribbon', 'outline'], true) ? $product->badge_style : 'pill';
    $reviews = $product->reviews;
    $rating = $reviews->isNotEmpty() ? round((float) $reviews->avg('rating'), 1) : null;
    $whatsAppMessage = $isArabic ? 'مرحبًا، أريد الاستفسار عن '.$translation?->name : 'Hello, I would like to ask about '.$translation?->name;
@endphp
@extends('site.layouts.app')
@section('title', $translation?->name ?? $product->sku)
@section('description', Str::limit(strip_tags($translation?->description ?? ''), 155))
@section('content')
<section class="web-product-page">
    <div class="web-container">
        <nav class="web-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('site.home') }}">{{ $isArabic?'الرئيسية':'Home' }}</a><span>›</span>@if($productsPage=$sitePages->firstWhere('template','products'))<a href="{{ route('site.pages.show',$productsPage->slug) }}">{{ $isArabic?'المنتجات':'Products' }}</a><span>›</span>@endif<strong>{{ $translation?->name ?? $product->sku }}</strong></nav>
        <div class="web-product-detail">
            <div class="web-product-gallery" data-product-gallery>
                <div class="web-product-main-image">@if($mainImage)<img src="{{ Storage::disk('public')->url($mainImage) }}" alt="{{ $translation?->name ?? $product->sku }}" data-main-image>@else<span>{{ $account?->name ?? 'Landivo' }}</span>@endif @if($product->hasVisibleBadge())<span class="product-custom-badge product-custom-badge--{{ $badgeStyle }}" style="--product-badge-bg:{{ $product->badge_background_color ?: '#d97706' }};--product-badge-text:{{ $product->badge_text_color ?: '#ffffff' }}">{{ $badgeLabel }}</span>@endif @if($discount)<b>{{ $isArabic?'وفر':'Save' }} {{ $discount }}%</b>@endif</div>
                @if($gallery->count()>1)<div class="web-product-thumbs">@foreach($gallery as $media)<button type="button" class="{{ $loop->first?'active':'' }}" data-gallery-thumb data-image="{{ Storage::disk('public')->url($media->file_path) }}"><img src="{{ Storage::disk('public')->url($media->file_path) }}" alt="{{ $media->alt_text ?: $translation?->name }}"></button>@endforeach</div>@endif
            </div>
            <article class="web-product-summary">
                <small>{{ $product->sku }}</small>
                <h1>{{ $translation?->name ?? $product->sku }}</h1>
                @if($rating)<div class="web-rating"><span>★</span><strong>{{ number_format($rating,1) }}</strong><em>({{ $reviews->count() }} {{ $isArabic?'تقييم':'reviews' }})</em></div>@endif
                @if($translation?->description)<div class="web-product-description">{!! $translation->description !!}</div>@endif
                <div class="web-detail-price"><strong>{{ number_format((float)$product->price,2) }} <small>{{ $product->currency }}</small></strong>@if($discount)<del>{{ number_format((float)$product->compare_at_price,2) }} {{ $product->currency }}</del><span>-{{ $discount }}%</span>@endif</div>
                <div class="web-stock {{ $product->quantity > 0 ? 'in-stock' : 'out-of-stock' }}"><i></i>{{ $product->quantity > 0 ? ($isArabic?'متوفر في المخزون':'In stock') : ($isArabic?'غير متوفر حاليًا':'Currently unavailable') }}@if($product->quantity>0)<small>{{ $product->quantity }} {{ $isArabic?'قطعة متاحة':'available' }}</small>@endif</div>
                @if(!empty($product->options))<div class="web-product-options">@foreach($product->options as $option)<div><strong>{{ $option[$isArabic?'name_ar':'name_en'] ?? $option['name_ar'] ?? $option['name_en'] ?? '' }}</strong><div>@foreach(($option['values']??[]) as $value)<span>{{ $value[$isArabic?'label_ar':'label_en'] ?? $value['label_ar'] ?? $value['label_en'] ?? $value['code'] ?? '' }}</span>@endforeach</div></div>@endforeach</div>@endif
                @if($product->variants->isNotEmpty())<div class="web-variants"><h2>{{ $isArabic?'الخيارات المتوفرة':'Available variants' }}</h2>@foreach($product->variants as $variant)<div><span><strong>{{ $variant->translation()?->name ?: $variant->sku }}</strong>@if($variant->translation()?->description)<small>{{ $variant->translation()?->description }}</small>@endif</span><b>{{ number_format((float)($variant->price ?: $product->price),2) }} {{ $product->currency }}</b></div>@endforeach</div>@endif
                @if(!empty($settings['contact_whatsapp']))<a class="web-product-whatsapp" href="{{ \App\Support\WhatsAppUrl::make($settings['contact_whatsapp'], $whatsAppMessage, $account?->phone_country_code) }}" target="_blank" rel="noopener"><span>◉</span>{{ $isArabic?'اسأل عن المنتج عبر واتساب':'Ask about this product on WhatsApp' }}</a>@endif
                <ul class="web-product-assurances"><li>{{ $isArabic?'معلومات واضحة وشفافة':'Clear product information' }}</li><li>{{ $isArabic?'دعم مباشر قبل وبعد الطلب':'Support before and after ordering' }}</li><li>{{ $isArabic?'تجربة شراء آمنة وموثوقة':'A safe and trusted experience' }}</li></ul>
            </article>
        </div>
        @if($translation?->details)<section class="web-product-info"><header><span>{{ $isArabic?'تفاصيل موثوقة':'Product information' }}</span><h2>{{ $isArabic?'تفاصيل المنتج':'Product details' }}</h2></header><div class="web-rich-content">{!! $translation->details !!}</div></section>@endif
        @if($reviews->isNotEmpty())<section class="web-product-reviews"><header><span>{{ $isArabic?'تجارب العملاء':'Customer experiences' }}</span><h2>{{ $isArabic?'تقييمات المنتج':'Product reviews' }}</h2></header><div>@foreach($reviews->take(6) as $review)<article><div><strong>{{ $review->name ?: ($isArabic?'عميل موثّق':'Verified customer') }}</strong><span>{{ str_repeat('★',$review->rating) }}</span></div>@if($review->content)<p>{{ $review->content }}</p>@endif</article>@endforeach</div></section>@endif
        @if($relatedProducts->isNotEmpty())<section class="web-related-products"><header class="web-section-head"><div><span>{{ $isArabic?'قد يعجبك أيضًا':'You may also like' }}</span><h2>{{ $isArabic?'منتجات أخرى':'Related products' }}</h2></div></header><div class="web-products-grid">@foreach($relatedProducts as $related)@include('site.partials.product-card',['product'=>$related])@endforeach</div></section>@endif
    </div>
</section>
@endsection
@push('styles')<style>.web-product-page{background:var(--web-bg)}</style>@endpush
@push('scripts')@endpush
