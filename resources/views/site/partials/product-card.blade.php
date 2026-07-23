@php
    $translation = $product->translations->firstWhere('locale', app()->getLocale()) ?: $product->translations->first();
    $image = $product->localizedMedia()?->file_path ?: data_get($product->metadata, app()->getLocale() === 'ar' ? 'image_ar' : 'image_en') ?: $product->primary_image_path;
    $discount = $product->compare_at_price && $product->compare_at_price > $product->price ? round((1 - $product->price / $product->compare_at_price) * 100) : 0;
    $badgeLabel = $product->badgeLabel();
    $badgeStyle = in_array($product->badge_style, ['pill', 'ribbon', 'outline'], true) ? $product->badge_style : 'pill';
@endphp
<article class="web-product-card">
    <a class="web-product-card-link" href="{{ route('site.products.show', $product) }}" aria-label="{{ $translation?->name ?? $product->sku }}">
        <div class="web-product-media">@if($image)<img src="{{ Storage::disk('public')->url($image) }}" alt="{{ $translation?->name ?? $product->sku }}">@else<span>{{ $account?->name ?? 'Landivo' }}</span>@endif @if($product->hasVisibleBadge())<span class="product-custom-badge product-custom-badge--{{ $badgeStyle }}" style="--product-badge-bg:{{ $product->badge_background_color ?: '#d97706' }};--product-badge-text:{{ $product->badge_text_color ?: '#ffffff' }}">{{ $badgeLabel }}</span>@endif @if($discount)<b>-{{ $discount }}%</b>@endif</div>
        <div class="web-product-body"><small>{{ $product->sku }}</small><h3>{{ $translation?->name ?? $product->sku }}</h3>@if($translation?->description)<p>{{ Str::limit(strip_tags($translation->description), 90) }}</p>@endif<div class="web-product-price"><strong>{{ number_format((float)$product->price,2) }} <em>{{ $product->currency }}</em></strong>@if($discount)<del>{{ number_format((float)$product->compare_at_price,2) }}</del>@endif</div><span class="web-product-more">{{ app()->getLocale()==='ar'?'عرض التفاصيل':'View details' }} ←</span></div>
    </a>
</article>
