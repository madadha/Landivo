<x-filament-panels::page>
    <div class="osr" dir="rtl">
        <section class="osr-hero">
            <div>
                <span class="osr-kicker">تقارير المبيعات</span>
                <h1>تقرير حالات الطلبات</h1>
                <p>راقب توزيع الطلبات وقيمة المبيعات، وانتقل مباشرة إلى الطلبات التابعة لكل حالة.</p>
            </div>
            <a class="osr-primary-link" href="{{ $this->getOrdersUrl() }}">
                <span>عرض جميع الطلبات</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        </section>

        <section class="osr-summary" aria-label="ملخص التقرير">
            <article class="osr-stat osr-stat--orders">
                <span class="osr-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h12a2 2 0 0 1 2 2v18l-3-2-3 2-3-2-3 2-4-2.7V4a2 2 0 0 1 2-2Z"/><path d="M8 7h8M8 11h8M8 15h5"/></svg></span>
                <div><small>إجمالي الطلبات</small><strong>{{ number_format($total) }}</strong><span>طلب مسجل</span></div>
            </article>
            <article class="osr-stat osr-stat--revenue">
                <span class="osr-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 6.5c0-1.4-2.2-2.5-5-2.5S7 5.1 7 6.5 9.2 9 12 9s5 1.1 5 2.5S14.8 14 12 14s-5 1.1-5 2.5S9.2 19 12 19s5-1.1 5-2.5"/></svg></span>
                <div><small>إجمالي المبيعات</small><strong>{{ number_format($revenue, 2) }}</strong><span>AED</span></div>
            </article>
            <article class="osr-stat osr-stat--average">
                <span class="osr-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg></span>
                <div><small>متوسط قيمة الطلب</small><strong>{{ number_format($averageOrderValue, 2) }}</strong><span>AED</span></div>
            </article>
            <article class="osr-stat osr-stat--statuses">
                <span class="osr-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12a9 9 0 1 1-5.3-8.2"/></svg></span>
                <div><small>حالات الطلبات</small><strong>{{ number_format($rows->count()) }}</strong><span>حالة معرفة</span></div>
            </article>
        </section>

        <section class="osr-panel">
            <header class="osr-panel-head">
                <div><span class="osr-kicker">توزيع الحالات</span><h2>أداء الطلبات حسب الحالة</h2><p>اضغط على أي بطاقة لعرض الطلبات الخاصة بهذه الحالة فقط.</p></div>
                <span class="osr-count">{{ $rows->count() }} حالات</span>
            </header>

            <div class="osr-grid">
                @forelse ($rows as $status)
                    @php
                        $count = (int) $status->orders_count;
                        $percent = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                        $color = $status->color ?: '#64748b';
                    @endphp
                    <a class="osr-status" href="{{ $this->getOrdersUrl($status->getKey()) }}" style="--status-color: {{ $color }}">
                        <div class="osr-status-top">
                            <span class="osr-status-mark"><i></i></span>
                            <span class="osr-status-state {{ $status->archived_at ? 'is-archived' : '' }}">{{ $status->archived_at ? 'مؤرشفة' : 'نشطة' }}</span>
                        </div>
                        <div class="osr-status-title">
                            <div><h3>{{ $status->name_ar }}</h3><p>{{ $status->name_en ?: '—' }}</p></div>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                        </div>
                        <div class="osr-metrics">
                            <div><small>عدد الطلبات</small><strong>{{ number_format($count) }}</strong></div>
                            <div><small>قيمة المبيعات</small><strong>{{ number_format((float) ($status->revenue_total ?? 0), 2) }} <em>AED</em></strong></div>
                        </div>
                        <div class="osr-progress-head"><span>نسبة الطلبات</span><strong>{{ $percent }}%</strong></div>
                        <div class="osr-progress"><i style="width: {{ $percent }}%"></i></div>
                        <span class="osr-open">عرض الطلبات <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></span>
                    </a>
                @empty
                    <div class="osr-empty"><span>لا توجد حالات طلبات بعد.</span><a href="{{ $this->getOrdersUrl() }}">عرض الطلبات</a></div>
                @endforelse
            </div>
        </section>
    </div>

    <style>
        .osr{--ink:#101828;--muted:#667085;--line:#e7eaf0;display:grid;gap:22px;font-family:inherit;color:var(--ink)}
        .osr *{box-sizing:border-box}.osr svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
        .osr-hero{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:30px 32px;border-radius:24px;background:linear-gradient(125deg,#0b1735 0%,#142a5b 55%,#194f67 100%);color:#fff;box-shadow:0 18px 42px rgba(16,24,40,.14)}
        .osr-hero:after{content:"";position:absolute;inset:auto -60px -110px auto;width:280px;height:280px;border:55px solid rgba(255,255,255,.055);border-radius:50%}.osr-hero>*{position:relative;z-index:1}
        .osr-kicker{display:block;margin-bottom:7px;color:#f2b84b;font-size:12px;font-weight:800;letter-spacing:.04em}.osr-hero h1{margin:0 0 8px;font-size:clamp(25px,3vw,38px);font-weight:900}.osr-hero p{margin:0;color:#d5deee;font-size:14px}
        .osr-primary-link{display:inline-flex;align-items:center;gap:10px;white-space:nowrap;border-radius:14px;background:#fff;padding:13px 17px;color:#12234a;font-size:13px;font-weight:900;text-decoration:none;box-shadow:0 10px 24px rgba(0,0,0,.15);transition:.2s}.osr-primary-link:hover{transform:translateY(-2px)}.osr-primary-link svg{width:18px}
        .osr-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.osr-stat{display:flex;align-items:center;gap:14px;min-height:128px;padding:20px;border:1px solid var(--line);border-radius:20px;background:#fff;box-shadow:0 9px 28px rgba(16,24,40,.055)}
        .osr-stat-icon{display:grid;place-items:center;flex:0 0 48px;height:48px;border-radius:15px}.osr-stat--orders .osr-stat-icon{background:#eef4ff;color:#3b70e8}.osr-stat--revenue .osr-stat-icon{background:#eafbf3;color:#16a36a}.osr-stat--average .osr-stat-icon{background:#fff7e6;color:#df8b14}.osr-stat--statuses .osr-stat-icon{background:#f3edff;color:#8756d8}
        .osr-stat div{min-width:0}.osr-stat small,.osr-stat span{display:block;color:var(--muted);font-size:11px}.osr-stat strong{display:block;margin:5px 0 2px;font-size:23px;line-height:1.1;font-weight:900;font-variant-numeric:tabular-nums}
        .osr-panel{overflow:hidden;border:1px solid var(--line);border-radius:24px;background:#fff;box-shadow:0 12px 34px rgba(16,24,40,.055)}.osr-panel-head{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:23px 25px;border-bottom:1px solid var(--line)}.osr-panel-head h2{margin:0 0 5px;font-size:21px;font-weight:900}.osr-panel-head p{margin:0;color:var(--muted);font-size:13px}.osr-count{border-radius:999px;background:#f2f4f7;padding:8px 12px;color:#475467;font-size:12px;font-weight:800}
        .osr-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;padding:20px}.osr-status{position:relative;display:block;overflow:hidden;padding:20px;border:1px solid var(--line);border-radius:19px;background:linear-gradient(180deg,#fff,#fcfcfd);color:inherit;text-decoration:none;transition:transform .2s,border-color .2s,box-shadow .2s}.osr-status:before{content:"";position:absolute;inset:0 0 auto;height:4px;background:var(--status-color)}.osr-status:hover{transform:translateY(-3px);border-color:var(--status-color);box-shadow:0 15px 30px rgba(16,24,40,.09)}
        .osr-status-top,.osr-status-title,.osr-progress-head{display:flex;align-items:center;justify-content:space-between;gap:12px}.osr-status-mark{display:grid;place-items:center;width:28px;height:28px;border-radius:9px;background:#f2f4f7}.osr-status-mark i{width:9px;height:9px;border-radius:50%;background:var(--status-color);box-shadow:0 0 0 4px #fff}.osr-status-state{border-radius:999px;background:#eafbf3;padding:5px 9px;color:#087a50;font-size:10px;font-weight:800}.osr-status-state.is-archived{background:#f2f4f7;color:#667085}
        .osr-status-title{margin-top:16px}.osr-status-title h3{margin:0 0 3px;font-size:18px;font-weight:900}.osr-status-title p{margin:0;color:var(--muted);font-size:11px;direction:ltr;text-align:right}.osr-status-title>svg{color:#98a2b3}
        .osr-metrics{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:17px 0;padding:13px;border-radius:14px;background:#f7f8fa}.osr-metrics>div+div{border-right:1px solid #e4e7ec;padding-right:13px}.osr-metrics small{display:block;margin-bottom:5px;color:var(--muted);font-size:10px}.osr-metrics strong{font-size:16px;font-weight:900;font-variant-numeric:tabular-nums}.osr-metrics em{font-size:9px;font-style:normal;color:var(--muted)}
        .osr-progress-head{margin-bottom:7px;color:var(--muted);font-size:10px}.osr-progress-head strong{color:var(--ink);font-size:11px}.osr-progress{height:7px;overflow:hidden;border-radius:999px;background:#eef1f5}.osr-progress i{display:block;height:100%;border-radius:inherit;background:var(--status-color);transition:width .3s}.osr-open{display:flex;align-items:center;justify-content:flex-end;gap:4px;margin-top:15px;color:var(--status-color);font-size:11px;font-weight:900}.osr-open svg{width:15px;height:15px}
        .osr-empty{grid-column:1/-1;display:grid;place-items:center;gap:12px;min-height:220px;color:var(--muted)}.osr-empty a{color:#2563eb;font-weight:800}
        @media(max-width:1050px){.osr-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:760px){.osr{gap:15px}.osr-hero{align-items:flex-start;flex-direction:column;padding:24px 20px;border-radius:20px}.osr-primary-link{width:100%;justify-content:center}.osr-grid{grid-template-columns:1fr;padding:14px}.osr-panel-head{align-items:flex-start;padding:20px;flex-direction:column}.osr-count{align-self:flex-start}}
        @media(max-width:520px){.osr-summary{grid-template-columns:1fr 1fr;gap:9px}.osr-stat{display:block;min-height:132px;padding:14px}.osr-stat-icon{width:38px;height:38px;margin-bottom:12px}.osr-stat-icon svg{width:18px}.osr-stat strong{font-size:18px}.osr-stat span{font-size:9px}.osr-status{padding:17px}.osr-metrics{grid-template-columns:1fr}.osr-metrics>div+div{border-top:1px solid #e4e7ec;border-right:0;padding-top:10px;padding-right:0}}
    </style>
</x-filament-panels::page>
