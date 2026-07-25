@php $activities = $activities ?? \App\Support\DemoCustomers::activities(); @endphp

<div class="module-card side-card">
    <h2 class="card-title">Recent Activities</h2>
    <div class="timeline timeline-compact">
        @foreach ($activities as $a)
            <div class="timeline-item">
                <div class="timeline-dot"></div>
                <div class="timeline-body">
                    <div class="timeline-title">{{ $a['title'] }}</div>
                    <div class="timeline-meta">{{ $a['note'] }} · {{ $a['time'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>