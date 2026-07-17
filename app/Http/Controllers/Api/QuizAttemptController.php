<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\TracksParticipation;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

/**
 * Attempt Quiz use case (SDD Table 40) and the quiz-scoring half of the
 * Grading and Participation Module (SDD 5.6): scores responses instantly
 * against the stored answer key on manual submission or timer expiry.
 */
class QuizAttemptController extends Controller
{
    use TracksParticipation;

    /** Step 1: student opens the quiz; a QuizAttempt is created/resumed. */
 /** Step 1: student opens the quiz; a QuizAttempt is created/resumed. */
public function start(Request $request, Quiz $quiz)
{
    if ($request->user()->isBlacklistedIn($quiz->group_id)) {
        return response()->json(['message' => 'You are blacklisted from this group.'], 403);
    }

    if ($quiz->status !== 'Open') {
        return response()->json(['message' => 'This quiz is not currently open.'], 422);
    }

    $config = $quiz->configuration;

    $opensAt = null;
    $endsAt = null;

    if ($config) {
        $opensAt = \Illuminate\Support\Carbon::parse(
            $config->scheduled_date->toDateString() . ' ' . $config->start_time
        );
        $endsAt = $opensAt->copy()->addMinutes((int) $config->duration_minutes);

        // Don't let a student start before the scheduled hour, even if
        // the quiz was published early.
        if (now()->lt($opensAt)) {
            return response()->json([
                'message' => "This quiz opens at {$opensAt->format('Y-m-d H:i')}.",
            ], 422);
        }

        // The scheduled window has already elapsed (e.g. auto-close
        // hasn't run yet this minute) — don't hand out a fresh timer.
        if (now()->gte($endsAt)) {
            return response()->json([
                'message' => 'This quiz\'s scheduled window has ended.',
            ], 422);
        }
    }

    $attempt = QuizAttempt::firstOrCreate(
        ['quiz_id' => $quiz->quiz_id, 'user_id' => $request->user()->user_id],
        ['started_at' => now()]
    );

    $attempt->load('quiz.questions', 'quiz.configuration');

    $secondsRemaining = $endsAt ? max(0, $endsAt->timestamp - now()->timestamp) : null;

    $payload = $attempt->toArray();
    $payload['seconds_remaining'] = $secondsRemaining;

    return response()->json($payload, 201);
}

    /**
     * Step 4-6: manual submission or timer expiry (auto_submitted=true)
     * triggers instant scoring against the answer key.
     */
    public function submit(Request $request, QuizAttempt $attempt)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:quiz_questions,question_id',
            'answers.*.selected_option' => 'nullable|in:A,B,C,D',
            'auto_submitted' => 'nullable|boolean',
        ]);

        if ($attempt->submitted_at) {
            return response()->json(['message' => 'This attempt has already been submitted.'], 422);
        }

        $totalScore = 0;

        foreach ($request->answers as $a) {
            $question = $attempt->quiz->questions()->findOrFail($a['question_id']);
            $isCorrect = $question->correct_option === ($a['selected_option'] ?? null);
            $marksAwarded = $isCorrect ? $question->marks : 0;
            $totalScore += $marksAwarded;

            QuizAnswer::updateOrCreate(
                ['attempt_id' => $attempt->attempt_id, 'question_id' => $question->question_id],
                [
                    'selected_option' => $a['selected_option'] ?? null,
                    'is_correct' => $isCorrect,
                    'marks_awarded' => $marksAwarded,
                ]
            );
        }

        $attempt->update([
            'submitted_at' => now(),
            'auto_submitted' => $request->boolean('auto_submitted'),
            'score' => $totalScore,
        ]);

        $this->recordParticipation($attempt->user, $attempt->quiz->group_id, 'quiz_attempt');

        return response()->json($attempt->fresh(['answers', 'quiz']));
    }

    /** Whole-class results view for lecturers/admins (route-protected). */
    public function results(Quiz $quiz)
    {
        return response()->json(
            $quiz->attempts()->with('user')->whereNotNull('submitted_at')->orderByDesc('score')->get()
        );
    }

    /**
     * A student's own quiz history across every quiz they've attempted,
     * used to power the "My Grades" view on the dashboard.
     */
    public function mine(Request $request)
    {
        $attempts = QuizAttempt::with(['quiz.configuration', 'quiz.group'])
            ->where('user_id', $request->user()->user_id)
            ->orderByDesc('started_at')
            ->get();

        return response()->json($attempts);
    }

    /**
     * A student's own attempt + answer breakdown for one quiz, so they can
     * review what they got right/wrong without exposing the whole class's
     * results (that stays behind the lecturer-only `results` endpoint above).
     */
    public function myAttempt(Request $request, Quiz $quiz)
    {
        $attempt = QuizAttempt::with(['answers.question', 'quiz'])
            ->where('quiz_id', $quiz->quiz_id)
            ->where('user_id', $request->user()->user_id)
            ->first();

        if (! $attempt) {
            return response()->json(['message' => 'You have not attempted this quiz yet.'], 404);
        }

        return response()->json($attempt);
    }
}
