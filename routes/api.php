<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GradingController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\MessagingController;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\QuizAttemptController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReplyController;
use App\Http\Controllers\Api\SocialShareController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;

/*
|--------------------------------------------------------------------------
| Smart Discussion Forum API Routes
|--------------------------------------------------------------------------
| Grouped to mirror SDD Section 5 (Component Design) module by module.
| All routes below the "auth:sanctum" group represent the JWT/Sanctum
| protected API described in SDD 5.1 (Membership and On-boarding Module).
*/

// ---------------------------------------------------------------------
// 5.1 Membership and On-boarding Module — public auth endpoints
// ---------------------------------------------------------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/topics', [TopicController::class, 'globalIndex']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [ProfileController::class, 'update']);
    Route::post('/me/profile-picture', [ProfileController::class, 'updatePicture']);
  Route::get('/users/{userId}/profile', [ProfileController::class, 'show']);

    // -------------------------------------------------------------
    // 5.1 Role Management (Administrator only)
    // -------------------------------------------------------------
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::patch('/users/{user}/role', [UserController::class, 'assignRole']);
    });

    // -------------------------------------------------------------
    // Groups (create: any authenticated user; join: any member)
    // -------------------------------------------------------------
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::post('/groups/{group}/join', [GroupController::class, 'join']);
    Route::get('/groups/{group}/members', [GroupController::class, 'members']);

    // -------------------------------------------------------------
    // 5.3 Topic Management and Export Module
    // -------------------------------------------------------------
    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/groups/{group}/topics', [TopicController::class, 'index']);
  Route::get('/groups/{group}/topics/categories', [TopicController::class, 'categories']);
    Route::post('/groups/{group}/topics', [TopicController::class, 'store'])
        ->middleware('blacklist');
    Route::get('/topics/{topic}', [TopicController::class, 'show']);
    Route::get('/topics/{topic}/export', [TopicController::class, 'export']);

    // -------------------------------------------------------------
    // Posts, replies, moderation (part of 5.2 + 5.3)
    // -------------------------------------------------------------
    Route::get('/topics/{topic}/posts', [PostController::class, 'index']);
    Route::get('/topics/{id}', [TopicController::class, 'show']);
    Route::post('/topics/{topic}/posts', [PostController::class, 'store']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/flag', [PostController::class, 'flag'])
        ->middleware('role:Administrator,Lecturer');

    Route::post('/posts/{post}/replies', [ReplyController::class, 'store']);
    Route::post('/replies/{reply}/flag', [ReplyController::class, 'flag'])
        ->middleware('role:Administrator,Lecturer');

    // -------------------------------------------------------------
    // 5.2 Moderation and Inactivity Management Module (admin/lecturer)
    // -------------------------------------------------------------
    Route::middleware('role:Administrator,Lecturer')->group(function () {
        Route::post('/groups/{group}/moderation/scan-inactivity', [ModerationController::class, 'scanInactivity']);
        Route::post('/moderation/warnings/{warning}/resolve', [ModerationController::class, 'resolveWarning']);
        Route::post('/groups/{group}/blacklist/{user}', [ModerationController::class, 'blacklistUser']);
        Route::post('/moderation/blacklists/{blacklist}/lift', [ModerationController::class, 'liftBlacklist']);
    });

    // -------------------------------------------------------------
    // 5.4 Messaging and Synchronization Module
    // -------------------------------------------------------------
    Route::post('/groups/{group}/messages', [MessagingController::class, 'send'])
        ->middleware('blacklist');
    Route::post('/sync', [SyncController::class, 'sync']);

    // -------------------------------------------------------------
    // 5.5 Quiz Engine Module
    // -------------------------------------------------------------
    Route::get('/groups/{group}/quizzes', [QuizController::class, 'index']);
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show']);

    // Student-facing: quizzes across all of the user's own groups
    // (lecturers get everything they authored; students get published-only).
    Route::get('/me/quizzes', [QuizController::class, 'mine']);

    Route::middleware('role:Administrator,Lecturer')->group(function () {
        Route::post('/groups/{group}/quizzes', [QuizController::class, 'store']);
        Route::post('/quizzes/{quiz}/publish', [QuizController::class, 'publish']);
        Route::post('/quizzes/{quiz}/close', [QuizController::class, 'close']);
        // FIXED: this previously pointed at QuizController, which has no
        // `results` method — the real implementation lives on QuizAttemptController.
        // Whole-class results are lecturer/admin only; students use their own
        // attempt endpoint below instead.
        Route::get('/quizzes/{quiz}/results', [QuizAttemptController::class, 'results']);
    });

    Route::post('/quizzes/{quiz}/attempts/start', [QuizAttemptController::class, 'start']);
    Route::post('/attempts/{attempt}/submit', [QuizAttemptController::class, 'submit']);

    // Student-facing: "my grades" for quizzes.
    Route::get('/me/quiz-attempts', [QuizAttemptController::class, 'mine']);
    Route::get('/quizzes/{quiz}/my-attempt', [QuizAttemptController::class, 'myAttempt']);

    // -------------------------------------------------------------
    // 5.6 Grading and Participation Module
    // -------------------------------------------------------------

  Route::get('/groups/{group}/topics/categories', [App\Http\Controllers\Api\TopicController::class, 'categories']);
    Route::get('/groups/{group}/leaderboard', [GradingController::class, 'leaderboard']);

    // Student-facing: "my grade" breakdown (participation + quizzes) for a group.
    Route::get('/groups/{group}/my-grade', [GradingController::class, 'myGrade']);

    Route::middleware('role:Administrator,Lecturer')->group(function () {
        Route::get('/groups/{group}/scoring-criteria', [GradingController::class, 'criteriaIndex']);
        Route::post('/groups/{group}/scoring-criteria', [GradingController::class, 'storeCriteria']);
        // Lecturer-facing: full per-student gradebook for a group.
        Route::get('/groups/{group}/gradebook', [GradingController::class, 'gradebook']);
    });

    // -------------------------------------------------------------
    // 5.7 Statistics Module (Administrator and Lecturer)
    // -------------------------------------------------------------
    // WIDENED: lecturers now get analytics for their own groups too, so the
    // lecturer dashboard can link straight into this page (previously
    // Administrator-only).
    Route::get('/groups/{group}/statistics', [StatisticsController::class, 'groupStatistics'])
        ->middleware('role:Administrator,Lecturer');

    // -------------------------------------------------------------
    // 5.8 ML Classification and Recommendation
    // -------------------------------------------------------------
    Route::get('/recommendations', [RecommendationController::class, 'index']);

    // -------------------------------------------------------------
    // 5.9 Social Media Sharing Module
    // -------------------------------------------------------------
    Route::post('/posts/{post}/share', [SocialShareController::class, 'store']);

    // -------------------------------------------------------------
    // 5.10 Notification Module
    // -------------------------------------------------------------
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
});