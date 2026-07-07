<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Profile self-service: lets any authenticated user (Student, Lecturer,
 * Administrator) view and update their own biodata and profile picture.
 */
class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'phone_public' => 'sometimes|boolean',
            'department' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh('roles'),
        ]);
    }

    public function show($userId)
    {
        $user = \App\Models\User::with('roles')->findOrFail($userId);

        return response()->json([
            'user_id' => $user->user_id,
            'full_name' => $user->full_name,
            'bio' => $user->bio,
            'phone' => $user->phone_public ? $user->phone : null,
            'phone_public' => (bool) $user->phone_public,
            'profile_picture' => $user->profile_picture,
            'department' => $user->department,
            'role' => $user->roles->first()->role_name ?? 'Student',
        ]);
    }

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        $user->update(['profile_picture' => $path]);

        return response()->json([
            'message' => 'Profile picture updated.',
            'profile_picture_url' => asset('storage/' . $path),
        ]);
    }
}