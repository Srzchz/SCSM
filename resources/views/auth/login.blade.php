@extends('layouts.guest')

@section('title', 'Log In')

@section('content')

    <h1 class="text-lg font-bold mb-1">Welcome back</h1>
    <p class="text-sm text-curema-sub mb-5">Log in to your SCSM account.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-medium text-curema-sub mb-1.5">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-curema-sub mb-1.5">Password</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <label class="flex items-center gap-2 text-sm text-curema-sub">
            <input type="checkbox" name="remember" class="rounded border-curema-border">
            Remember me
        </label>

        <button type="submit"
                class="w-full py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold">
            Log In
        </button>
    </form>

    <p class="text-sm text-curema-sub text-center mt-5">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold text-curema-ink hover:underline">Sign up</a>
    </p>

@endsection
