<x-filament-panels::page>
    @php
        $eventLabels = ['created' => 'إنشاء', 'updated' => 'تحديث', 'deleted' => 'حذف', 'restored' => 'استعادة', 'login' => 'تسجيل دخول', 'logout' => 'تسجيل خروج'];
        $eventIcons = ['created' => '+', 'updated' => '↻', 'deleted' => '×', 'restored' => '↥', 'login' => '→', 'logout' => '←'];
        $fieldLabels = [
            'name' => 'الاسم', 'name_ar' => 'الاسم بالعربية', 'name_en' => 'الاسم بالإنجليزية', 'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف', 'status' => 'الحالة', 'order_status_id' => 'حالة الطلب', 'price' => 'السعر',
            'compare_at_price' => 'السعر السابق', 'quantity' => 'الكمية', 'is_active' => 'مفعّل', 'is_approved' => 'معتمد',
            'is_featured' => 'مميز', 'title' => 'العنوان', 'slug' => 'الرابط المختصر', 'settings' => 'الإعدادات',
            'content' => 'المحتوى', 'description' => 'الوصف', 'total' => 'الإجمالي', 'source' => 'المصدر',
        ];
        $displayValue = function ($value, string $key): string {
            if ($value === null) return 'فارغ';
            $booleanFields = ['is_active', 'is_approved', 'is_featured', 'is_visible', 'is_final', 'deduct_inventory', 'track_inventory'];
            if (in_array($key, $booleanFields, true) && ($value === true || $value === '1' || $value === 1)) return 'نعم';
            if (in_array($key, $booleanFields, true) && ($value === false || $value === '0' || $value === 0)) return 'لا';
            if (is_array($value)) return \Illuminate\Support\Str::limit(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 600, '…');
            return \Illuminate\Support\Str::limit(strip_tags((string) $value), 600, '…');
        };
    @endphp

    <div class="ldv-audit" dir="rtl">
        <section class="ldv-audit-hero">
            <div><span><i></i> مراقبة وأمان النظام</span><h1>سجل التدقيق</h1><p>سجل زمني موثوق لكل التغييرات المهمة داخل Landivo: من قام بها، ماذا تغيّر، ومتى ومن أي جهاز.</p></div>
            <aside><strong>{{ number_format($total) }}</strong><span>حدث ضمن الفلاتر</span><small>آخر تحديث {{ now()->format('H:i') }}</small></aside>
        </section>

        <section class="ldv-report-filters">
            <header><div><strong>البحث والتصفية</strong><span>تتبّع مستخدمًا أو قسمًا أو عملية ضمن فترة محددة.</span></div><button type="button" wire:click="clearFilters">إعادة الضبط</button></header>
            <div class="ldv-filter-grid">
                <label><span>بحث</span><input type="search" wire:model.live.debounce.450ms="search" placeholder="المستخدم، السجل، العملية أو IP"></label>
                <label><span>من تاريخ</span><input type="date" wire:model.live="dateFrom"></label>
                <label><span>إلى تاريخ</span><input type="date" wire:model.live="dateTo"></label>
                <label><span>نوع العملية</span><select wire:model.live="event"><option value="">كل العمليات</option>@foreach($eventLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                <label><span>القسم</span><select wire:model.live="module"><option value="">كل الأقسام</option>@foreach($modules as $moduleOption)<option value="{{ $moduleOption }}">{{ $moduleOption }}</option>@endforeach</select></label>
                <label><span>المستخدم</span><select wire:model.live="userId"><option value="">كل المستخدمين</option>@foreach($users as $userOption)<option value="{{ $userOption->id }}">{{ $userOption->name }} — {{ $userOption->email }}</option>@endforeach</select></label>
            </div>
        </section>

        <div class="ldv-report-kpis">
            <article class="is-blue"><span>إجمالي النشاط</span><strong>{{ number_format($total) }}</strong><small>كل العمليات المطابقة</small><i>◎</i></article>
            <article class="is-emerald"><span>سجلات جديدة</span><strong>{{ number_format($createdCount) }}</strong><small>عمليات إنشاء</small><i>+</i></article>
            <article class="is-violet"><span>تعديلات</span><strong>{{ number_format($updatedCount) }}</strong><small>حقول تم تحديثها</small><i>↻</i></article>
            <article class="is-amber"><span>أحداث الدخول</span><strong>{{ number_format($authCount) }}</strong><small>دخول وخروج المستخدمين</small><i>→</i></article>
        </div>

        <div class="ldv-report-grid ldv-report-grid--wide">
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">الحركة اليومية</span><h2>نشاط النظام خلال الفترة</h2></div></header>
                <div class="ldv-audit-chart">
                    @forelse($dailyActivity as $day)
                        <div><span>{{ $day['count'] }}</span><i style="height: {{ max(8, ($day['count'] / $maxDailyActivity) * 130) }}px"></i><small>{{ $day['day'] }}</small></div>
                    @empty<div class="ldv-report-empty">ستظهر الحركة هنا بعد تسجيل أول تغيير.</div>@endforelse
                </div>
            </section>
            <section class="ldv-report-panel">
                <header><div><span class="ldv-panel-eyebrow">توزيع النشاط</span><h2>الأقسام الأكثر تغييرًا</h2></div></header>
                <div class="ldv-audit-modules">
                    @forelse($moduleBreakdown as $row)
                        <div><b>{{ $loop->iteration }}</b><span>{{ $row->module }}</span><strong>{{ number_format($row->events_count) }}</strong></div>
                    @empty<div class="ldv-report-empty">لا توجد عمليات مسجلة ضمن الفترة.</div>@endforelse
                </div>
            </section>
        </div>

        <section class="ldv-report-panel ldv-audit-log-panel">
            <header><div><span class="ldv-panel-eyebrow">الخط الزمني</span><h2>أحدث العمليات</h2></div><small>{{ number_format($logs->total()) }} سجل</small></header>
            <div class="ldv-audit-list">
                @forelse($logs as $log)
                    @php($changedKeys = collect(array_keys(($log->old_values ?? []) + ($log->new_values ?? []))))
                    <details class="ldv-audit-event is-{{ $log->event }}" wire:key="audit-{{ $log->id }}">
                        <summary>
                            <span class="ldv-audit-event__icon">{{ $eventIcons[$log->event] ?? '•' }}</span>
                            <span class="ldv-audit-event__main"><strong>{{ $log->description }}</strong><small>{{ $log->subject_label ?: 'سجل #'.$log->auditable_id }}</small></span>
                            <span class="ldv-audit-event__module">{{ $log->module }}</span>
                            <span class="ldv-audit-event__user"><strong>{{ $log->user?->name ?: 'النظام' }}</strong><small>{{ $log->ip_address ?: 'بدون IP' }}</small></span>
                            <span class="ldv-audit-event__time"><strong>{{ $log->created_at?->format('Y-m-d H:i') }}</strong><small>{{ $log->created_at?->diffForHumans() }}</small></span>
                            <span class="ldv-audit-event__chevron">⌄</span>
                        </summary>
                        <div class="ldv-audit-event__details">
                            <div class="ldv-audit-meta">
                                <span><b>العملية</b>{{ $eventLabels[$log->event] ?? $log->event }}</span>
                                <span><b>نوع السجل</b>{{ class_basename($log->auditable_type ?: 'System') }} #{{ $log->auditable_id ?: '—' }}</span>
                                <span><b>الطلب</b>{{ $log->request_method ?: '—' }}</span>
                                <span><b>عنوان IP</b><span dir="ltr">{{ $log->ip_address ?: '—' }}</span></span>
                                <span class="is-wide"><b>الرابط</b><span dir="ltr">{{ $log->url ?: '—' }}</span></span>
                                <span class="is-wide"><b>الجهاز والمتصفح</b>{{ $log->user_agent ?: '—' }}</span>
                            </div>
                            @if($changedKeys->isNotEmpty())
                                <div class="ldv-audit-diff">
                                    <header><strong>تفاصيل التغييرات</strong><span>{{ $changedKeys->count() }} حقل</span></header>
                                    @foreach($changedKeys as $key)
                                        <div><b>{{ $fieldLabels[$key] ?? str_replace('_', ' ', $key) }}</b><span class="is-old">{{ $displayValue(($log->old_values ?? [])[$key] ?? null, $key) }}</span><i>←</i><span class="is-new">{{ $displayValue(($log->new_values ?? [])[$key] ?? null, $key) }}</span></div>
                                    @endforeach
                                </div>
                            @else
                                <div class="ldv-audit-no-diff">هذا الحدث لا يحتوي على تغييرات حقول، مثل تسجيل الدخول أو الخروج.</div>
                            @endif
                        </div>
                    </details>
                @empty
                    <div class="ldv-audit-empty"><span>✓</span><strong>السجل جاهز للمراقبة</strong><p>لا توجد عمليات مطابقة حاليًا. أي تغيير جديد في النظام سيظهر هنا تلقائيًا.</p></div>
                @endforelse
            </div>
            <div class="ldv-report-pagination">{{ $logs->links() }}</div>
        </section>
    </div>
</x-filament-panels::page>
