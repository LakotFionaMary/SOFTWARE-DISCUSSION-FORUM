<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Membership and On-boarding Module (SDD 5.1).
 *
 * Handles registration (with rules acceptance), authentication (JWT/Sanctum
 * token issuance embedding the user's role), and session termination.
 */
class AuthController extends Controller
{
    /**
     * Register User use case (SDD Table 28).
     * Step 1-2: name/email/password + rules are presented.
     * Step 3: user must accept the rules or the request is blocked.
     * Step 4-5: validate, hash password, store timestamped acceptance.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'rules_accepted' => 'required|boolean|accepted',
            'role' => 'nullable|in:Student,Lecturer,Administrator',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Alternative flow: if the user declines the terms, block registration.
        if (! $request->boolean('rules_accepted')) {
            return response()->json(['message' => 'You must accept the forum rules to register.'], 422);
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'presence_status' => 'Offline',
            'rules_accepted' => true,
            'rules_accepted_at' => now(),
        ]);

        $roleName = $request->input('role', 'Student');
        $role = Role::firstOrCreate(['role_name' => $roleName]);
        UserRole::create([
            'user_id' => $user->user_id,
            'role_id' => $role->role_id,
            'assigned_at' => now(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'user' => $user->fresh('roles'),
            'token' => $token,
        ], 201);
    }

    /**
     * Authenticate User use case (SDD Table 29).
     * Blocks suspended (blacklisted) accounts and issues an API token
     * carrying the user's role, mirroring the JWT payload described in the SDD.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Suspended account alternative flow. Only an inactivity-triggered
        // blacklist locks the whole account out of login — a flag-triggered
        // or manual blacklist only suspends the member from that one group
        // (enforced separately by BlacklistMiddleware on that group's
        // routes), so it must not block login here.
        if ($user->blacklists()->where('reason', Blacklist::REASON_INACTIVITY)->where('end_date', '>', now())->exists()) {
            return response()->json([
                'message' => 'Your account is currently suspended due to prolonged inactivity.',
            ], 403);
        }

        $user->update(['presence_status' => 'Online', 'last_active_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('roles'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->update(['presence_status' => 'Offline']);
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('roles'));
    }
}
