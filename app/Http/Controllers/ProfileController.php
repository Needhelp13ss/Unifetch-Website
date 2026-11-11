<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private const YEAR_LEVELS = [
        '1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Graduate',
    ];

    // UPDATE (and first-time CREATE of details)
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validateWithBag('profile', [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'student_id'      => ['required', 'string', 'max:50', Rule::unique('users', 'student_id')->ignore($user->id)],
            'bio'             => ['nullable', 'string', 'max:160'],
            'gender'          => ['nullable', Rule::in(['male', 'female', 'other', 'prefer_not_to_say'])],
            'birthday'        => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'phone'           => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'course'          => ['nullable', 'string', 'max:100'],
            'year_level'      => ['nullable', Rule::in(self::YEAR_LEVELS)],
            'campus_location' => ['nullable', 'string', 'max:100'],
            'favorite_food'   => ['nullable', 'string', 'max:100'],
            'interests'       => ['nullable', 'string', 'max:150'],
        ]);

        $validated['show_gender'] = $request->boolean('show_gender');
        $validated['interests']   = $this->cleanInterests($validated['interests'] ?? null);

        $user->update($validated);

        return redirect()->route('profile')->with('success', 'Profile updated successfully!');
    }

    // DELETE: remove profile photo only
    public function destroyImage()
    {
        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->update(['profile_image' => null]);
        }

        return back()->with('success', 'Profile picture removed.');
    }

    // DELETE: clear all optional "About Me" details
    public function clearDetails()
    {
        Auth::user()->update([
            'bio' => null,
            'gender' => null,
            'show_gender' => false,
            'birthday' => null,
            'phone' => null,
            'course' => null,
            'year_level' => null,
            'campus_location' => null,
            'favorite_food' => null,
            'interests' => null,
        ]);

        return back()->with('success', 'Your profile details have been cleared.');
    }

    // DELETE: delete the whole account (needs password)
    public function destroy(Request $request)
    {
        $request->validateWithBag('delete', [
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        Auth::logout();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // "pizza, gaming ,  coding" -> "pizza, gaming, coding" (max 6 tags)
    private function cleanInterests(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $tags = collect(explode(',', $raw))
            ->map(fn ($t) => Str::limit(trim($t), 20, ''))
            ->filter()
            ->unique()
            ->take(6);

        return $tags->isEmpty() ? null : $tags->implode(', ');
    }
}