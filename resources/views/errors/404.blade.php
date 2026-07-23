@php
    $account = null;

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('accounts')) {
            $account = \App\Models\Account::query()->first();
        }
    } catch (\Throwable) {
        // The error page must remain available even when the database is unavailable.
    }

    $settings = (array) ($account?->settings ?? []);
    $requestedLocale = request()->hasSession()
        ? request()->session()->get('locale', app()->getLocale())
        : app()->getLocale();
    $isArabic = $requestedLocale !== 'en';
    $companyName = $account?->name ?: config('app.name', 'Landivo');
    $logoUrl = $account?->logo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($account->logo_path)
        : null;
    $faviconUrl = $account?->favicon_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($account->favicon_path)
        : null;
    $homeLabel = $isArabic ? 'العودة إلى الرئيسية' : 'Back to home';
    $productsLabel = $isArabic ? 'تصفّح المنتجات' : 'Browse products';
    $backLabel = $isArabic ? 'العودة للصفحة السابقة' : 'Go back';
@endphp
<!doctype html>
<html lang="{{ $isArabic ? 'ar' : 'en' }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $isArabic ? 'الصفحة غير موجودة' : 'Page not found' }} | {{ $companyName }}</title>
    @if($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    <style>
        @font-face {
            font-family: "Cairo";
            src: url("{{ asset('fonts/cairo/Cairo-Regular.ttf') }}") format("truetype");
            font-style: normal;
            font-weight: 400;
            font-display: swap;
        }

        @font-face {
            font-family: "Cairo";
            src: url("{{ asset('fonts/cairo/Cairo-Bold.ttf') }}") format("truetype");
            font-style: normal;
            font-weight: 700;
            font-display: swap;
        }

        :root {
            color-scheme: light;
            --ink: #0f1a31;
            --muted: #68738a;
            --primary: {{ $settings['primary_color'] ?? '#94a80c' }};
            --primary-deep: #75870a;
            --line: rgba(15, 26, 49, .1);
            --surface: rgba(255, 255, 255, .86);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 18%, rgba(148, 168, 12, .15), transparent 27rem),
                radial-gradient(circle at 88% 82%, rgba(26, 71, 142, .11), transparent 30rem),
                #f5f7fb;
            font-family: "Cairo", system-ui, sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .error-shell {
            position: relative;
            isolation: isolate;
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 28px;
        }

        .error-shell::before,
        .error-shell::after {
            position: fixed;
            z-index: -1;
            width: 320px;
            height: 320px;
            border: 1px solid rgba(148, 168, 12, .18);
            border-radius: 50%;
            content: "";
        }

        .error-shell::before {
            inset-block-start: -190px;
            inset-inline-end: -90px;
        }

        .error-shell::after {
            inset-block-end: -210px;
            inset-inline-start: -80px;
        }

        .error-card {
            width: min(100%, 1020px);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .75);
            border-radius: 32px;
            background: var(--surface);
            box-shadow: 0 30px 90px rgba(15, 26, 49, .13);
            backdrop-filter: blur(20px);
        }

        .error-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 86px;
            padding: 18px 28px;
            border-bottom: 1px solid var(--line);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }

        .brand img {
            display: block;
            width: auto;
            max-width: 170px;
            height: 50px;
            object-fit: contain;
        }

        .brand-mark {
            display: grid;
            width: 46px;
            height: 46px;
            place-items: center;
            border-radius: 15px;
            color: #fff;
            background: var(--ink);
            box-shadow: 0 10px 25px rgba(15, 26, 49, .18);
        }

        .language {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 8px 16px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--muted);
            background: rgba(255, 255, 255, .72);
            font-size: 13px;
            font-weight: 700;
            transition: .2s ease;
        }

        .language:hover {
            color: var(--ink);
            border-color: rgba(148, 168, 12, .45);
            transform: translateY(-1px);
        }

        .error-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, .8fr);
            align-items: center;
            gap: 48px;
            padding: 68px clamp(28px, 7vw, 76px) 76px;
        }

        [dir="ltr"] .error-content {
            grid-template-columns: minmax(320px, .8fr) minmax(0, 1fr);
        }

        [dir="ltr"] .error-copy {
            order: 2;
        }

        .error-kicker {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 16px;
            color: var(--primary-deep);
            font-size: 13px;
            font-weight: 700;
        }

        .error-kicker::before {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 0 7px rgba(148, 168, 12, .12);
            content: "";
        }

        h1 {
            max-width: 580px;
            margin: 0;
            font-size: clamp(34px, 5vw, 60px);
            line-height: 1.2;
            letter-spacing: -.035em;
        }

        .error-copy p {
            max-width: 610px;
            margin: 20px 0 0;
            color: var(--muted);
            font-size: clamp(15px, 2vw, 18px);
            line-height: 1.95;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 34px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 12px 22px;
            border: 1px solid transparent;
            border-radius: 15px;
            font: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .button:hover {
            transform: translateY(-2px);
        }

        .button-primary {
            color: #fff;
            background: var(--ink);
            box-shadow: 0 14px 30px rgba(15, 26, 49, .2);
        }

        .button-secondary {
            color: var(--ink);
            border-color: var(--line);
            background: #fff;
        }

        .button-link {
            padding-inline: 8px;
            color: var(--muted);
            background: transparent;
        }

        .error-visual {
            position: relative;
            display: grid;
            min-height: 310px;
            place-items: center;
        }

        .error-code {
            color: var(--ink);
            font-size: clamp(124px, 20vw, 230px);
            font-weight: 700;
            line-height: 1;
            letter-spacing: -.11em;
            text-shadow: 0 25px 55px rgba(15, 26, 49, .12);
        }

        .error-zero {
            display: inline-grid;
            width: .69em;
            height: .69em;
            margin-inline: .02em .06em;
            place-items: center;
            border-radius: 50%;
            color: #fff;
            background: var(--primary);
            font-size: .72em;
            vertical-align: .11em;
            box-shadow: inset 0 0 0 12px rgba(255, 255, 255, .16), 0 18px 40px rgba(148, 168, 12, .28);
        }

        .error-zero svg {
            width: 44%;
            height: 44%;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.8;
        }

        .visual-note {
            position: absolute;
            inset-block-end: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--muted);
            background: rgba(255, 255, 255, .85);
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 10px 26px rgba(15, 26, 49, .08);
        }

        .visual-note span {
            color: var(--primary-deep);
        }

        @media (max-width: 800px) {
            .error-shell {
                padding: 14px;
            }

            .error-card {
                border-radius: 24px;
            }

            .error-nav {
                min-height: 72px;
                padding: 13px 18px;
            }

            .brand img {
                max-width: 130px;
                height: 42px;
            }

            .error-content,
            [dir="ltr"] .error-content {
                grid-template-columns: 1fr;
                gap: 26px;
                padding: 38px 24px 44px;
            }

            [dir="ltr"] .error-copy {
                order: initial;
            }

            .error-copy {
                text-align: center;
            }

            .error-kicker,
            .error-actions {
                justify-content: center;
            }

            .error-copy p {
                margin-inline: auto;
            }

            .error-visual {
                grid-row: 1;
                min-height: 190px;
            }

            .error-code {
                font-size: clamp(112px, 42vw, 170px);
            }

            .visual-note {
                inset-block-end: -2px;
            }
        }

        @media (max-width: 500px) {
            .error-actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
            }

            .button-link {
                min-height: 42px;
            }
        }
    </style>
