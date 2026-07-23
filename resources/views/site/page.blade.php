@php($translation=$sitePage->translation())
@php($isArabic=app()->getLocale()==='ar')
@extends('site.layouts.app')
@section('title',$translation?->seo_title?:$translation?->title)
@section('description',$translation?->seo_description?:$translation?->excerpt)
@section('content')
<section class="web-page-hero {{ $translation?->hero_image?'has-image':'' }}" @if($translation?->hero_image) style="--page-hero:url('{{ Storage::disk('public')->url($translation->hero_image) }}')" @endif><div class="web-container"><span>{{ $account?->name }}</span><h1>{{ $translation?->title }}</h1>@if($translation?->excerpt)<p>{{ $translation->excerpt }}</p>@endif</div></section>

@if($sitePage->template==='products')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/site-products.css') }}">
@endpush
<section class="web-section">
    <div class="web-container" data-products-catalog>
        <div class="web-products-grid" data-products-grid>
            @include('site.partials.product-cards', ['products' => $products])
        </div>

        @if($productsLoadMode === 'infinite')
            @if($products->hasMorePages())
                <div class="web-infinite-products" data-products-loader data-next-url="{{ $products->nextPageUrl() }}">
                    <span class="web-load-spinner" aria-hidden="true"></span>
                    <button type="button" data-load-more>
                        {{ $settings[$isArabic ? 'products_load_more_ar' : 'products_load_more_en'] ?? ($isArabic ? 'تحميل المزيد' : 'Load more') }}
                    </button>
                    <p data-load-status aria-live="polite"></p>
                </div>
            @endif
            <noscript>@include('site.partials.pagination', ['paginator' => $products])</noscript>
        @else
            @include('site.partials.pagination', ['paginator' => $products])
        @endif
    </div>
</section>
@if($productsLoadMode === 'infinite')
    @push('scripts')
        <script src="{{ asset('js/site-products.js') }}" defer></script>
    @endpush
@endif
@else
<section class="web-page-content {{ in_array($sitePage->template,['privacy','terms'],true)?'web-legal-page':'' }}"><div class="web-container web-content-shell">
@if(in_array($sitePage->template,['privacy','terms'],true))<nav class="web-legal-nav" aria-label="{{ $isArabic?'الصفحات القانونية':'Legal pages' }}"><div><strong>{{ $isArabic?'المركز القانوني':'Legal center' }}</strong><span>{{ $isArabic?'معلومات واضحة لحماية حقوقك وتجربتك.':'Clear information for a trusted experience.' }}</span></div>@foreach($sitePages->whereIn('template',['privacy','terms']) as $legalPage)<a href="{{ route('site.pages.show',$legalPage->slug) }}" class="{{ $legalPage->is($sitePage)?'active':'' }}">{{ $legalPage->translation()?->navigation_label ?: $legalPage->translation()?->title }} <b>←</b></a>@endforeach</nav>@endif
<article class="web-rich-content">{!! $translation?->content !!}</article>
@if($sitePage->template==='contact')<div class="web-contact-layout"><div class="web-contact-cards"><div><i>✉</i><strong>{{ $isArabic?'البريد الإلكتروني':'Email' }}</strong><span>{{ $settings['contact_email']??'—' }}</span></div><div><i>☎</i><strong>{{ $isArabic?'الهاتف':'Phone' }}</strong><span>{{ $settings['contact_phone']??'—' }}</span></div><div><i>⌖</i><strong>{{ $isArabic?'العنوان':'Address' }}</strong><span>{{ $settings[$isArabic?'contact_address_ar':'contact_address_en']??'—' }}</span></div></div><form class="web-contact-form" method="post" action="{{ route('site.contact',$sitePage) }}">@csrf @if(session('contact_success'))<div class="web-success">{{ session('contact_success') }}</div>@endif<div class="web-form-grid"><label>{{ $isArabic?'الاسم الكامل':'Full name' }}<input name="name" value="{{ old('name') }}" required></label><label>{{ $isArabic?'البريد الإلكتروني':'Email' }}<input type="email" name="email" value="{{ old('email') }}"></label><label>{{ $isArabic?'رقم الهاتف':'Phone' }}<input name="phone" value="{{ old('phone') }}"></label><label>{{ $isArabic?'الموضوع':'Subject' }}<input name="subject" value="{{ old('subject') }}"></label></div><label>{{ $isArabic?'رسالتك':'Your message' }}<textarea name="message" required>{{ old('message') }}</textarea></label>@if($errors->any())<div class="web-errors">{{ $errors->first() }}</div>@endif<button>{{ $isArabic?'إرسال الرسالة':'Send message' }} ←</button></form></div>@endif
@foreach((array)($translation?->blocks??[]) as $block)<section class="web-content-block web-block-{{ $block['type']??'content' }}">@if(!empty($block['image']))<img src="{{ Storage::disk('public')->url($block['image']) }}" alt="{{ $block['title']??'' }}">@endif<div>@if(!empty($block['title']))<h2>{{ $block['title'] }}</h2>@endif @if(($block['type']??'')==='html'){!! $block['html']??'' !!}@else{!! $block['body']??'' !!}@endif @if(!empty($block['button_url']))<a href="{{ $block['button_url'] }}">{{ $block['button_label']??($isArabic?'اعرف المزيد':'Learn more') }} ←</a>@endif</div></section>@endforeach
</div></section>
@endif
@endsection
