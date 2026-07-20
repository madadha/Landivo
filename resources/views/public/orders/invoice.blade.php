<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}?v={{ filemtime(public_path('css/fonts.css')) }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $order->order_number }}</title>
    <style>body{margin:0;background:#f4f7fb;color:#172033;font-family:Arial,sans-serif}.invoice{width:min(100% - 32px,720px);margin:32px auto;background:#fff;border-radius:20px;padding:32px;box-shadow:0 18px 45px #17203314}.head{display:flex;justify-content:space-between;gap:20px;border-bottom:1px solid #e5e7eb;padding-bottom:20px}.muted{color:#667085}.customer,.items{margin-top:24px;padding:18px;border:1px solid #e5e7eb;border-radius:14px}.row{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #f0f2f5}.row:last-child{border-bottom:0}.total{margin-top:22px;text-align:end;font-size:22px;font-weight:800;color:#2563eb}.print{margin-top:24px;border:0;border-radius:10px;padding:12px 18px;background:#2563eb;color:#fff;font-weight:700;cursor:pointer}@media print{body{background:#fff}.invoice{width:auto;margin:0;box-shadow:none}.print{display:none}}</style>
</head>
<body>
<main class="invoice">
    <div class="head"><div><h1>{{ $order->account?->name ?? 'Landivo' }}</h1><p class="muted">فاتورة طلب</p></div><div><strong>{{ $order->order_number }}</strong><p class="muted">{{ $order->created_at?->format('Y-m-d H:i') }}</p></div></div>
    <section class="customer"><div class="row"><span>العميل</span><strong>{{ $order->customer?->name }}</strong></div><div class="row"><span>الهاتف</span><strong dir="ltr">{{ $order->customer?->phone }}</strong></div>@if($order->customer?->city)<div class="row"><span>المدينة</span><strong>{{ $order->customer->city }}</strong></div>@endif</section>
    <section class="items"><h2>تفاصيل الطلب</h2>@foreach($order->items as $item)<div class="row"><span>{{ $item->product_name }} × {{ $item->quantity }}</span><strong>{{ number_format((float) $item->total, 2) }} {{ $order->currency }}</strong></div>@endforeach</section>
    <div class="total">الإجمالي: {{ number_format((float) $order->total, 2) }} {{ $order->currency }}</div>
    <button class="print" onclick="window.print()">طباعة / حفظ PDF</button>
</main>
</body>
</html>
