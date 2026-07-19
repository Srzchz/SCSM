@php $followUps = $followUps ?? \App\Support\DemoCustomers::followUps(); @endphp

<div class="w-[220px] h-[317px] bg-curema-card rounded-2xl border border-curema-border p-4 flex flex-col overflow-hidden">
    <h2 class="font-semibold mb-3 text-sm">Upcoming Follow-ups</h2>
    <ul class="divide-y divide-curema-border overflow-y-auto overflow-x-hidden flex-1 scrollbar-hide">
        @foreach ($followUps as $f)
            <li>
                <a href="{{ route('customers.show', $f['id']) }}" class="py-2.5 flex items-start gap-2 hover:bg-curema-bg -mx-2 px-2 rounded-lg transition min-w-0">
                    <div class="w-7 h-7 rounded-full bg-curema-bg flex items-center justify-center text-xs shrink-0">👤</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium leading-tight truncate">{{ $f['name'] }}</p>
                        <p class="text-[11px] text-curema-sub truncate">{{ $f['note'] }}</p>
                    </div>
                    <span class="text-[10px] text-curema-sub shrink-0 whitespace-nowrap">{{ $f['date'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
    <a href="#" class="text-xs text-curema-sub block text-center pt-2">•••</a>
</div>