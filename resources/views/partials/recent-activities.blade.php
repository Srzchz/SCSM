@php $activities = $activities ?? \App\Support\DemoCustomers::activities(); @endphp

<div class="w-[220px] h-[263px] bg-curema-card rounded-2xl border border-curema-border p-4 flex flex-col overflow-hidden">
    <h2 class="font-semibold mb-3 text-sm">Recent Activities</h2>
    <ul class="divide-y divide-curema-border overflow-y-auto flex-1 scrollbar-hide">
        @foreach ($activities as $a)
            <li class="py-2.5 flex items-start gap-2">
                <div class="w-7 h-7 rounded-full bg-curema-purplesoft flex items-center justify-center text-xs shrink-0">{{ $a['icon'] }}</div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium leading-tight truncate">{{ $a['title'] }}</p>
                    <p class="text-[11px] text-curema-sub truncate">{{ $a['note'] }}</p>
                </div>
                <span class="text-[10px] text-curema-sub whitespace-nowrap">{{ $a['time'] }}</span>
            </li>
        @endforeach
    </ul>
    <a href="#" class="text-xs text-curema-sub block text-center pt-2">•••</a>
</div>