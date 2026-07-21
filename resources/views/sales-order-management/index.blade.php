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
    <link rel="stylesheet" href="{{ asset('css/fanatec.css') }}">
@endpush

@section('content')
    <div id="root"></div>
@endsection

@push('scripts')
    <script src="{{ asset('js/fanatec.js') }}"></script>
@endpush