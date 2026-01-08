<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('img/BPSLogo.png') }}">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="theme-color" content="{{ ($settings->theme_colors ?? [])['primary'] ?? '#20246b' }}" />
    <title>@yield('code') • @yield('message') | BPS LMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Libre+Baskerville:wght@400;700&display=swap"
        rel="stylesheet">
    @php
        $primaryColor = ($settings->theme_colors ?? [])['primary'] ?? '#20246b';
        // Convert hex to RGB for rgba() usage
        $hex = ltrim($primaryColor, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    @endphp
    <style>
        :root {
            --blue: {{ $primaryColor }};
            --blue-rgb: {{ $r }}, {{ $g }}, {{ $b }};
            --ink: #ffffff;
            --ink-dim: rgba(255, 255, 255, .88);
            --muted: rgba(255, 255, 255, .66);
            --panel: color-mix(in srgb, {{ $primaryColor }} 45%, transparent);
            --panel-2: color-mix(in srgb, {{ $primaryColor }} 60%, transparent);
            --edge: rgba(255, 255, 255, .12);
            --shadow: rgba(var(--blue-rgb), .35);
        }

        * {
            box-sizing: border-box
        }

        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            color: var(--ink);
            background:
                radial-gradient(1200px 700px at 80% -10%, rgba(255, 255, 255, .06) 0%, rgba(255, 255, 255, 0) 55%),
                radial-gradient(900px 600px at 10% 110%, rgba(255, 255, 255, .04) 0%, rgba(255, 255, 255, 0) 55%),
                linear-gradient(180deg, var(--blue) 0%, color-mix(in srgb, var(--blue) 96%, transparent) 100%);
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji",
                "Segoe UI Emoji", "Segoe UI Symbol";
            line-height: 1.6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .wrap {
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 42px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .wrap {
                grid-template-columns: 1fr;
                gap: 28px
            }
        }

        .card {
            background: linear-gradient(180deg, rgba(255, 255, 255, .06), rgba(255, 255, 255, .03)), linear-gradient(180deg,
                    var(--panel), var(--panel-2));
            border: 1px solid var(--edge);
            border-radius: 18px;
            backdrop-filter: blur(4px);
            box-shadow: 0 10px 30px var(--shadow), inset 0 1px 0 rgba(255, 255, 255, .06);
            padding: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
        }

        .brand img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .brand span {
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: 18px;
            letter-spacing: .3px;
            color: var(--ink-dim);
        }

        h1 {
            margin: 6px 0 8px;
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: clamp(28px, 4.2vw, 40px);
            line-height: 1.2;
            color: var(--ink);
            text-shadow: 0 2px 0 rgba(var(--blue-rgb), .25);
        }

        .lede {
            color: var(--ink-dim);
            margin-bottom: 18px;
        }

        .hint {
            color: var(--muted);
            font-size: 14px;
            margin-top: 14px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }

        .btn {
            appearance: none;
            border: 1px solid rgba(255, 255, 255, .35);
            color: var(--ink);
            background: linear-gradient(180deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, .06));
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: .2s ease;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, .6)
        }

        .btn:active {
            transform: translateY(0) scale(.99)
        }

        .btn.primary {
            background: var(--ink);
            color: var(--blue);
            border-color: rgba(255, 255, 255, .9);
            box-shadow: 0 6px 16px rgba(var(--blue-rgb), .25), inset 0 1px 0 rgba(var(--blue-rgb), .12);
        }

        .btn.primary:hover {
            filter: brightness(1.05)
        }

        /* Library scene */
        .scene {
            position: relative;
            width: 100%;
            aspect-ratio: 4 / 3;
            min-height: 300px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: inset 0 8px 40px rgba(var(--blue-rgb), .55), 0 10px 30px rgba(var(--blue-rgb), .45);
            background:
                radial-gradient(120% 85% at 80% 10%, rgba(255, 255, 255, .08), rgba(255, 255, 255, 0) 60%),
                linear-gradient(180deg, rgba(var(--blue-rgb), .95) 0%, rgba(var(--blue-rgb), 1) 100%);
            border: 1px solid var(--edge);
        }

        /* Shelves */
        .shelf {
            position: absolute;
            left: 0;
            right: 0;
            height: 16%;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .14), rgba(255, 255, 255, .04) 24%),
                linear-gradient(180deg, rgba(var(--blue-rgb), .9), rgba(var(--blue-rgb), 1));
            box-shadow: inset 0 -2px 0 rgba(255, 255, 255, .08);
            border-top: 1px solid rgba(255, 255, 255, .12);
        }

        .s1 {
            top: 20%
        }

        .s2 {
            top: 44%
        }

        .s3 {
            top: 68%
        }

        /* Books */
        .books {
            position: absolute;
            inset: 0;
        }

        .book {
            position: absolute;
            bottom: calc(100% - 10px);
            width: 22px;
            height: 80px;
            border-radius: 3px 3px 2px 2px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .12), rgba(255, 255, 255, .02)), rgba(var(--blue-rgb), .95);
            box-shadow: 2px 3px 0 rgba(var(--blue-rgb), .6), inset 0 0 0 2px rgba(255, 255, 255, .05);
        }

        .book::after {
            content: "";
            position: absolute;
            left: 4px;
            right: 4px;
            top: 10px;
            height: 2px;
            background: rgba(255, 255, 255, .18);
            box-shadow: 0 8px 0 rgba(255, 255, 255, .14), 0 16px 0 rgba(255, 255, 255, .1);
            opacity: .8;
        }

        .b1 {
            background: linear-gradient(180deg, rgba(255, 255, 255, .14), rgba(255, 255, 255, .03)), rgba(var(--blue-rgb), .92)
        }

        .b2 {
            background: linear-gradient(180deg, rgba(255, 255, 255, .10), rgba(255, 255, 255, .02)), rgba(var(--blue-rgb), .98)
        }

        .b3 {
            background: linear-gradient(180deg, rgba(255, 255, 255, .18), rgba(255, 255, 255, .04)), rgba(var(--blue-rgb), .88)
        }

        .b4 {
            background: linear-gradient(180deg, rgba(255, 255, 255, .09), rgba(255, 255, 255, .02)), rgba(var(--blue-rgb), 1)
        }

        .b5 {
            background: linear-gradient(180deg, rgba(255, 255, 255, .2), rgba(255, 255, 255, .05)), rgba(var(--blue-rgb), .85)
        }

        /* Hanging sign */
        .sign {
            position: absolute;
            left: 50%;
            top: 8%;
            transform-origin: top center;
            transform: translateX(-50%) rotate(0deg);
            width: 240px;
            height: 130px;
            filter: drop-shadow(0 8px 12px rgba(var(--blue-rgb), .6));
            animation: swing 4s ease-in-out infinite;
        }

        .rope {
            position: absolute;
            left: 50%;
            top: -38px;
            width: 2px;
            height: 42px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .9), rgba(255, 255, 255, .7));
            transform: translateX(-50%);
            box-shadow: 0 0 0 1px rgba(var(--blue-rgb), .35);
        }

        .board {
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 1), rgba(255, 255, 255, .92));
            border-radius: 10px;
            border: 1px solid rgba(var(--blue-rgb), .25);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .board::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 10px;
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, .8), inset 0 -2px 10px rgba(var(--blue-rgb), .18);
            pointer-events: none;
        }

        .sign h2 {
            margin: 0;
            font-family: "Libre Baskerville", Georgia, serif;
            font-size: 30px;
            color: var(--blue);
            letter-spacing: 2px;
        }

        .sign small {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
            color: rgba(var(--blue-rgb), .9);
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 12px;
        }

        @keyframes swing {

            0%,
            100% {
                transform: translateX(-50%) rotate(-3.2deg)
            }

            50% {
                transform: translateX(-50%) rotate(3.2deg)
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .sign {
                animation: none
            }
        }

        /* Dust motes */
        .dust {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .mote {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(255, 255, 255, .7);
            border-radius: 50%;
            filter: blur(.3px);
            animation: drift linear infinite;
            opacity: .7;
        }

        .m1 {
            left: 10%;
            top: 70%;
            animation-duration: 9s
        }

        .m2 {
            left: 30%;
            top: 50%;
            animation-duration: 12s;
            width: 2px;
            height: 2px;
            opacity: .55
        }

        .m3 {
            left: 65%;
            top: 60%;
            animation-duration: 10s
        }

        .m4 {
            left: 82%;
            top: 40%;
            animation-duration: 13s;
            width: 2px;
            height: 2px;
            opacity: .55
        }

        .m5 {
            left: 48%;
            top: 30%;
            animation-duration: 11s
        }

        @keyframes drift {
            0% {
                transform: translateY(0) translateX(0);
                opacity: .15
            }

            50% {
                opacity: .7
            }

            100% {
                transform: translateY(-60px) translateX(10px);
                opacity: .15
            }
        }

        footer {
            margin-top: 10px;
            color: var(--muted);
            font-size: 12px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
    </style>
</head>

<body>
    <main class="wrap" role="main" aria-labelledby="page-title">
        <section class="card">
            <div class="brand">
                <img src="{{ asset('img/BPSLogo.png') }}" alt="BPS logo" />
                <span>BPS Library Management System</span>
            </div>

            <h1 id="page-title">@yield('code') — @yield('message')</h1>
            <p class="lede">
                @yield('description')
            </p>

            <div class="actions">
                @yield('actions')
            </div>

            <p class="hint">Error code: @yield('code') @yield('message')</p>
            <footer aria-hidden="true">If you think this is a mistake, please contact support.</footer>
        </section>

        <section class="scene" aria-label="Bookshelves with a hanging @yield('code') sign">
            <div class="shelf s1"></div>
            <div class="shelf s2"></div>
            <div class="shelf s3"></div>

            <div class="books">
                <!-- Top shelf -->
                <div class="book b1" style="left:6%; height:74px"></div>
                <div class="book b3" style="left:10%; height:92px"></div>
                <div class="book b2" style="left:14%; height:84px"></div>
                <div class="book b4" style="left:18%; height:78px"></div>
                <div class="book b5" style="left:23%; height:88px"></div>
                <div class="book b1" style="left:28%; height:70px"></div>
                <div class="book b3" style="left:33%; height:82px"></div>

                <!-- Middle shelf -->
                <div class="book b2" style="left:56%; height:86px"></div>
                <div class="book b5" style="left:60%; height:74px"></div>
                <div class="book b1" style="left:64%; height:96px"></div>
                <div class="book b4" style="left:68%; height:76px"></div>
                <div class="book b3" style="left:72%; height:82px"></div>
                <div class="book b2" style="left:76%; height:90px"></div>

                <!-- Bottom shelf -->
                <div class="book b4" style="left:12%; top:64%; height:86px"></div>
                <div class="book b5" style="left:16%; top:64%; height:92px"></div>
                <div class="book b1" style="left:20%; top:64%; height:78px"></div>
                <div class="book b3" style="left:24%; top:64%; height:84px"></div>
                <div class="book b2" style="left:28%; top:64%; height:80px"></div>
            </div>

            <div class="sign" aria-hidden="true">
                <div class="rope"></div>
                <div class="board">
                    <h2>@yield('code')</h2>
                    <small>@yield('message')</small>
                </div>
            </div>

            <div class="dust" aria-hidden="true">
                <span class="mote m1"></span>
                <span class="mote m2"></span>
                <span class="mote m3"></span>
                <span class="mote m4"></span>
                <span class="mote m5"></span>
            </div>
        </section>
    </main>

    <span class="sr-only">@yield('code') - @yield('message')</span>
</body>

</html>