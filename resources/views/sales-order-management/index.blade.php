@extends('layouts.app')

@php
    $tabLabels = [
        'quotations' => 'Sales Quotations',
        'orders' => 'Sales Orders',
        'pricing' => 'Pricing Rules',
        'invoicing' => 'Invoicing',
    ];
    $active = $tabLabels[request('tab')] ?? 'Sales Quotations';
@endphp

@section('title', 'Sales Order Management')

@push('styles')
    <style>
        {!! file_get_contents(resource_path('views/sales-order-management/sales-order-management.css')) !!}
    </style>
@endpush

@section('content')
    <div id="root"></div>
@endsection

@push('scripts')
    <script>
        {!! file_get_contents(resource_path('views/sales-order-management/sales-order-management.js')) !!}
    </script>
@endpush