<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        :root {
            --guest-ink: #172033;
            --guest-muted: #64748b;
            --guest-primary: #2563eb;
            --guest-surface: #fff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            padding: clamp(10px, 4vw, 48px);
            display: grid;
            place-items: center;
            color: var(--guest-ink);
            font-family: 'Figtree', sans-serif;
            background:
                radial-gradient(ellipse at top left, #dbeafe 0, transparent 50%),
                radial-gradient(ellipse at bottom right, #e0e7ff 0, transparent 45%),
                #f1f5f9;
        }

        .guest-shell {
            width: 100%;
            max-width: 1120px;
            min-height: 680px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 28px;
            background: #fff;
            box-shadow:
                0 30px 80px -25px rgba(15, 23, 42, .25),
                0 8px 24px -12px rgba(15, 23, 42, .12);
        }

        /* Cột giới thiệu */
        .guest-brand {
            position: relative;
            isolation: isolate;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            padding: clamp(32px, 5vw, 64px);
            overflow: hidden;
            background:
                radial-gradient(circle at 0% 0%, rgba(96, 165, 250, .35), transparent 50%),
                linear-gradient(145deg, #1e40af, #172554 85%);
        }

        .guest-brand::before,
        .guest-brand::after {
            position: absolute;
            z-index: -1;
            content: '';
            pointer-events: none;
            border-radius: 50%;
        }

        .guest-brand::before {
            top: -140px;
            right: -140px;
            width: 360px;
            height: 360px;
            border: 1px solid rgba(255, 255, 255, .13);
            box-shadow:
                0 0 0 45px rgba(255, 255, 255, .035),
                0 0 0 90px rgba(255, 255, 255, .025);
        }

        .guest-brand::after {
            bottom: -180px;
            left: -100px;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle,
                    rgba(59, 130, 246, .28),
                    transparent 70%);
        }

        .guest-brand-content {
            position: relative;
            width: 100%;
            max-width: 360px;
            color: #fff;
        }

        .guest-brand-content>a {
            display: inline-flex;
            margin-bottom: 32px;
            border-radius: 22px;
        }

        .guest-logo {
            display: block;
            width: 80px;
            height: 80px;
            margin: 0;
            padding: 5px;
            object-fit: cover;
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 22px;
            background: rgba(255, 255, 255, .12);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .16);
        }

        .guest-brand h1 {
            margin: 0;
            font-size: clamp(36px, 4vw, 52px);
            font-weight: 700;
            line-height: 1.12;
            letter-spacing: -.045em;
            overflow-wrap: anywhere;
        }

        .guest-brand p {
            max-width: 320px;
            margin: 22px 0 0;
            color: #cbdcfb;
            font-size: 16px;
            line-height: 1.85;
        }

        /* Khu vực form */
        .guest-form-area {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            padding: clamp(24px, 4vw, 48px);
            background: linear-gradient(180deg, #fff, #f8fafc);
        }

        .guest-form-card {
            width: 100%;
            max-width: 440px;
            padding: clamp(24px, 3vw, 36px);
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            background: var(--guest-surface);
            box-shadow: 0 16px 40px -20px rgba(15, 23, 42, .22);
            overflow-wrap: anywhere;
        }

        .guest-form-card form {
            margin: 0;
        }

        .guest-form-card form>div+div {
            margin-top: 20px;
        }

        .guest-form-card h1,
        .guest-form-card h2 {
            margin: 0 0 12px;
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -.03em;
        }

        .guest-form-card label {
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
        }

        .guest-form-card label[for] {
            display: block;
            margin-bottom: 7px;
        }

        .guest-form-card input:not([type='checkbox']) :not([type='radio']) {
            font: inherit;
        }

        .guest-form-card input:not([type='checkbox']):not([type='radio']):not([type='hidden']) {
            display: block;
            width: 100%;
            min-width: 0;
            min-height: 48px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 11px;
            background: #f8fafc;
            color: #0f172a;
            font-family: inherit;
            font-size: 15px;
            line-height: 1.4;
            transition:
                border-color .18s ease,
                box-shadow .18s ease,
                background .18s ease;
        }

        .guest-form-card input::placeholder {
            color: #94a3b8;
        }

        .guest-form-card input:not([type='checkbox']):not([type='radio']):focus {
            outline: none;
            border-color: var(--guest-primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        }

        .guest-form-card input[aria-invalid='true'] {
            border-color: #dc2626;
        }

        .guest-form-card input[type='checkbox'],
        .guest-form-card input[type='radio'] {
            width: 17px;
            height: 17px;
            vertical-align: middle;
            accent-color: var(--guest-primary);
        }

        /* Nút submit của Breeze */
        .guest-form-card button[type='submit'],
        .guest-form-card button:not([type]),
        .guest-form-card a.guest-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 12px 24px;
            border: 1px solid transparent;
            border-radius: 11px;
            background: var(--guest-primary);
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            letter-spacing: 0;
            text-transform: none;
            text-decoration: none;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(37, 99, 235, .2);
            transition:
                background .18s ease,
                box-shadow .18s ease,
                transform .18s ease;
        }

        .guest-form-card button[type='submit']:hover,
        .guest-form-card button:not([type]):hover,
        .guest-form-card a.guest-button:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, .28);
        }

        .guest-form-card button:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        .guest-form-card a {
            color: #2563eb;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            text-underline-offset: 4px;
        }

        .guest-form-card a:hover {
            color: #1e40af;
            text-decoration: underline;
        }

        .guest-form-card a:focus-visible,
        .guest-form-card button:focus-visible,
        .guest-brand-content>a:focus-visible {
            outline: 3px solid #60a5fa;
            outline-offset: 4px;
        }

        /* Hàng chứa liên kết và nút của form Breeze */
        .guest-form-card form>.flex.items-center.justify-end {
            flex-wrap: wrap;
            gap: 14px;
        }

        .guest-form-card form>.flex.items-center.justify-end>button {
            margin-inline-start: 0;
        }

    </style>

</head>

<body>
    <main class="guest-shell">
        <section class="guest-brand">
            <div class="guest-brand-content">
                <a href="/">
                    <img class="guest-logo" src="{{ asset('images/logo.jpg') }}" alt="Logo">
                </a>
                <h1>Web Analyst</h1>
                <p>Quản lý cửa hàng đơn giản hơn, nhanh hơn và chuyên nghiệp hơn.</p>
            </div>
        </section>

        <section class="guest-form-area">
            <div class="guest-form-card">
                {{ $slot }}
            </div>
        </section>
    </main>
</body>

</html>
