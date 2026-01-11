<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $twoFactorEnabled = ! is_null($user->two_factor_secret);
        $twoFactorConfirmed = ! is_null($user->two_factor_confirmed_at);

        if ($twoFactorEnabled && !$twoFactorConfirmed) {
            $user->makeVisible('two_factor_secret');
        }

        return Inertia::render('Profile/Index', [
            'user' => $user,
            'twoFactorEnabled' => $twoFactorEnabled,
            'twoFactorConfirmed' => $twoFactorConfirmed,
        ]);
    }

    public function update(UpdateProfileInformationRequest $request)
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return back()->with('status', 'profile-information-updated');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('status', 'password-updated');
    }
}