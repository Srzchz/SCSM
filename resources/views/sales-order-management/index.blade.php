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



