@php $insights = $insights ?? \App\Support\DemoCustomers::insights(); @endphp

<div class="w-[220px] h-[239px] bg-white rounded-[18px] border border-[rgba(18,15,52,0.08)] shadow-[0_10px_30px_rgba(18,15,52,0.04)] p-[18px] flex flex-col overflow-hidden">
    <h2 class="font-extrabold text-base mb-3">Customer Insight</h2>
    <ul class="space-y-2.5 overflow-y-auto">
        @foreach ($insights as $i)
            <li class="flex items-center justify-between text-xs">
                <span class="text-curema-sub">{{ $i['label'] }}</span>
                <span class="flex items-center gap-1 font-semibold shrink-0 ml-2">{{ $i['value'] }} <span class="text-curema-sub">›</span></span>
            </li>
        @endforeach
    </ul>
</div>