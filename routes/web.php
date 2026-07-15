<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Token Protected Architecture
|--------------------------------------------------------------------------
| Every page here is a thin Blade shell: real data comes from the JSON
| API in routes/api.php via the `api()` JS helper in layouts/app.blade.php,
| which attaches the Sanctum bearer token from localStorage. Grouped to
| mirror the app's structure so a given feature's route/view pair is easy
| to find.
*/

// ---------------------------------------------------------------------
// Public: auth & onboarding
// ---------------------------------------------------------------------
Route::view('/', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/rules', 'auth.rules')->name('rules');
Route::view('/group-rules', 'grouprules')->name('group-rules');

// ---------------------------------------------------------------------
// Dashboard: one router view + one per role. dashboard.index inspects
// the authenticated user's role (via /api/me) and redirects to the
// matching dashboard below - nobody has to know these URLs by hand.
// ---------------------------------------------------------------------
Route::view('/dashboard', 'dashboard.index')->name('dashboard');
Route::view('/dashboard/student', 'dashboard.student')->name('dashboard.student');
Route::view('/dashboard/lecturer', 'dashboard.lecturer')->name('dashboard.lecturer');
Route::view('/dashboard/admin', 'dashboard.admin')->name('dashboard.admin');

// Role management (Administrator only - enforced by the /api/users*
// endpoints themselves; this page just gives them a UI for that).
Route::view('/admin/users', 'admin.users')->name('admin.users');

// ---------------------------------------------------------------------
// Profile
// ---------------------------------------------------------------------
Route::view('/profile', 'profile.edit')->name('profile.edit');

// ---------------------------------------------------------------------
// Groups: discussion, statistics, gradebook
// ---------------------------------------------------------------------
Route::view('/groups/{group}', 'topics.index')->name('topics.index');
Route::view('/groups/{group}/statistics', 'groups.statistics')->name('groups.statistics');
Route::view('/groups/{group}/gradebook', 'groups.gradebook')->name('groups.gradebook');

// ---------------------------------------------------------------------
// Topics & quizzes
// ---------------------------------------------------------------------
Route::view('/topics/{topic}', 'topics.show')->name('topics.show');
Route::view('/quizzes/{quiz}', 'quizzes.attempt')->name('quizzes.attempt');
