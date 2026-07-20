<x-filament-panels::page>
    <div dir="rtl" class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-slate-950 p-5 text-white shadow-sm"><p class="text-sm text-slate-300">إجمالي الطلبات</p><strong class="mt-2 block text-3xl">{{ number_format($total) }}</strong></div>
            <div class="rounded-2xl bg-emerald-600 p-5 text-white shadow-sm"><p class="text-sm text-emerald-100">إجمالي المبيعات</p><strong class="mt-2 block text-3xl">{{ number_format($revenue, 2) }} <small class="text-base">AED</small></strong></div>
            <div class="rounded-2xl bg-amber-500 p-5 text-white shadow-sm"><p class="text-sm text-amber-100">الحالات المعرفة</p><strong class="mt-2 block text-3xl">{{ number_format($rows->count()) }}</strong></div>
        </div>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <header class="border-b border-gray-100 px-5 py-4"><h2 class="text-lg font-black text-slate-900">توزيع الطلبات حسب الحالة</h2><p class="mt-1 text-sm text-slate-500">إحصائية شاملة للحالات والأرشفة وقيمة المبيعات.</p></header>
            <div class="divide-y divide-gray-100">
                @forelse($rows as $status)
                    @php($count = (int) $status->orders_count)
                    @php($percent = $total > 0 ? round($count / $total * 100, 1) : 0)
                    <div class="grid gap-3 px-5 py-4 md:grid-cols-[minmax(180px,1fr)_2fr_100px_120px] md:items-center">
                        <div class="flex items-center gap-3"><span class="h-3 w-3 rounded-full" style="background:{{ $status->color ?: '#64748b' }}"></span><div><strong class="block text-slate-900">{{ $status->name_ar }}</strong><span class="text-xs text-slate-500">{{ $status->name_en }} · {{ $status->archived_at ? 'مؤرشفة' : 'نشطة' }}</span></div></div>
                        <div><div class="h-2 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full" style="width:{{ max($count ? 3 : 0, $percent) }}%;background:{{ $status->color ?: '#64748b' }}"></span></div><span class="mt-1 block text-xs text-slate-500">{{ $percent }}%</span></div>
                        <strong class="text-slate-900">{{ number_format($count) }} طلب</strong>
                        <span class="font-bold text-slate-700">{{ number_format((float) ($status->revenue_total ?? 0), 2) }} AED</span>
                    </div>
                @empty
                    <p class="px-5 py-12 text-center text-sm text-slate-500">لا توجد حالات طلبات بعد.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
