@php $insights = $insights ?? \App\Support\DemoCustomers::insights(); @endphp

<div class="module-card side-card">
    <h2 class="card-title">Customer Insight</h2>
    <p class="side-card-hint">{{ $insights['hint'] ?? '' }}</p>
    @foreach ($insights['segments'] ?? [] as $seg)
        <div class="insight-bar-row">
            <span class="insight-bar-label">{{ $seg['label'] }}</span>
            <div class="insight-bar-track" style="background:transparent;"><div class="insight-bar-fill" style="width:{{ $seg['pct'] }}%;background:{{ $seg['color'] }}"></div></div>
            <span class="insight-bar-pct">{{ $seg['pct'] }}%</span>
        </div>
    @endforeach
</div>