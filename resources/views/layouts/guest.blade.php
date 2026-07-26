<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SCSM')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        curema: {
                            bg: '#E9EBFC',
                            card: '#FFFFFF',
                            ink: '#120F34',
                            sub: '#5B5876',
                            border: '#CFD2F9',
                            purple: '#120F34',
                            purplesoft: '#B0B4EC',
                            green: '#00630F',
                            greensoft: '#9CFF9F',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="bg-curema-bg text-curema-ink antialiased font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="w-9 h-9 rounded-xl bg-curema-purplesoft flex items-center justify-center text-lg">◈</div>
            <span class="text-xl font-extrabold tracking-tight">SCSM</span>
        </div>

        <div class="bg-curema-card rounded-2xl border border-curema-border shadow-sm p-6">
            @if (session('success'))
                <div class="mb-4 px-3 py-2.5 rounded-xl bg-curema-greensoft text-curema-green text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-3 py-2.5 rounded-xl bg-red-50 text-red-600 text-sm font-medium space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </div>

</body>
</html>
