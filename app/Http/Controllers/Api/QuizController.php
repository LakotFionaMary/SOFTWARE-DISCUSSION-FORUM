<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Quiz;
use App\Models\QuizConfiguration;
use App\Models\QuizQuestion;
use App\Services\NotificationService;
use Illuminate\Http\Request;

/**
 * Quiz configuration half of the Quiz Engine Module (SDD 5.5).
 * Lets a lecturer author, schedule, and publish a quiz with its questions.
 */
class QuizController extends Controller
{
    public function __construct(private NotificationService $notifications)
    {
    }

    public function index(Group $group)
    {
        return response()->json($group->quizzes()->with('configuration')->latest()->paginate(20));
    }

    /** Configure Quiz use case (SDD Table 39), steps 1-5: create + schedule. */
    public function store(Request $request, Group $group)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'scheduled_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string',
            'questions.*.option_a' => 'required|string',
            'questions.*.option_b' => 'required|string',
            'questions.*.option_c' => 'required|string',
            'questions.*.option_d' => 'required|string',
            'questions.*.correct_option' => 'required|in:A,B,C,D',
            'questions.*.marks' => 'nullable|integer|min:1',
        ]);

        $quiz = Quiz::create([
            'group_id' => $group->group_id,
            'lecturer_id' => $request->user()->user_id,
            'title' => $request->title,
            'status' => 'Scheduled',
        ]);

        QuizConfiguration::create([
            'quiz_id' => $quiz->quiz_id,
            'scheduled_date' => $request->scheduled_date,
            'start_time' => $request->start_time,
            'duration_minutes' => $request->duration_minutes,
        ]);

        foreach ($request->questions as $q) {
            QuizQuestion::create([
                'quiz_id' => $quiz->quiz_id,
                'question_text' => $q['question_text'],
                'option_a' => $q['option_a'],
                'option_b' => $q['option_b'],
                'option_c' => $q['option_c'],
                'option_d' => $q['option_d'],
                'correct_option' => $q['correct_option'],
                'marks' => $q['marks'] ?? 1,
            ]);
        }

        // Step 6: push a Quiz Announcement notification to eligible group members.
        $this->notifications->sendToMany(
            $group->members,
            'Quiz Announcement',
            "A new quiz '{$quiz->title}' has been scheduled for {$request->scheduled_date} at {$request->start_time}.",
            'Quiz',
            $quiz->quiz_id
        );

        return response()->json($quiz->load(['configuration', 'questions']), 201);
    }

    public function show(Quiz $quiz)
    {
        return response()->json($quiz->load(['configuration', 'questions', 'lecturer']));
    }

    /**
     * Powers the dashboard's quiz feed for the currently authenticated user.
     * Lecturers/Admins: every quiz they personally authored, any status
     * (so they can still Publish a Scheduled quiz or Close an Open one).
     * Students: quizzes belonging to groups they're a member of, excluding
     * Draft/Scheduled ones that haven't been published to the class yet.
     */
    public function mine(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('Lecturer') || $user->hasRole('Administrator')) {
            $quizzes = Quiz::with(['configuration', 'group'])
                ->where('lecturer_id', $user->user_id)
                ->latest()
                ->get();
        } else {
            $groupIds = $user->groups()->pluck('groups.group_id');

            $quizzes = Quiz::with(['configuration', 'group'])
                ->whereIn('group_id', $groupIds)
                ->whereIn('status', ['Open', 'Closed'])
                ->latest()
                ->get();

            // Auto-open/close support: expose the quiz's real start/end
            // clock moments (mirrors QuizAttemptController::start()) so the
            // dashboard can decide when to auto-launch the quiz window
            // without re-deriving the date/time math on the client.
            $quizzes->each(function (Quiz $quiz) {
                $config = $quiz->configuration;
                if (! $config) {
                    return;
                }

                $opensAt = \Illuminate\Support\Carbon::parse(
                    $config->scheduled_date->toDateString() . ' ' . $config->start_time
                );
                $endsAt = $opensAt->copy()->addMinutes((int) $config->duration_minutes);

                $quiz->opens_at = $opensAt->toIso8601String();
                $quiz->ends_at = $endsAt->toIso8601String();
            });
        }

        return response()->json($quizzes);
    }

    public function publish(Quiz $quiz)
    {
        $quiz->update(['status' => 'Open']);

        return response()->json($quiz);
    }

    public function close(Quiz $quiz)
    {
        $quiz->update(['status' => 'Closed']);

        return response()->json($quiz);
    }
}
