<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SCSM') - Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <script src="{{ asset('js/curema-storage.js') }}"></script>

    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            max-width: 100vw;
        }
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #DAD7EF; border-radius: 999px; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }

        html.curema-dark { filter: invert(1) hue-rotate(180deg); }
        html.curema-dark img,
        html.curema-dark canvas { filter: invert(1) hue-rotate(180deg); }
    </style>
    @stack('styles')
</head>
<body class="bg-curema-bg text-curema-ink antialiased"
      x-data="{ ui: { exportOpen: false, settingsOpen: false, addCustomerOpen: false } }"
      x-init="Curema.settings.applyDarkMode();">

    <aside class="hidden lg:flex flex-col fixed left-0 top-0 h-screen w-[250px] bg-curema-card border-r border-curema-border p-5 z-30">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-1 pb-6 mb-2 border-b border-curema-border">
            <div class="w-9 h-9 rounded-xl bg-curema-purplesoft flex items-center justify-center text-lg">�</div>
            <span class="text-xl font-extrabold tracking-tight">SCSM</span>
        </a>

        <nav class="flex-1 mt-4 space-y-1">
            @php
                $nav = [
                    ['icon' => '▦', 'label' => 'Dashboard', 'route' => 'dashboard'],
                    ['icon' => '◇', 'label' => 'Customers', 'route' => 'customers.index'],
                    ['icon' => '◈', 'label' => 'Purchase Behavior', 'route' => 'purchase-behavior'],
                    ['icon' => '▤', 'label' => 'Orders', 'route' => 'orders.index'],
                    ['icon' => '◔', 'label' => 'Communication Logs', 'route' => 'communication-logs'],
                    ['icon' => '📋', 'label' => 'Sales Quotations', 'route' => 'sales-order-management.index', 'params' => ['tab' => 'quotations']],
                    ['icon' => '🚚', 'label' => 'Sales Orders', 'route' => 'sales-order-management.index', 'params' => ['tab' => 'orders']],
                    ['icon' => '🏷️', 'label' => 'Pricing Rules', 'route' => 'sales-order-management.index', 'params' => ['tab' => 'pricing']],
                    ['icon' => '🧾', 'label' => 'Invoicing', 'route' => 'sales-order-management.index', 'params' => ['tab' => 'invoicing']],
                    ['icon' => '◎', 'label' => 'Cases', 'route' => 'ascm.cases'],
                    ['icon' => '◈', 'label' => 'Warranty', 'route' => 'ascm.warranty'],
                    ['icon' => '▮', 'label' => 'Sales Report', 'route' => 'sales-report'],
                ];
                $active = $active ?? 'Dashboard';
            @endphp

            @foreach ($nav as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                          {{ $active === $item['label']
                                ? 'bg-curema-purplesoft text-curema-purple font-semibold'
                                : 'text-curema-ink/70 hover:bg-curema-bg' }}">
                    <span class="w-5 text-center">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="pt-4 mt-4 border-t border-curema-border space-y-1">
            <a href="{{ route('account') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-curema-ink/70 hover:bg-curema-bg">
                <span class="w-5 text-center">◐</span> Account
            </a>
            <button type="button" @click="ui.settingsOpen = true"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-curema-ink/70 hover:bg-curema-bg text-left">
                <span class="w-5 text-center">⚙</span> Settings
            </button>
        </div>
    </aside>

    <main class="lg:ml-[250px] min-h-screen py-6 pl-6 pr-6 box-border overflow-x-hidden">
        @yield('content')
    </main>

    @include('partials.modals.export-modal')
    @include('partials.modals.settings-modal')
    @include('partials.modals.add-customer-modal')
    <script>
        // Chart.js caches the canvas's last computed pixel size in its inline
        // style attribute. After a browser zoom change, that cached size no
        // longer matches the container's actual CSS size, and chart.resize()
        // alone won't fix it — the canvas stays stuck at the old dimensions.
        // Clearing the inline width/height forces a full recalculation.
        function refreshAllCharts() {
            Object.values(Chart.instances).forEach((chart) => {
                chart.canvas.style.width = '';
                chart.canvas.style.height = '';
                chart.resize();
                chart.update('none');
            });
        }

        let chartResizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(chartResizeTimer);
            chartResizeTimer = setTimeout(refreshAllCharts, 250);
        });
    </script>
    <script>
        
        (function () {
            const params = new URLSearchParams(window.location.search);
            if (params.get('loggedOut') === '1') {
                alert('You have been logged out.');
                params.delete('loggedOut');
                const clean = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, '', clean);
            }
        })();
    </script>

    @stack('scripts')
</body>
</html>