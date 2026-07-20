<x-filament-panels::page>
    @php
        $landingName = fn ($page) => $page?->translations?->firstWhere('locale', 'ar')?->title ?? $page?->translations?->first()?->title ?? $page?->slug ?? '—';
        $productName = fn ($product) => $product?->translations?->firstWhere('locale', 'ar')?->name ?? $product?->translations?->first()?->name ?? $product?->sku ?? '—';
        $maxRatingCount = max(1, (int) $ratingDistribution->max());
        $approvalRate = $reviewsCount > 0 ? ($approvedCount / $reviewsCount) * 100 : 0;
    @endphp

    <div class="ldv-report" dir="rtl">
        <section class="ldv-report-hero ldv-report-hero--reviews">
            <div class="ldv-report-hero__copy">
                <span class="ldv-report-kicker"><i></i> صوت العميل</span>
                <h1>تقرير التقييمات</h1>
                <p>راقب رضا العملاء وجودة المنتجات وصفحات الهبوط، واعرف التقييمات الموثقة والتي تحتاج إلى مراجعة.</p>
            </div>
            <a href="{{ $this->exportUrl() }}" class="ldv-export-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>تصدير التقييمات</span>
            </a>
        </section>

        <section class="ldv-report-filters">
            <header><div><strong>تصفية التقييمات</strong><span>حدّد الفترة والمنتج والصفحة وحالة الاعتماد.</span></div><button type="button" wire:click="clearFilters">إعادة الضبط</button></header>
            <div class="ldv-filter-grid">
                <label><span>بحث</span><input type="search" wire:model.live.debounce.450ms="search" placeholder="اسم العميل، الهاتف، البريد أو نص التقييم"></label>
                <label><span>من تاريخ</span><input type="date" wire:model.live="dateFrom"></label>
                <label><span>إلى تاريخ</span><input type="date" wire:model.live="dateTo"></label>
                <label><span>عدد النجوم</span><select wire:model.live="rating"><option value="">كل التقييمات</option>@for($stars = 5; $stars >= 1; $stars--)<option value="{{ $stars }}">{{ $stars }} نجوم</option>@endfor</select></label>
                <label><span>حالة الاعتماد</span><select wire:model.live="approval"><option value="">الكل</option><option value="approved">معتمد</option><option value="pending">بانتظار الاعتماد</option></select></label>
                <label><span>المنتج</span><select wire:model.live="productId"><option value="">كل المنتجات</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $productName($product) }}</option>@endforeach</select></label>
                <label><span>صفحة الهبوط</span><select wire:model.live="landingPageId"><option value="">كل الصفحات</option>@foreach($landingPages as $page)<option value="{{ $page->id }}">{{ $landingName($page) }}</option>@endforeach</select></label>
            </div>
        </section>

        <div class="ldv-report-kpis">
            <article class="is-amber"><span>متوسط التقييم</span><strong>{{ number_format($averageRating, 1) }} <small>/ 5</small></strong><small><span class="ldv-stars">★★★★★</span></small><i>★</i></article>
            <article class="is-blue"><span>إجمالي التقييمات</span><strong>{{ number_format($reviewsCount) }}</strong><small>ضمن الفلاتر الحالية</small><i>▤</i></article>
            <article class="is-emerald"><span>تقييمات معتمدة</span><strong>{{ number_format($approvedCount) }}</strong><small>{{ number_format($approvalRate, 0) }}% من النتائج</small><i>✓</i></article>
            <article class="is-violet"><span>شراء موثّق</span><strong>{{ number_format($verifiedCount) }}</strong><small>مرتبط بطلب حقيقي</small><i>◆</i></article>
        </div>

        <div class="ldv-report-grid ldv-report-grid--wide">
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">جودة التجربة</span><h2>توزيع النجوم</h2></div><small>{{ number_format($averageRating, 1) }} متوسط</small></header>
                <div class="ldv-rating-bars">
                    @foreach($ratingDistribution as $stars => $count)
                        <div><span>{{ $stars }} <b>★</b></span><div><i style="width: {{ max($count ? 5 : 0, ($count / $maxRatingCount) * 100) }}%"></i></div><strong>{{ number_format($count) }}</strong></div>
                    @endforeach
                </div>
            </section>
            <section class="ldv-report-panel ldv-sentiment-card">
                <header><div><span class="ldv-panel-eyebrow">مؤشر الرضا</span><h2>صحة التقييمات</h2></div></header>
                <div class="ldv-score-ring" style="--score: {{ ($averageRating / 5) * 100 }}"><div><strong>{{ number_format(($averageRating / 5) * 100, 0) }}%</strong><span>رضا العملاء</span></div></div>
                <p>{{ $averageRating >= 4 ? 'أداء ممتاز. حافظ على جودة المنتج وسرعة المتابعة.' : ($averageRating >= 3 ? 'أداء جيد مع مساحة واضحة لتحسين تجربة العميل.' : 'التقييمات تحتاج إلى مراجعة سريعة لأسباب عدم الرضا.') }}</p>
            </section>
        </div>

        <div class="ldv-report-grid">
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">المنتجات</span><h2>الأكثر تقييمًا</h2></div></header>
                <div class="ldv-compact-list">
                    @forelse($topProducts as $row)
                        <div><span><strong>{{ $productName($row->product) }}</strong><small><span class="ldv-stars">★</span> {{ number_format($row->average_rating, 1) }}</small></span><b>{{ number_format($row->reviews_count) }} تقييم</b><em>{{ number_format(($row->average_rating / 5) * 100, 0) }}%</em></div>
                    @empty<div class="ldv-report-empty">لا توجد تقييمات منتجات ضمن النتائج.</div>@endforelse
                </div>
            </section>
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">صفحات الهبوط</span><h2>تقييمات الصفحات</h2></div></header>
                <div class="ldv-compact-list">
                    @forelse($topLandingPages as $row)
                        <div><span><strong>{{ $landingName($row->landingPage) }}</strong><small>/l/{{ $row->landingPage?->slug }}</small></span><b>{{ number_format($row->reviews_count) }} تقييم</b><em><span class="ldv-stars">★</span> {{ number_format($row->average_rating, 1) }}</em></div>
                    @empty<div class="ldv-report-empty">لا توجد تقييمات صفحات ضمن النتائج.</div>@endforelse
                </div>
            </section>
        </div>

        <section class="ldv-report-panel ldv-report-table-panel">
            <header><div><span class="ldv-panel-eyebrow">سجل آراء العملاء</span><h2>التقييمات المطابقة</h2></div><small>{{ number_format($reviews->total()) }} تقييم</small></header>
            <div class="ldv-report-table-wrap">
                <table class="ldv-report-table">
                    <thead><tr><th>العميل</th><th>التقييم</th><th>التعليق</th><th>المنتج</th><th>صفحة الهبوط</th><th>الموثوقية</th><th>الحالة</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td><strong>{{ $review->name }}</strong><small dir="ltr">{{ $review->customer_phone ?: $review->customer_email }}</small></td>
                            <td><span class="ldv-review-score"><b>{{ $review->rating }}</b> ★</span></td>
                            <td class="ldv-review-content">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $review->content)), 110) }}</td>
                            <td><strong>{{ $productName($review->product) }}</strong><small>{{ $review->product?->sku }}</small></td>
                            <td><strong>{{ $landingName($review->landingPage) }}</strong><small>/l/{{ $review->landingPage?->slug }}</small></td>
                            <td>@if($review->is_verified_purchase)<span class="ldv-verified-pill">✓ شراء موثّق</span>@else<span class="ldv-muted-pill">غير مرتبط بطلب</span>@endif</td>
                            <td>@if($review->is_approved)<span class="ldv-approved-pill">معتمد</span>@else<span class="ldv-pending-pill">بانتظار الاعتماد</span>@endif</td>
                            <td><strong>{{ $review->created_at?->format('Y-m-d') }}</strong><small>{{ $review->created_at?->format('H:i') }}</small></td>
                        </tr>
                    @empty<tr><td colspan="8"><div class="ldv-report-empty">لا توجد تقييمات مطابقة للفلاتر الحالية.</div></td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            <div class="ldv-report-pagination">{{ $reviews->links() }}</div>
        </section>
    </div>
</x-filament-panels::page>