</head>
<body>
<div class="error-shell">
    <section class="error-card" aria-labelledby="not-found-title">
        <nav class="error-nav" aria-label="{{ $isArabic ? 'التنقل' : 'Navigation' }}">
            <a class="brand" href="{{ url('/') }}">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}">
                @else
                    <span class="brand-mark">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($companyName, 0, 1)) }}</span>
                    <span>{{ $companyName }}</span>
                @endif
            </a>
            <a class="language" href="{{ route('locale.switch', $isArabic ? 'en' : 'ar') }}">
                {{ $isArabic ? 'English' : 'العربية' }}
            </a>
        </nav>

        <div class="error-content">
            <div class="error-copy">
                <span class="error-kicker">{{ $isArabic ? 'يبدو أنك وصلت إلى طريق غير موجود' : 'It looks like this route does not exist' }}</span>
                <h1 id="not-found-title">{{ $isArabic ? 'عذرًا، لم نجد الصفحة التي تبحث عنها' : 'Sorry, we could not find that page' }}</h1>
                <p>
                    {{ $isArabic
                        ? 'قد يكون الرابط قد تغيّر أو أن الصفحة لم تعد متاحة. يمكنك العودة إلى الصفحة الرئيسية أو متابعة تصفّح منتجاتنا.'
                        : 'The link may have changed or the page may no longer be available. You can return home or continue browsing our products.' }}
                </p>
                <div class="error-actions">
                    <a class="button button-primary" href="{{ url('/') }}">{{ $homeLabel }}</a>
                    <a class="button button-secondary" href="{{ url('/products') }}">{{ $productsLabel }}</a>
                    <button class="button button-link" type="button" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">{{ $backLabel }}</button>
                </div>
            </div>

            <div class="error-visual" aria-hidden="true">
                <div class="error-code">4<span class="error-zero"><svg viewBox="0 0 24 24"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/><path d="m15.5 8.5-2 5-5 2 2-5 5-2Z"/></svg></span>4</div>
                <div class="visual-note"><span>404</span> {{ $isArabic ? 'صفحة غير موجودة' : 'Page not found' }}</div>
            </div>
        </div>
    </section>
</div>
</body>
</html>
