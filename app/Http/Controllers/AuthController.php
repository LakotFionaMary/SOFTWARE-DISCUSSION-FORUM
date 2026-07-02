<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt hashes 'password' against the 'password' column by
        // default. Since the User table stores it as password_hash, tell
        // the guard which column to compare — see the getAuthPassword()
        // note on the User model below.
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_active_at' => now(), 'presence_status' => 'Online']);

            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            } elseif ($user->isLecturer()) {
                return redirect('/lecturer/dashboard');
            }
            return redirect('/student/dashboard');
        }

        return back()->withErrors([
            'email' => 'These credentials are invalid.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'min:8', 'confirmed'],
            'role'      => ['required', 'in:student,lecturer'], // role_name to assign
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'full_name'       => $data['full_name'],
                'email'           => $data['email'],
                'password_hash'   => Hash::make($data['password']),
                'presence_status' => 'Offline',
                'last_active_at'  => now(),
            ]);

            // Look up the Role row (Table 21) and link it via the
            // UserRole pivot (Table 6) — roles are not a plain column
            // on User in this schema.
            $role = Role::where('role_name', ucfirst($data['role']))->firstOrFail();

            $user->roles()->attach($role->role_id, [
                'assigned_at' => now(),
                'assigned_by' => $user->user_id, // self-registered
            ]);

            return $user;
        });

        Auth::login($user);

        if ($user->isLecturer()) {
            return redirect('/lecturer/dashboard');
        }
        return redirect('/student/dashboard');
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            $user->update(['presence_status' => 'Offline', 'last_active_at' => now()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
