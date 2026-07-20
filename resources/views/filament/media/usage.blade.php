<div style="display:grid;gap:14px">
    @if($record->usage_count > 0)
        <div style="padding:12px 14px;border-radius:12px;background:#ecfdf3;color:#067647;font-weight:800">هذا الملف مستخدم في {{ $record->usage_count }} موضع، ولذلك الحذف الآمن معطل.</div>
        @foreach((array) $record->usage_locations as $usage)
            <div style="padding:14px;border:1px solid #e2e8f0;border-radius:14px;background:#fff">
                <strong style="display:block;color:#172033">{{ $usage['label'] ?? 'موضع استخدام' }}</strong>
                <small style="color:#667085">{{ $usage['type'] ?? '' }} #{{ $usage['id'] ?? '' }} — الحقل: {{ $usage['field'] ?? '' }}</small>
            </div>
        @endforeach
    @else
        <div style="padding:14px;border-radius:12px;background:#fff7ed;color:#9a3412;font-weight:800">الملف غير مستخدم حاليًا ويمكن حذفه بأمان بعد المراجعة.</div>
    @endif
    <div style="padding:14px;border-radius:14px;background:#f8fafc;word-break:break-all">
        <b>المسار:</b> {{ $record->path }}<br><b>الرابط:</b> {{ $record->public_url }}
    </div>
</div>
