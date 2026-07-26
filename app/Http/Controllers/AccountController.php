<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(): View
    {
        return view('account.show', ['active' => 'account']);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'department' => 'nullable|string|max:80',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->fill([
            'name' => $data['name'],
            'department' => $data['department'] ?? null,
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Account updated.');
    }
}
