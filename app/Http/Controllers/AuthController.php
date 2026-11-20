<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{ // REGISTER ACCOUNT
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'student_id' => 'required|string|max:50|unique:users,student_id',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'account_type' => 'nullable|in:customer,runner',
        ] + RunnerController::applicationRules(null, 'account_type'), RunnerController::applicationMessages());

        $user = new User([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'student_id' => $validated['student_id'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Runners start as customers until the admin approves them.
        if (($validated['account_type'] ?? 'customer') === 'runner') {
            $user->save();
            RunnerController::saveApplication($user, $validated, $request->file('id_photo'));
        } else {
            $user->save();
        }

        // DO NOT AUTOMATICALLY LOG IN THE USER
        // Redirect to login after successful registration
        return redirect()->route('login')
            ->with('success', 'Account created successfully! Please log in.');
    }

    // LOGIN ACCOUNT
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            if ($request->user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Incorrect email or password.',
        ])->onlyInput('email');
    }

    // LOGOUT ACCOUNT
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // UPDATE PROFILE IMAGE
    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $path = $request->file('profile_image')
            ->store('profile-images', 'public');

        $user->update([
            'profile_image' => $path,
        ]);

        return back()->with(
            'success',
            'Profile picture updated successfully!'
        );
    }
}
