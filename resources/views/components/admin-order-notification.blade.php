<a class="ldv-new-orders" href="{{ $ordersUrl }}" title="عرض الطلبات الجديدة" aria-label="الطلبات الجديدة: {{ $newOrders }}">
    <span class="ldv-new-orders__icon">
        <x-filament::icon icon="heroicon-o-bell" />
        @if($newOrders > 0)<b>{{ $newOrders > 99 ? '99+' : $newOrders }}</b>@endif
    </span>
    <span class="ldv-new-orders__copy"><small>الطلبات الجديدة</small><strong>{{ number_format($newOrders) }}</strong></span>
</a>
