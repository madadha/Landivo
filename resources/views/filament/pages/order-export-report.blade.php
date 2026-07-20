<x-filament-panels::page>
    @php
        $landingName = fn ($page) => $page?->translations?->firstWhere('locale', 'ar')?->title ?? $page?->translations?->first()?->title ?? $page?->slug ?? '—';
        $productName = fn ($product) => $product?->translations?->firstWhere('locale', 'ar')?->name ?? $product?->translations?->first()?->name ?? $product?->sku ?? '—';
        $maxStatusCount = max(1, (int) $statusBreakdown->max('orders_count'));
    @endphp

    <div class="ldv-report" dir="rtl">
        <section class="ldv-report-hero ldv-report-hero--orders">
            <div class="ldv-report-hero__copy">
                <span class="ldv-report-kicker"><i></i> مركز تقارير المبيعات</span>
                <h1>تصدير الطلبات</h1>
                <p>حلّل الطلبات والمبيعات ومصادر الإعلانات، ثم نزّل البيانات المطابقة للفلاتر في ملف CSV عربي متوافق مع Excel.</p>
            </div>
            <a href="{{ $this->exportUrl() }}" class="ldv-export-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>تصدير CSV</span>
            </a>
        </section>

        <section class="ldv-report-filters">
            <header><div><strong>تصفية التقرير</strong><span>كل البطاقات والجداول وملف CSV تتبع هذه الفلاتر.</span></div><button type="button" wire:click="clearFilters">إعادة الضبط</button></header>
            <div class="ldv-filter-grid">
                <label><span>بحث سريع</span><input type="search" wire:model.live.debounce.450ms="search" placeholder="رقم الطلب، الاسم، الهاتف أو البريد"></label>
                <label><span>من تاريخ</span><input type="date" wire:model.live="dateFrom"></label>
                <label><span>إلى تاريخ</span><input type="date" wire:model.live="dateTo"></label>
                <label><span>حالة الطلب</span><select wire:model.live="statusId"><option value="">كل الحالات</option>@foreach($statuses as $status)<option value="{{ $status->id }}">{{ $status->name_ar }}</option>@endforeach</select></label>
                <label><span>صفحة الهبوط</span><select wire:model.live="landingPageId"><option value="">كل الصفحات</option>@foreach($landingPages as $page)<option value="{{ $page->id }}">{{ $landingName($page) }}</option>@endforeach</select></label>
                <label><span>المنتج</span><select wire:model.live="productId"><option value="">كل المنتجات</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $productName($product) }}</option>@endforeach</select></label>
                <label><span>مصدر الإعلان</span><select wire:model.live="source"><option value="">كل المصادر</option>@foreach($sources as $sourceOption)<option value="{{ $sourceOption }}">{{ $sourceOption }}</option>@endforeach</select></label>
            </div>
        </section>

        <div class="ldv-report-kpis">
            <article class="is-blue"><span>إجمالي الطلبات</span><strong>{{ number_format($ordersCount) }}</strong><small>طلب مطابق للفلاتر</small><i>↗</i></article>
            <article class="is-emerald"><span>إجمالي المبيعات</span><strong>{{ number_format($salesTotal, 2) }}</strong><small>قيمة الطلبات المحددة</small><i>د.إ</i></article>
            <article class="is-violet"><span>متوسط قيمة الطلب</span><strong>{{ number_format($averageOrderValue, 2) }}</strong><small>متوسط الإنفاق لكل طلب</small><i>Ø</i></article>
            <article class="is-amber"><span>عملاء فريدون</span><strong>{{ number_format($customersCount) }}</strong><small>حسب سجلات العملاء</small><i>◎</i></article>
        </div>

        <div class="ldv-report-grid ldv-report-grid--wide">
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">مسار المبيعات</span><h2>توزيع الطلبات حسب الحالة</h2></div><small>{{ number_format($ordersCount) }} طلب</small></header>
                <div class="ldv-status-report">
                    @forelse($statusBreakdown as $row)
                        <div class="ldv-status-report__row" style="--status: {{ $row->status?->color ?: '#2563eb' }}">
                            <div><i></i><strong>{{ $row->status?->name_ar ?: 'بدون حالة' }}</strong><span>{{ number_format($row->sales_total, 2) }} د.إ</span></div>
                            <div class="ldv-status-report__track"><i style="width: {{ max(4, ((int) $row->orders_count / $maxStatusCount) * 100) }}%"></i></div>
                            <b>{{ number_format($row->orders_count) }}</b>
                        </div>
                    @empty
                        <div class="ldv-report-empty">لا توجد طلبات ضمن الفلاتر الحالية.</div>
                    @endforelse
                </div>
            </section>

            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">أداء المنتجات</span><h2>الأعلى مبيعًا</h2></div><small>حسب قيمة المبيعات</small></header>
                <div class="ldv-ranking-list">
                    @forelse($topProducts as $row)
                        <div><b>{{ $loop->iteration }}</b><span><strong>{{ $row->product_name ?: 'منتج غير مسمى' }}</strong><small>{{ number_format($row->units_count) }} قطعة</small></span><em>{{ number_format($row->sales_total, 2) }} د.إ</em></div>
                    @empty
                        <div class="ldv-report-empty">لا توجد بيانات منتجات بعد.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="ldv-report-grid">
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">صفحات الهبوط</span><h2>الصفحات المحققة للطلبات</h2></div></header>
                <div class="ldv-compact-list">
                    @forelse($landingBreakdown as $row)
                        <div><span><strong>{{ $landingName($row->landingPage) }}</strong><small>/l/{{ $row->landingPage?->slug }}</small></span><b>{{ number_format($row->orders_count) }} طلب</b><em>{{ number_format($row->sales_total, 2) }} د.إ</em></div>
                    @empty<div class="ldv-report-empty">لا توجد صفحات ضمن النتائج.</div>@endforelse
                </div>
            </section>
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">الإعلانات والاكتساب</span><h2>مصادر الطلبات</h2></div></header>
                <div class="ldv-compact-list">
                    @forelse($sourceBreakdown as $row)
                        <div><span><strong>{{ $row->source_name === 'direct' ? 'زيارة مباشرة' : $row->source_name }}</strong><small>مصدر محفوظ مع الطلب</small></span><b>{{ number_format($row->orders_count) }} طلب</b><em>{{ number_format($row->sales_total, 2) }} د.إ</em></div>
                    @empty<div class="ldv-report-empty">لا توجد مصادر ضمن النتائج.</div>@endforelse
                </div>
            </section>
        </div>

        <section class="ldv-report-panel ldv-report-table-panel">
            <header><div><span class="ldv-panel-eyebrow">معاينة قبل التصدير</span><h2>الطلبات المطابقة</h2></div><small>{{ number_format($orders->total()) }} سجل</small></header>
            <div class="ldv-report-table-wrap">
                <table class="ldv-report-table">
                    <thead><tr><th>الطلب</th><th>العميل</th><th>الحالة</th><th>صفحة الهبوط</th><th>المنتجات</th><th>المصدر</th><th>الإجمالي</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong><small>#{{ $order->id }}</small></td>
                            <td><strong>{{ $order->customer?->name ?: '—' }}</strong><small dir="ltr">{{ $order->customer?->phone ?: $order->customer?->email }}</small></td>
                            <td><span class="ldv-table-status" style="--status: {{ $order->status?->color ?: '#64748b' }}">{{ $order->status?->name_ar ?: 'بدون حالة' }}</span></td>
                            <td><strong>{{ $landingName($order->landingPage) }}</strong><small>/l/{{ $order->landingPage?->slug }}</small></td>
                            <td><strong>{{ $order->items->pluck('product_name')->filter()->implode('، ') ?: '—' }}</strong><small>{{ number_format($order->items->sum('quantity')) }} قطعة</small></td>
                            <td><span class="ldv-source-pill">{{ $order->source ?: 'direct' }}</span></td>
                            <td><strong>{{ number_format((float) $order->total, 2) }}</strong><small>{{ $order->currency }}</small></td>
                            <td><strong>{{ $order->created_at?->format('Y-m-d') }}</strong><small>{{ $order->created_at?->format('H:i') }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="ldv-report-empty">لا توجد طلبات مطابقة. غيّر الفلاتر وحاول مجددًا.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ldv-report-pagination">{{ $orders->links() }}</div>
        </section>
    </div>
</x-filament-panels::page>
