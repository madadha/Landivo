@php($total = $newOrders + $pendingReviews)
<div class="group relative me-3 flex items-center" title="{{ $total ? 'لديك إشعارات جديدة' : 'لا توجد إشعارات جديدة' }}">
    <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl() }}" aria-label="الطلبات الجديدة" class="relative grid h-9 w-9 place-items-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
        <x-filament::icon icon="heroicon-o-bell" class="h-5 w-5" />
        @if($total > 0)<span class="absolute -right-1 -top-1 min-w-4 rounded-full bg-red-600 px-1 text-center text-[10px] font-black leading-4 text-white ring-2 ring-white">{{ $total > 99 ? '99+' : $total }}</span>@endif
    </a>
    @if($total > 0)<span class="pointer-events-none absolute left-0 top-11 z-30 hidden w-max max-w-72 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs shadow-xl group-hover:block"><strong class="block">إشعارات جديدة</strong>{{ $newOrders }} طلب جديد · {{ $pendingReviews }} تقييم بانتظار المراجعة</span>@endif
</div>
