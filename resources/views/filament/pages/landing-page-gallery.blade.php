<x-filament-panels::page>
    <div class="ldv-gallery" dir="rtl">
        <section class="ldv-gallery-hero">
            <div>
                <span class="ldv-gallery-kicker">مركز الحملات التسويقية</span>
                <h1>صفحات الهبوط في مكان واحد</h1>
                <p>راقب أداء صفحاتك، افتح الصفحة المنشورة وعدّل تصميمها ومحتواها مباشرة من معرض بصري واضح.</p>
            </div>
            <div class="ldv-gallery-hero-actions">
                <div><strong>{{ number_format($publishedPages) }}</strong><span>صفحة منشورة</span></div>
                <div><strong>{{ number_format($totalPages) }}</strong><span>إجمالي الصفحات</span></div>
                <a href="{{ $createUrl }}">+ إنشاء صفحة هبوط</a>
            </div>
        </section>

        <section class="ldv-gallery-toolbar">
            <label class="ldv-gallery-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" wire:model.live.debounce.350ms="search" placeholder="ابحث بالعنوان أو الرابط أو كود المنتج...">
            </label>
            <select wire:model.live="status">
                <option value="">كل الحالات</option>
                @foreach($statuses as $statusOption)<option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</option>@endforeach
            </select>
            @if($search || $status)<button type="button" wire:click="clearFilters">مسح التصفية</button>@endif
        </section>

        <section class="ldv-landing-grid">
            @forelse($pages as $page)
                @php
                    $image = $this->previewImage($page);
                    $views = (int) $page->views_count;
                    $orders = (int) $page->orders_count;
                    $conversion = $views > 0 ? ($orders / $views) * 100 : 0;
                    $statusValue = $page->status instanceof \App\LandingPageStatus ? $page->status->value : (string) $page->status;
                @endphp
                <article class="ldv-landing-card" wire:key="landing-card-{{ $page->id }}">
                    <a class="ldv-landing-cover" href="{{ $this->editUrl($page) }}">
                        @if($image)<img src="{{ $image }}" alt="{{ $this->pageTitle($page) }}" loading="lazy">@else<div class="ldv-landing-placeholder"><span>{{ mb_substr($this->pageTitle($page), 0, 1) }}</span><small>صفحة هبوط</small></div>@endif
                        <span class="ldv-page-status is-{{ $statusValue }}"><i></i>{{ $page->status?->label() ?? $statusValue }}</span>
                        <span class="ldv-cover-edit">فتح التفاصيل</span>
                    </a>
                    <div class="ldv-landing-body">
                        <div class="ldv-landing-heading">
                            <div><span>{{ $this->productName($page) }}</span><h2>{{ $this->pageTitle($page) }}</h2></div>
                            <a href="{{ $this->publicUrl($page) }}" target="_blank" rel="noopener" title="فتح الصفحة">↗</a>
                        </div>
                        <a class="ldv-landing-slug" href="{{ $this->publicUrl($page) }}" target="_blank" rel="noopener" dir="ltr">/l/{{ $page->slug }}</a>
                        <div class="ldv-landing-stats">
                            <div><span>الزيارات</span><strong>{{ number_format($views) }}</strong></div>
                            <div><span>الطلبات</span><strong>{{ number_format($orders) }}</strong></div>
                            <div><span>التحويل</span><strong>{{ number_format($conversion, 1) }}%</strong></div>
                            <div><span>الإيراد</span><strong>{{ number_format((float) $page->revenue_total, 0) }}</strong></div>
                        </div>
                        <div class="ldv-landing-footer"><span>آخر تحديث {{ $page->updated_at?->diffForHumans() }}</span><a href="{{ $this->editUrl($page) }}">إدارة الصفحة ←</a></div>
                    </div>
                </article>
            @empty
                <div class="ldv-gallery-empty"><strong>لا توجد صفحات مطابقة</strong><span>غيّر كلمات البحث أو أنشئ صفحة هبوط جديدة.</span><a href="{{ $createUrl }}">إنشاء صفحة</a></div>
            @endforelse
        </section>

        @if($pages->hasPages())<div class="ldv-gallery-pagination">{{ $pages->links() }}</div>@endif
    </div>
</x-filament-panels::page>
