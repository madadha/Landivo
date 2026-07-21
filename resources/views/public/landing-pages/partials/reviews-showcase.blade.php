@php
    $legacySection = $landingPage->sections->first(fn ($section) => $section->type->value === 'testimonials' && $section->is_visible);
    $enabled = (bool) data_get($landingPage->settings, 'reviews_showcase_enabled', false) || (bool) $legacySection;
    $reviews = $landingPage->reviews;
    $style = data_get($landingPage->settings, 'reviews_showcase_style', 'elegant');
    $style = in_array($style, ['elegant', 'soft', 'glass', 'dark'], true) ? $style : 'elegant';
    $alignment = data_get($landingPage->settings, 'reviews_showcase_alignment', 'center');
    $alignment = in_array($alignment, ['start', 'center', 'end'], true) ? $alignment : 'center';
    $title = data_get($landingPage->settings, app()->getLocale() === 'ar' ? 'reviews_showcase_title_ar' : 'reviews_showcase_title_en');
    $title = $title ?: data_get($legacySection?->settings, app()->getLocale() === 'ar' ? 'title_ar' : 'title_en');
    $title = $title ?: data_get($legacySection?->settings, 'title');
    $title = $title ?: (app()->getLocale() === 'ar' ? 'ماذا يقول عملاؤنا؟' : 'What our customers say');
    $subtitle = data_get($landingPage->settings, app()->getLocale() === 'ar' ? 'reviews_showcase_subtitle_ar' : 'reviews_showcase_subtitle_en');
    $subtitle = $subtitle ?: (app()->getLocale() === 'ar' ? 'تجارب حقيقية من عملائنا' : 'Real experiences from our customers');
    $accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) data_get($landingPage->settings, 'reviews_showcase_accent'))
        ? data_get($landingPage->settings, 'reviews_showcase_accent')
        : '#F59E0B';
@endphp

@if($enabled && $sectionVisible('testimonials') && $reviews->isNotEmpty())
    <section
        class="section reviews-showcase reviews-showcase--{{ $style }}"
        style="order:{{ $sectionPosition('testimonials', 40) }};--reviews-accent:{{ $accent }};--reviews-heading-align:{{ $alignment }}"
        data-reviews-showcase
        data-autoplay="{{ data_get($landingPage->settings, 'reviews_showcase_autoplay', true) ? '1' : '0' }}"
        data-interval="{{ max(2, min(30, (int) data_get($landingPage->settings, 'reviews_showcase_interval', 5))) * 1000 }}"
    >
        <header class="reviews-showcase__header">
            <span>{{ app()->getLocale() === 'ar' ? 'آراء موثوقة' : 'Trusted reviews' }}</span>
            <h2>{{ $title }}</h2>
            @if(filled($subtitle))<p>{{ $subtitle }}</p>@endif
        </header>

        <div class="reviews-showcase__viewport" data-reviews-viewport>
            <div class="reviews-showcase__track" data-reviews-track>
                @foreach($reviews as $review)
                    <article class="reviews-showcase__card" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                        <div class="reviews-showcase__quote" aria-hidden="true">“</div>
                        <div class="reviews-showcase__stars" aria-label="{{ $review->rating }} / 5">
                            {{ str_repeat('★', $review->rating) }}<span>{{ str_repeat('★', 5 - $review->rating) }}</span>
                        </div>
                        @if(filled($review->content))
                            <p>{{ strip_tags($review->content) }}</p>
                        @else
                            <p class="reviews-showcase__rating-only">{{ app()->getLocale() === 'ar' ? 'تجربة تستحق خمس نجوم.' : 'A five-star experience.' }}</p>
                        @endif
                        <footer>
                            @if($review->photo_path)
                                <img src="{{ Storage::disk('public')->url($review->photo_path) }}" alt="{{ $review->name }}">
                            @else
                                <span class="reviews-showcase__avatar">{{ mb_substr($review->name, 0, 1) }}</span>
                            @endif
                            <div>
                                <strong>{{ $review->name }}</strong>
                                <small>{{ $review->created_at->translatedFormat('d M Y') }}</small>
                            </div>
                            @if($review->is_verified_purchase && data_get($landingPage->settings, 'reviews_showcase_verified_badge', true))
                                <span class="reviews-showcase__verified">✓ {{ app()->getLocale() === 'ar' ? 'شراء موثق' : 'Verified' }}</span>
                            @endif
                        </footer>
                    </article>
                @endforeach
            </div>
        </div>

        @if($reviews->count() > 1)
            <div class="reviews-showcase__navigation">
                <button type="button" data-reviews-prev aria-label="{{ app()->getLocale() === 'ar' ? 'السابق' : 'Previous' }}">‹</button>
                <div class="reviews-showcase__dots" data-reviews-dots></div>
                <button type="button" data-reviews-next aria-label="{{ app()->getLocale() === 'ar' ? 'التالي' : 'Next' }}">›</button>
            </div>
        @endif
    </section>
@endif

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-reviews-showcase]').forEach(function (section) {
                var viewport = section.querySelector('[data-reviews-viewport]');
                var track = section.querySelector('[data-reviews-track]');
                var cards = Array.from(track ? track.children : []);
                var dots = section.querySelector('[data-reviews-dots]');
                var previous = section.querySelector('[data-reviews-prev]');
                var next = section.querySelector('[data-reviews-next]');
                var page = 0;
                var pages = 1;
                var timer = null;

                if (!viewport || !track || cards.length < 2) return;

                function perView() {
                    return window.matchMedia('(max-width: 720px)').matches ? 1 : Math.min(3, cards.length);
                }

                function drawDots() {
                    pages = Math.max(1, Math.ceil(cards.length / perView()));
                    page = Math.min(page, pages - 1);
                    if (!dots) return;
                    dots.innerHTML = '';
                    for (var index = 0; index < pages; index++) {
                        var dot = document.createElement('button');
                        dot.type = 'button';
                        dot.setAttribute('aria-label', String(index + 1));
                        dot.classList.toggle('is-active', index === page);
                        dot.addEventListener('click', (function (target) {
                            return function () { page = target; render(); restart(); };
                        })(index));
                        dots.appendChild(dot);
                    }
                }

                function render() {
                    var target = cards[Math.min(page * perView(), cards.length - 1)];
                    var offset = target ? target.offsetLeft : 0;
                    track.style.transform = 'translate3d(' + (-offset) + 'px,0,0)';
                    if (dots) Array.from(dots.children).forEach(function (dot, index) {
                        dot.classList.toggle('is-active', index === page);
                    });
                }

                function move(step) {
                    page = (page + step + pages) % pages;
                    render();
                }

                function restart() {
                    if (timer) window.clearInterval(timer);
                    if (section.dataset.autoplay !== '1' || pages < 2) return;
                    timer = window.setInterval(function () { move(1); }, Number(section.dataset.interval || 5000));
                }

                if (previous) previous.addEventListener('click', function () { move(-1); restart(); });
                if (next) next.addEventListener('click', function () { move(1); restart(); });
                section.addEventListener('mouseenter', function () { if (timer) window.clearInterval(timer); });
                section.addEventListener('mouseleave', restart);
                window.addEventListener('resize', function () { drawDots(); render(); restart(); });

                drawDots();
                render();
                restart();
            });
        });
    </script>
@endonce
