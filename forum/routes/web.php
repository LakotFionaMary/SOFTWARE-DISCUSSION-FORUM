<?php
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Home — redirects to login if not logged in, or to dashboard if logged in
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->isAdmin())    return redirect('/admin/dashboard');
        if ($user->isLecturer()) return redirect('/lecturer/dashboard');
        return redirect('/student/dashboard');
    }
    return redirect('/login');
});

// Guest-only routes (logged-in users cannot access these)
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Student dashboard

Route::middleware(['auth'])->group(function () {
      Route::get('/quizzes',                    [QuizController::class, 'studentIndex'])->name('quizzes');
    Route::get('/quizzes/{id}/attempt',       [QuizController::class, 'attempt'])    ->name('quizzes.attempt');
    Route::post('/quizzes/{id}/submit',       [QuizController::class, 'submit'])     ->name('quizzes.submit');
    Route::get('/quizzes/{quizId}/result/{attemptId}', [QuizController::class, 'result'])->name('quizzes.result');

   Route::get('/student/dashboard', fn() => view('dashboards.student'));
    Route::get('/quizzes',       fn() => view('quiz'))      ->name('quizzes');
    Route::get('/notifications', fn() => view('notifications'))->name('notifications');
    Route::get('/profile',       fn() => view('profile'))      ->name('profile');
});

// Lecturer dashboard
Route::middleware(['auth', 'role:lecturer'])->group(function () {
    Route::get('/lecturer/dashboard', fn() => view('dashboards.lecturer'));
});

// ── Lecturer routes ─────────────────────────────────────
Route::middleware(['auth'])->prefix('lecturer')->group(function () {

    // Lecturer dashboard
    Route::get('/lecturer/dashboard', fn() => view('dashboards.lecturer'))
        ->name('lecturer.dashboard');

    // Show quiz creation form
    Route::get('/quizzes/create', fn() => view('quiz-config'))
        ->name('quizzes.create');

    // Store quiz (POST)
    Route::post('/quizzes', function (\Illuminate\Http\Request $request) {
        // Validate
        $data = $request->validate([
            'quiz_title'   => 'required|string|max:255',
            'category'     => 'required|string',
            'quiz_date'    => 'required|date',
            'start_time'   => 'required',
            'duration'     => 'required|integer|min:5|max:180',
            'instructions' => 'nullable|string',
            'attempts'     => 'required|string',
            'questions'    => 'required|array|min:1',
        ]);

        $status = $request->input('action') === 'publish' ? 'published' : 'draft';

        // TODO: Save to DB via Quiz model
        // Quiz::create([...]);

        $msg = $status === 'published' ? 'Quiz published successfully!' : 'Quiz saved as draft.';
        return redirect()->route('dashboards.lecturer')->with('success', $msg);
    })->name('quizzes.store');

    // List all quizzes
    Route::get('/quizzes', function () {
        // TODO: return Quiz::where('lecturer_id', Auth::id())->get()
        return view('dashboards.lecturer');
    })->name('quizzes.index');

    Route::get('/quizzes/create',          [QuizController::class, 'create'])   ->name('quizzes.create');
    Route::post('/quizzes',                [QuizController::class, 'store'])    ->name('quizzes.store');
    Route::get('/quizzes/{id}/edit',       [QuizController::class, 'edit'])     ->name('quizzes.edit');
    Route::put('/quizzes/{id}',            [QuizController::class, 'update'])   ->name('quizzes.update');
    Route::post('/quizzes/{id}/publish',   [QuizController::class, 'publish'])  ->name('quizzes.publish');
    Route::post('/quizzes/{id}/unpublish', [QuizController::class, 'unpublish'])->name('quizzes.unpublish');
    Route::delete('/quizzes/{id}',         [QuizController::class, 'destroy'])  ->name('quizzes.destroy');
    Route::get('/quizzes/{id}/feedback',   [QuizController::class, 'feedback']) ->name('quizzes.feedback');
});


// Admin dashboard
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', fn() => view('dashboards.admin'));
});


Route::get('/quizzes', function () {
        return view('quiz');
    })->name('quizzes');
 
    Route::get('/notifications', function () {
        return view('notifications');
    })->name('notifications');
 
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
 

 