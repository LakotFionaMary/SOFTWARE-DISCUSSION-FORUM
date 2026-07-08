<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Token Protected Architecture
|--------------------------------------------------------------------------
*/

Route::view('/', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/rules', 'auth.rules')->name('rules');

Route::view('/dashboard', 'dashboard.index')->name('dashboard');
Route::view('/groups/{group}', 'topics.index')->name('topics.index');
Route::view('/topics/{topic}', 'topics.show')->name('topics.show');
Route::view('/quizzes/{quiz}', 'quizzes.attempt')->name('quizzes.attempt');
Route::view('/admin/statistics/{group}', 'admin.statistics')->name('admin.statistics');
Route::view('/groups/{group}/gradebook', 'groups.gradebook')->name('groups.gradebook');

Route::view('/profile', 'profile.edit')->name('profile.edit');

/* group rules*/
Route::get('/group-rules', function () {
    return view('grouprules');
});