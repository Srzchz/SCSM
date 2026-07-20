@extends('crm.layouts.app')

@section('title', $pageTitle ?? 'Coming Soon')

@php $active = $pageTitle ?? ''; @endphp

@section('content')

    @include('crm.partials.topbar')

    <div class="bg-curema-card rounded-2xl border border-curema-border p-12 flex flex-col items-center justify-center text-center min-h-[420px]">
        <div class="w-16 h-16 rounded-2xl bg-curema-purplesoft flex items-center justify-center text-3xl mb-4">🚧</div>
        <h2 class="text-xl font-bold mb-2">{{ $pageTitle ?? 'Coming Soon' }}</h2>
        <p class="text-sm text-curema-sub max-w-sm">
            This page isn't built out yet — it's here so the sidebar link works while the rest of the module
            gets designed and wired up.
        </p>
    </div>

@endsection