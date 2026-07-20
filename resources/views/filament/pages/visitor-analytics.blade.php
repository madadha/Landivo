<x-filament-panels::page>
    <div wire:poll.30s="refreshAnalytics" class="space-y-6" dir="rtl">
        <section class="overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-bold text-emerald-300"><span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span> تحليلات مباشرة</div>
                    <h1 class="text-2xl font-black tracking-tight sm:text-3xl">تحليلات الزوار</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">راقب أداء صفحات الهبوط والمنتجات، وتابع سلوك الزوار لحظة بلحظة.</p>
                </div>
                <div class="flex items-center gap-3">
                    <select wire:model.live="period" class="rounded-xl border-white/10 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white outline-none">
                        <option class="text-slate-900" value="7">آخر 7 أيام</option>
                        <option class="text-slate-900" value="30">آخر 30 يوماً</option>
                        <option class="text-slate-900" value="90">آخر 90 يوماً</option>
                        <option class="text-slate-900" value="365">آخر سنة</option>
                    </select>
                    <button type="button" wire:click="refreshAnalytics" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-900 transition hover:bg-emerald-100">تحديث</button>
                </div>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach([
                ['key' => 'online', 'label' => 'زوار الآن', 'hint' => 'آخر 5 دقائق', 'icon' => '●', 'class' => 'bg-emerald-50 text-emerald-700'],
                ['key' => 'visits', 'label' => 'إجمالي الزيارات', 'hint' => 'مشاهدات الصفحة', 'icon' => '↗', 'class' => 'bg-blue-50 text-blue-700'],
                ['key' => 'unique_visitors', 'label' => 'زوار فريدون', 'hint' => 'جلسات مختلفة', 'icon' => '◉', 'class' => 'bg-violet-50 text-violet-700'],
                ['key' => 'landing_pages', 'label' => 'صفحات نشطة', 'hint' => 'صفحات الهبوط', 'icon' => '▣', 'class' => 'bg-amber-50 text-amber-700'],
                ['key' => 'products', 'label' => 'منتجات مشاهدة', 'hint' => 'ضمن المدة المحددة', 'icon' => '◇', 'class' => 'bg-rose-50 text-rose-700'],
            ] as $stat)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between"><span class="rounded-xl px-3 py-2 text-lg font-black {{ $stat['class'] }}">{{ $stat['icon'] }}</span><span class="text-2xl font-black text-slate-900">{{ number_format($stats[$stat['key']] ?? 0) }}</span></div>
                    <p class="mt-4 font-bold text-slate-900">{{ $stat['label'] }}</p><p class="mt-1 text-xs text-slate-500">{{ $stat['hint'] }}</p>
                </div>
            @endforeach
        </div>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5 flex items-center justify-between"><div><h2 class="text-lg font-black text-slate-900">الزوار الحاليون</h2><p class="mt-1 text-xs text-slate-500">يتم تحديث القائمة تلقائياً كل 30 ثانية</p></div><span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">{{ number_format($stats['online'] ?? 0) }} متصل الآن</span></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-right text-sm"><thead><tr class="border-b border-slate-100 text-xs text-slate-500"><th class="px-3 py-3 font-semibold">الصفحة</th><th class="px-3 py-3 font-semibold">المصدر</th><th class="px-3 py-3 font-semibold">الجهاز</th><th class="px-3 py-3 font-semibold">آخر نشاط</th></tr></thead><tbody>@forelse($currentVisitors as $visitor)<tr class="border-b border-slate-50 last:border-0"><td class="px-3 py-3 font-bold text-slate-800">{{ $visitor['path'] }}</td><td class="px-3 py-3 text-slate-500">{{ $visitor['location'] }}</td><td class="px-3 py-3"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $visitor['device'] }}</span></td><td class="px-3 py-3 text-slate-500">{{ $visitor['last_seen'] }}</td></tr>@empty<tr><td colspan="4" class="px-3 py-10 text-center text-sm text-slate-500">لا يوجد زوار نشطون خلال آخر 5 دقائق.</td></tr>@endforelse</tbody></table></div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><div class="mb-5 flex items-center justify-between"><h2 class="text-lg font-black text-slate-900">أكثر صفحات الهبوط زيارة</h2><span class="text-xs text-slate-500">آخر {{ $period }} يوماً</span></div><div class="space-y-3">@forelse($topLandingPages as $page)<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-sm font-black text-blue-700">{{ $loop->iteration }}</span><div class="min-w-0 flex-1"><p class="truncate font-bold text-slate-800">{{ $page['name'] }}</p><p class="truncate text-xs text-slate-500">/l/{{ $page['slug'] }}</p></div><strong class="text-blue-700">{{ number_format($page['visits']) }}</strong></div>@empty<p class="py-8 text-center text-sm text-slate-500">لا توجد بيانات بعد.</p>@endforelse</div></section>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><div class="mb-5 flex items-center justify-between"><h2 class="text-lg font-black text-slate-900">أكثر المنتجات مشاهدة</h2><span class="text-xs text-slate-500">حسب الزيارات</span></div><div class="space-y-3">@forelse($topProducts as $product)<div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-sm font-black text-emerald-700">{{ $loop->iteration }}</span><p class="min-w-0 flex-1 truncate font-bold text-slate-800">{{ $product['name'] }}</p><strong class="text-emerald-700">{{ number_format($product['visits']) }}</strong></div>@empty<p class="py-8 text-center text-sm text-slate-500">لا توجد بيانات بعد.</p>@endforelse</div></section>
        </div>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm"><div class="mb-5 flex items-center justify-between"><h2 class="text-lg font-black text-slate-900">الزيارات اليومية</h2><span class="text-xs text-slate-500">{{ $period }} يوماً</span></div><div class="flex min-h-52 items-end gap-2 overflow-x-auto pb-2">@forelse($dailyVisits as $day)@php($maxVisits = max(1, collect($dailyVisits)->max('visits')))<div class="flex min-w-10 flex-col items-center gap-2"><span class="text-xs font-bold text-slate-600">{{ $day['visits'] }}</span><div class="w-7 rounded-t-lg bg-gradient-to-t from-blue-600 to-cyan-400" style="height:{{ max(8, (int) (($day['visits'] / $maxVisits) * 150)) }}px"></div><span class="text-[10px] text-slate-500">{{ $day['day'] }}</span></div>@empty<p class="w-full py-12 text-center text-sm text-slate-500">سيظهر الرسم بعد استقبال الزيارات.</p>@endforelse</div></section>
    </div>
</x-filament-panels::page>
