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
        // 2048KB (2MB) was too tight for a real phone camera photo, which is
        // often 3-8MB+ straight out of the camera. Raised to 8MB. Note this
        // is validated by Laravel *after* PHP has already accepted the
        // upload — if upload_max_filesize / post_max_size in php.ini are
        // still smaller than this, PHP silently drops the file before this
        // validation ever runs, and it will look identical to "no file
        // selected" (a "required" error). Those ini values need raising too
        // if photos are still failing after this change.
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:8192',
        ], [
            'profile_picture.required' => 'No image was received by the server — check upload_max_filesize/post_max_size in php.ini if the file is large.',
            'profile_picture.image' => 'The file must be an image.',
            'profile_picture.mimes' => 'Only JPG and PNG images are supported.',
            'profile_picture.max' => 'Image must be smaller than 8MB.',
        ]);

        $user = $request->user();

        try {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Could not save the uploaded file to storage.',
            ], 500);
        }

        try {
            $user->update(['profile_picture' => $path]);
        } catch (\Throwable $e) {
            // Most likely cause: 'profile_picture' isn't in the User
            // model's $fillable array, so update() throws a
            // MassAssignmentException instead of silently failing.
            return response()->json([
                'message' => 'Uploaded file was saved but could not be attached to your profile. Check that profile_picture is fillable on the User model.',
            ], 500);
        }

        return response()->json([
            'message' => 'Profile picture updated.',
            'profile_picture' => $path,
            'profile_picture_url' => asset('storage/' . $path),
        ]);
    }
}
