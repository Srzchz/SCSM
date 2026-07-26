@extends('layouts.app')

@section('title', 'Account')

@section('content')

    <div class="mb-5">
        <h1 class="text-2xl font-extrabold">Account</h1>
        <p class="text-sm text-curema-sub">Manage your profile and login details</p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-curema-greensoft text-curema-green text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm font-medium space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php $user = auth()->user(); @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="module-card lg:col-span-1">
            <div class="flex flex-col items-center text-center gap-3 py-2">
                <div class="w-16 h-16 rounded-full bg-curema-purplesoft flex items-center justify-center text-xl font-bold">
                    {{ $user->avatar_initials ?: mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold">{{ $user->name }}</p>
                    <p class="text-sm text-curema-sub">{{ $user->email }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold
                             {{ $user->isManager() ? 'bg-curema-purplesoft text-curema-purple' : 'bg-curema-bg text-curema-ink/70' }}">
                    {{ ucfirst($user->role) }}
                </span>
                @if ($user->department)
                    <p class="text-xs text-curema-sub">{{ $user->department }}</p>
                @endif
            </div>
        </div>

        <div class="module-card lg:col-span-2">
            <h2 class="card-title mb-4">Edit profile</h2>

            <form method="POST" action="{{ route('account.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block text-xs font-medium text-curema-sub mb-1.5">Full name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                                  focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                </div>

                <div>
                    <label for="department" class="block text-xs font-medium text-curema-sub mb-1.5">Department</label>
                    <input type="text" name="department" id="department" value="{{ old('department', $user->department) }}"
                           class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                                  focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-medium text-curema-sub mb-1.5">New password <span class="text-curema-sub/70">(optional)</span></label>
                        <input type="password" name="password" id="password"
                               class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                                      focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-curema-sub mb-1.5">Confirm new password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full px-4 py-2.5 rounded-xl bg-curema-bg border border-curema-border text-sm
                                      focus:outline-none focus:ring-2 focus:ring-curema-purple/40">
                    </div>
                </div>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-curema-purple text-white text-sm font-semibold">
                    Save changes
                </button>
            </form>
        </div>
    </div>

@endsection
