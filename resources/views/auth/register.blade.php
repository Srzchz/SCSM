@extends('layouts.guest')

@section('title', 'Sign Up')

@section('content')

    <h1 class="text-lg font-bold mb-1">Create your account</h1>
    <p class="text-sm text-curema-sub mb-5">Set up access to SCSM.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-xs font-medium text-curema-sub mb-1.5">Full name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <div>
            <label for="email" class="block text-xs font-medium text-curema-sub mb-1.5">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <div>
            <span class="block text-xs font-medium text-curema-sub mb-1.5">Role</span>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center justify-center gap-2 py-2.5 rounded-xl border border-curema-border text-sm font-medium cursor-pointer has-[:checked]:border-curema-purple has-[:checked]:bg-curema-purplesoft/40">
                    <input type="radio" name="role" value="manager" class="accent-curema-purple" {{ old('role') === 'manager' ? 'checked' : '' }}>
                    Manager
                </label>
                <label class="flex items-center justify-center gap-2 py-2.5 rounded-xl border border-curema-border text-sm font-medium cursor-pointer has-[:checked]:border-curema-purple has-[:checked]:bg-curema-purplesoft/40">
                    <input type="radio" name="role" value="employee" class="accent-curema-purple" {{ old('role', 'employee') === 'employee' ? 'checked' : '' }}>
                    Employee
                </label>
            </div>
        </div>

        <div>
            <label for="department" class="block text-xs font-medium text-curema-sub mb-1.5">Department <span class="text-curema-sub/70">(optional)</span></label>
            <input type="text" name="department" id="department" value="{{ old('department') }}" placeholder="e.g. Sales, Support"
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <div>
            <label for="password" class="block text-xs font-medium text-curema-sub mb-1.5">Password</label>
            <input type="password" name="password" id="password" required
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-medium text-curema-sub mb-1.5">Confirm password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                          focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
        </div>

        <button type="submit"
                class="w-full py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold">
            Create Account
        </button>
    </form>

    <p class="text-sm text-curema-sub text-center mt-5">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold text-curema-ink hover:underline">Log in</a>
    </p>

@endsection
