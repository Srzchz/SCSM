<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ask.ist — Sales & CRM')</title>

    {{-- Swap for @vite(['resources/css/app.css','resources/js/app.js']) once your
         Vite/Laragon build is wired up. Left as plain tags so this shell runs
         standalone while you're still wireframing. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Charts are only actually built on Overview / Sales Report, but Chart.js is
         tiny and cached, and every section is rendered up front in this SPA shell,
         so it's simplest to load it once here rather than per-section. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body class="app-body">

    <div class="app-shell">

        @include('ascm.partials.sidebar')

        <div class="app-main">
            @include('ascm.partials.topbar')

            {{-- This is the only part of the page that ever changes.
                 app.js shows/hides children of #app-content based on
                 whichever sidebar item was clicked. --}}
            <main id="app-content" class="app-content">
                @yield('content')
            </main>
        </div>

    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
