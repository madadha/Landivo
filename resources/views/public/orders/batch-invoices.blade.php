<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        @font-face{font-family:Cairo;src:url(data:font/truetype;base64,{{ $fontRegular }}) format('truetype');font-weight:400}
        @font-face{font-family:Cairo;src:url(data:font/truetype;base64,{{ $fontBold }}) format('truetype');font-weight:700}
        @page{margin:14px 22px}
        *{box-sizing:border-box}
        body{margin:0;color:#172033;font-family:Cairo,DejaVu Sans,sans-serif;font-size:9px;line-height:1.45;direction:rtl}
        .invoice{page-break-after:always;position:relative}
        .invoice:last-child{page-break-after:auto}
        .header{padding:12px 16px;border-radius:12px;background:#12213f;color:#fff}
        .header-table,.data-table,.items-table{width:100%;border-collapse:collapse}
        .header-table td{vertical-align:top}
        .brand{font-size:18px;font-weight:700}.subtitle{color:#d8e2f3;font-size:8px}
        .invoice-meta{text-align:left;direction:ltr}.invoice-meta strong{display:block;font-size:12px}.invoice-meta span{color:#d8e2f3;font-size:8px}
        .status{display:inline-block;margin-top:5px;padding:3px 8px;border-radius:999px;background:#eef4ff;color:#254ca3;font-weight:700}
        .section{margin-top:8px;border:1px solid #e3e8f0;border-radius:10px;overflow:hidden;page-break-inside:avoid}
        .section-title{margin:0;padding:5px 9px;background:#f4f7fb;font-size:10px;font-weight:700}
        .data-table td{width:50%;padding:5px 9px;border-top:1px solid #eef1f5;vertical-align:top}
        .label{display:block;color:#667085;font-size:8px}.value{display:block;margin-top:2px;font-weight:700}
        .items-table th,.items-table td{padding:5px 8px;border-top:1px solid #e7ebf1;text-align:right}
        .items-table th{background:#f8fafc;color:#667085;font-size:8px}.items-table .number{text-align:left;direction:ltr}
        .notes{min-height:36px;padding:7px 9px;border-top:1px solid #e7ebf1;white-space:pre-wrap;word-break:break-word}
        .notes.is-empty{color:#98a2b3}
        .total-box{margin-top:8px;padding:8px 12px;border-radius:10px;background:#eef4ff;text-align:left;direction:ltr;page-break-inside:avoid}
        .total-box span{color:#52627a;font-size:8px}.total-box strong{display:block;color:#12213f;font-size:16px}
        .footer{margin-top:7px;padding-top:5px;border-top:1px solid #e6eaf0;color:#98a2b3;text-align:center;font-size:7px;page-break-inside:avoid}
    </style>
</head>
<body>
@php($arabic = new \ArPHP\I18N\Arabic)
@php($rtl = fn (mixed $text): string => $arabic->utf8Glyphs((string) $text, 500, false, false))
@foreach($orders as $order)
    <main class="invoice">
        <header class="header">
            <table class="header-table"><tr>
                <td><div class="brand">{{ $rtl($order->account?->name ?? 'Landivo') }}</div><div class="subtitle">{{ $rtl('فاتورة طلب') }} / Order Invoice</div>@if($order->status)<span class="status">{{ $rtl($order->status->name_ar) }}</span>@endif</td>
                <td class="invoice-meta"><strong>{{ $order->order_number }}</strong><span>{{ $order->created_at?->format('Y-m-d H:i') }}</span></td>
            </tr></table>
        </header>

        <section class="section">
            <h2 class="section-title">{{ $rtl('بيانات العميل') }}</h2>
            <table class="data-table">
                <tr><td><span class="label">{{ $rtl('الاسم الكامل') }}</span><span class="value">{{ $rtl($order->customer?->name ?: 'غير مسجل') }}</span></td><td><span class="label">{{ $rtl('رقم الهاتف') }}</span><span class="value" dir="ltr">{{ $order->customer?->phone ?: $rtl('غير مسجل') }}</span></td></tr>
                <tr><td><span class="label">{{ $rtl('البريد الإلكتروني') }}</span><span class="value" dir="ltr">{{ $order->customer?->email ?: $rtl('غير مسجل') }}</span></td><td><span class="label">{{ $rtl('المدينة / الإمارة') }}</span><span class="value">{{ $rtl($order->customer?->city ?: 'غير مسجلة') }}</span></td></tr>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">{{ $rtl('تفاصيل الطلب') }}</h2>
            <table class="items-table">
                <thead><tr><th>{{ $rtl('المنتج / العرض') }}</th><th>{{ $rtl('الكمية') }}</th><th>{{ $rtl('سعر الوحدة') }}</th><th>{{ $rtl('الإجمالي') }}</th></tr></thead>
                <tbody>
                @forelse($order->items as $item)
                    <tr><td>{{ $rtl($item->product_name) }}</td><td class="number">{{ $item->quantity }}</td><td class="number">{{ number_format((float) $item->unit_price, 2) }}</td><td class="number">{{ number_format((float) $item->total, 2) }} {{ $order->currency }}</td></tr>
                @empty
                    <tr><td colspan="4">{{ $rtl('لا توجد عناصر مسجلة لهذا الطلب.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">{{ $rtl('ملاحظات الطلب') }}</h2>
            <div class="notes {{ filled($order->notes) ? '' : 'is-empty' }}">{{ $rtl(filled($order->notes) ? $order->notes : 'لا توجد ملاحظات مسجلة لهذا الطلب.') }}</div>
        </section>

        <div class="total-box"><span>{{ $rtl('الإجمالي النهائي') }}</span><strong>{{ number_format((float) $order->total, 2) }} {{ $order->currency }}</strong></div>
        <footer class="footer">{{ $rtl('تم إنشاء هذه الفاتورة من نظام Landivo بتاريخ') }} {{ now()->format('Y-m-d H:i') }}</footer>
    </main>
@endforeach
</body>
</html>
