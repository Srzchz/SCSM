@php $followUps = $followUps ?? \App\Support\DemoCustomers::followUps(); @endphp

<div class="module-card side-card">
    <h2 class="card-title">Upcoming Follow-ups</h2>
    <ul class="followup-list">
        @foreach ($followUps as $f)
            <li>
                <span class="followup-name">{{ $f['name'] }}</span>
                <span class="followup-note">{{ $f['note'] }}</span>
                <span class="followup-when">{{ $f['date'] }}</span>
            </li>
        @endforeach
    </ul>
</div>