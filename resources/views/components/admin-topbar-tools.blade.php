<div class="ldv-topbar-tools">
    <div class="ldv-topbar-theme" title="مظهر لوحة التحكم">
        <x-filament-panels::theme-switcher />
    </div>

    @include('components.admin-order-notification', [
        'newOrders' => $newOrders,
        'ordersUrl' => $ordersUrl,
    ])
</div>
