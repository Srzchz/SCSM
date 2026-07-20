@extends('ascm.layouts.app')

@section('content')

    {{--
        Every section is rendered into the DOM up front, then hidden with
        CSS ([hidden] attribute) except the default one. app.js just toggles
        that attribute — no page reload, no extra requests.

        $sections and $default come from DashboardController@index.
    --}}
    @foreach ($sections as $key => $view)
        <section
            id="{{ $key }}"
            class="content-section"
            @if ($key !== $default) hidden @endif
        >
            @include($view)
        </section>
    @endforeach

@endsection
