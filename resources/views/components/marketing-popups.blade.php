@php
    $popupItems = collect($marketingPopups ?? []);
    $popupLocale = app()->getLocale() === 'en' ? 'en' : 'ar';
@endphp
@if($popupItems->isNotEmpty())
<link rel="stylesheet" href="{{ asset('css/marketing-popups.css') }}?v={{ filemtime(public_path('css/marketing-popups.css')) }}">
<div class="marketing-popups" data-marketing-popups data-event-base="{{ url('/marketing-popups') }}" data-csrf="{{ csrf_token() }}">
@foreach($popupItems as $popup)
@php
    $eyebrow = $popup->localized('eyebrow', $popupLocale);
    $title = $popup->localized('title', $popupLocale);
    $description = $popup->localized('description', $popupLocale);
    $buttonText = $popup->localized('button_text', $popupLocale);
    $buttonUrl = $popup->button_url && str_starts_with($popup->button_url, '/') ? url($popup->button_url) : $popup->button_url;
    $desktopImage = $popup->desktop_image ? Storage::disk('public')->url($popup->desktop_image) : null;
    $mobileImage = $popup->mobile_image ? Storage::disk('public')->url($popup->mobile_image) : $desktopImage;
    $template = $popup->template instanceof \App\MarketingPopupTemplate ? $popup->template->value : $popup->template;
@endphp
<section class="marketing-popup marketing-popup--{{ $template }}" data-marketing-popup data-id="{{ $popup->id }}" data-device="{{ $popup->device }}" data-trigger="{{ $popup->trigger_type }}" data-delay="{{ $popup->delay_seconds }}" data-scroll="{{ $popup->scroll_percentage }}" data-frequency="{{ $popup->frequency }}" data-close-backdrop="{{ $popup->close_on_backdrop ? '1' : '0' }}" aria-hidden="true" hidden style="--mp-bg:{{ $popup->background_color }};--mp-text:{{ $popup->text_color }};--mp-button:{{ $popup->button_color }};--mp-button-text:{{ $popup->button_text_color }};--mp-overlay:{{ $popup->overlay_color }};--mp-radius:{{ $popup->border_radius }}px;--mp-width:{{ $popup->max_width }}px">
<button class="marketing-popup__backdrop" type="button" data-popup-backdrop aria-label="{{ $popupLocale === 'ar' ? 'إغلاق' : 'Close' }}"></button>
<div class="marketing-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="marketing-popup-title-{{ $popup->id }}">
@if($popup->allow_close)<button class="marketing-popup__close" type="button" data-popup-close aria-label="{{ $popupLocale === 'ar' ? 'إغلاق النافذة' : 'Close popup' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>@endif
@if($desktopImage)<picture class="marketing-popup__media">@if($mobileImage)<source media="(max-width: 680px)" srcset="{{ $mobileImage }}">@endif<img src="{{ $desktopImage }}" alt="{{ $title }}" loading="eager"></picture>@endif
<div class="marketing-popup__content" dir="{{ $popupLocale === 'ar' ? 'rtl' : 'ltr' }}">
@if($template === 'coupon')<span class="marketing-popup__coupon-cut" aria-hidden="true">%</span>@endif
@if($eyebrow)<span class="marketing-popup__eyebrow">{{ $eyebrow }}</span>@endif
@if($title)<h2 id="marketing-popup-title-{{ $popup->id }}">{{ $title }}</h2>@endif
@if($description)<p>{!! nl2br(e($description)) !!}</p>@endif
@if($buttonText && $buttonUrl)<a class="marketing-popup__button" href="{{ $buttonUrl }}" data-popup-action @if($popup->open_new_tab) target="_blank" rel="noopener" @endif><span>{{ $buttonText }}</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>@endif
</div></div></section>
@endforeach
</div>
<script src="{{ asset('js/marketing-popups.js') }}?v={{ filemtime(public_path('js/marketing-popups.js')) }}" defer></script>
@endif
