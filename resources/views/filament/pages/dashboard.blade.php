<x-filament-panels::page>
    @php
        $maxDaily = max(1, collect($dailyPerformance)->max(fn ($day) => max($day['orders'], $day['visits'])));
        $icon = function (string $name): string {
            return match ($name) {
                'page' => '<path d="M6.75 2.75h6.1l4.4 4.4v14.1H6.75z"/><path d="M12.75 2.75v4.5h4.5M9.5 12h5M9.5 15.5h5"/>',
                'product' => '<path d="M4 8.25 12 3l8 5.25v8.5L12 22l-8-5.25z"/><path d="m4.5 8.5 7.5 4.75 7.5-4.75M12 13.25V22"/>',
                'order' => '<path d="M6 3.5h12v17H6z"/><path d="M9 8h6M9 12h6M9 16h3"/>',
                default => '<path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 10a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM16 4a3.5 3.5 0 0 1 0 6.8M21 20v-2a4 4 0 0 0-3-3.7"/>',
            };
        };
    @endphp

    <div class="ldv-dashboard" dir="rtl">
        <section class="ldv-dashboard-hero">
            <div class="ldv-hero-copy">
                <span class="ldv-live-pill"><i></i> مركز أداء Landivo</span>
                <h1>أهلًا {{ auth()->user()?->name }}، هذه صورة أعمالك اليوم.</h1>
                <p>تابع رحلة العميل من الزيارة إلى الطلب، واتخذ قرارات أسرع من لوحة واحدة واضحة.</p>
                <div class="ldv-hero-actions">
                    <a href="{{ \App\Filament\Resources\LandingPages\LandingPageResource::getUrl('create') }}" class="ldv-primary-action">إنشاء صفحة هبوط <span>←</span></a>
                    <a href="{{ \App\Filament\Pages\VisitorAnalytics::getUrl() }}" class="ldv-secondary-action">فتح التحليلات</a>
                </div>
            </div>
            <div class="ldv-hero-score">
                <span>معدل التحويل هذا الشهر</span>
                <strong>{{ number_format($stats['conversion'], 1) }}%</strong>
                <div><i style="width:{{ min(100, $stats['conversion'] * 5) }}%"></i></div>
                <small>{{ number_format($stats['visitors']) }} زائرًا فريدًا · {{ number_format($stats['orders_today']) }} طلب اليوم</small>
            </div>
        </section>

        <section class="ldv-kpis">
            @foreach([
                ['label' => 'إجمالي الطلبات', 'value' => number_format($stats['orders']), 'hint' => $stats['new_orders'].' بانتظار المتابعة', 'tone' => 'blue', 'icon' => '↗'],
                ['label' => 'إيراد الشهر', 'value' => number_format($stats['revenue'], 2), 'hint' => $stats['currency'], 'tone' => 'emerald', 'icon' => '◆'],
                ['label' => 'العملاء والـ Leads', 'value' => number_format($stats['customers']), 'hint' => 'قاعدة العملاء الحالية', 'tone' => 'violet', 'icon' => '◉'],
                ['label' => 'صفحات فعالة', 'value' => $stats['active_pages'].' / '.$stats['landing_pages'], 'hint' => $stats['products'].' منتج في النظام', 'tone' => 'amber', 'icon' => '◇'],
            ] as $stat)
                <article class="ldv-kpi ldv-tone-{{ $stat['tone'] }}">
                    <div class="ldv-kpi-top"><span>{{ $stat['label'] }}</span><i>{{ $stat['icon'] }}</i></div>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="ldv-quick-grid">
            @foreach($quickActions as $action)
                <a href="{{ $action['url'] }}" class="ldv-quick ldv-tone-{{ $action['tone'] }}">
                    <span class="ldv-quick-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">{!! $icon($action['icon']) !!}</svg></span>
                    <span><strong>{{ $action['label'] }}</strong><small>{{ $action['description'] }}</small></span>
                    <b>←</b>
                </a>
            @endforeach
        </section>

        <div class="ldv-dashboard-grid">
            <section class="ldv-panel ldv-performance">
                <header><div><span class="ldv-eyebrow">آخر 7 أيام</span><h2>نبض الزيارات والطلبات</h2></div><div class="ldv-legend"><span><i class="visits"></i> الزيارات</span><span><i class="orders"></i> الطلبات</span></div></header>
                <div class="ldv-chart">
                    @foreach($dailyPerformance as $day)
                        <div class="ldv-chart-day">
                            <div class="ldv-bars"><i class="visits" title="{{ $day['visits'] }} زيارة" style="height:{{ max(7, round(($day['visits'] / $maxDaily) * 150)) }}px"></i><i class="orders" title="{{ $day['orders'] }} طلب" style="height:{{ max(7, round(($day['orders'] / $maxDaily) * 150)) }}px"></i></div>
                            <strong>{{ $day['label'] }}</strong><small>{{ $day['date'] }}</small>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="ldv-panel">
                <header><div><span class="ldv-eyebrow">توزيع العمل</span><h2>حالات الطلبات</h2></div><a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl() }}">عرض الكل</a></header>
                <div class="ldv-status-list">
                    @forelse($orderStatuses as $status)
                        <div class="ldv-status"><div><span><i style="background:{{ $status['color'] }}"></i>{{ $status['label'] }}</span><strong>{{ $status['count'] }}</strong></div><div class="ldv-progress"><i style="width:{{ $status['percent'] }}%;background:{{ $status['color'] }}"></i></div></div>
                    @empty
                        <div class="ldv-empty">أضف حالات الطلب لتظهر هنا.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="ldv-dashboard-grid ldv-bottom-grid">
            <section class="ldv-panel">
                <header><div><span class="ldv-eyebrow">آخر 30 يومًا</span><h2>أفضل صفحات الهبوط</h2></div><a href="{{ \App\Filament\Resources\LandingPages\LandingPageResource::getUrl() }}">إدارة الصفحات</a></header>
                <div class="ldv-pages-list">
                    @forelse($topLandingPages as $page)
                        <div class="ldv-page-row"><div class="ldv-page-rank">{{ $loop->iteration }}</div><div class="ldv-page-info"><strong>{{ $page['title'] }}</strong><a href="{{ $page['public_url'] }}" target="_blank">/l/{{ $page['slug'] }}</a><div class="ldv-progress"><i style="width:{{ $page['percent'] }}%"></i></div></div><div class="ldv-page-visits"><strong>{{ number_format($page['visits']) }}</strong><small>زيارة</small></div><a class="ldv-edit-link" href="{{ $page['edit_url'] }}">تعديل</a></div>
                    @empty
                        <div class="ldv-empty">ستظهر الصفحات الأعلى أداءً بعد استقبال الزيارات.</div>
                    @endforelse
                </div>
            </section>

            <section class="ldv-panel ldv-orders-panel">
                <header><div><span class="ldv-eyebrow">تحديث مباشر</span><h2>أحدث الطلبات</h2></div><a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl() }}">عرض كل الطلبات</a></header>
                <div class="ldv-orders-table-wrap"><table class="ldv-orders-table"><thead><tr><th>العميل</th><th>المصدر</th><th>القيمة</th><th>الحالة</th><th>الوقت</th></tr></thead><tbody>
                    @forelse($recentOrders as $order)
                        <tr onclick="window.location='{{ $order['url'] }}'"><td><strong>{{ $order['customer'] }}</strong><small>{{ $order['phone'] }}</small></td><td><span>{{ $order['page'] }}</span><small>#{{ $order['number'] }}</small></td><td><strong>{{ $order['total'] }}</strong><small>{{ $order['currency'] }}</small></td><td><span class="ldv-status-badge" style="--status-color:{{ $order['status_color'] }}">{{ $order['status'] }}</span></td><td>{{ $order['created_at'] }}</td></tr>
                    @empty
                        <tr><td colspan="5"><div class="ldv-empty">لا توجد طلبات بعد.</div></td></tr>
                    @endforelse
                </tbody></table></div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
