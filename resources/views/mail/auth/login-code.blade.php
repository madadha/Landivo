<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>رمز تسجيل الدخول</title>
</head>
<body style="margin:0;background:#f4f7fb;color:#172033;font-family:Tahoma,Arial,sans-serif">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;background:#f4f7fb">
    <tr><td align="center" style="padding:32px 14px">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;max-width:560px;overflow:hidden;border:1px solid #e5eaf2;border-radius:20px;background:#fff;box-shadow:0 18px 45px rgba(23,32,51,.08)">
            <tr><td align="center" style="padding:25px 28px;background:#12213f;color:#fff;font-size:22px;font-weight:800">{{ $companyName }}</td></tr>
            <tr><td style="padding:30px 32px;text-align:right">
                <h1 style="margin:0 0 16px;font-size:22px">مرحبًا{{ filled($userName) ? ' '.$userName : '' }}،</h1>
                <p style="margin:0 0 18px;color:#52627a;font-size:15px;line-height:1.9">استخدم رمز التحقق التالي لإكمال تسجيل الدخول إلى لوحة التحكم:</p>
                <div dir="ltr" style="margin:22px 0;border:1px dashed #cbd5e1;border-radius:14px;background:#f8fafc;padding:18px;text-align:center;color:#12213f;font-size:34px;font-weight:900;letter-spacing:8px">{{ $code }}</div>
                <p style="margin:0;color:#667085;font-size:14px;line-height:1.8">تنتهي صلاحية هذا الرمز خلال {{ $expiryMinutes }} دقائق. إذا لم تطلب تسجيل الدخول، تجاهل هذه الرسالة.</p>
                <p style="margin:24px 0 0;color:#344054;font-size:14px;line-height:1.8">مع التحية،<br><strong>{{ $companyName }}</strong></p>
            </td></tr>
            <tr><td align="center" style="border-top:1px solid #edf0f5;padding:17px 24px;color:#98a2b3;font-size:12px">جميع الحقوق محفوظة © {{ now()->year }} {{ $companyName }}</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
