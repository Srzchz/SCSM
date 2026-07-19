@extends('layouts.app')

@section('title', 'Customers')

@php
    $active = 'Customers';
     
@endphp

@section('content')

    @include('partials.topbar')

    <div class="flex items-start gap-4 min-w-0">
        <div class="flex-1 min-w-0">
            @include('partials.customer-table', [
                'tableTitle' => 'All Customers',
                'tableCustomers' => $tableCustomers,
                'showViewAllLink' => false,
            ])
        </div>

        <div class="w-[220px] shrink-0 flex flex-col gap-4">
            @include('partials.customer-insight')
            @include('partials.upcoming-followups')
            @include('partials.recent-activities')
        </div>
    </div>

@endsection