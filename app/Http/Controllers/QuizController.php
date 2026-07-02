<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizController extends Controller
{
    // ══════════════════════════════════════════════════════
    //  LECTURER METHODS
    // ══════════════════════════════════════════════════════

    /** Show quiz management dashboard for lecturer */
    public function index()
    {
        $quizzes = Quiz::where('lecturer_id', Auth::id())
            ->withCount('questions')
            ->with('attempts')
            ->latest()
            ->get()
            ->map(function ($quiz) {
                $submitted = $quiz->attempts->where('status', '!=', 'in_progress');
                $quiz->attempted_count = $submitted->count();
                $quiz->avg_score       = $quiz->average_score;
                $quiz->status_live     = $quiz->status_live;
                return $quiz;
            });

        $stats = [
            'total'     => $quizzes->count(),
            'published' => $quizzes->where('status', 'published')->count(),
            'draft'     => $quizzes->where('status', 'draft')->count(),
            'attempted' => $quizzes->sum('attempted_count'),
        ];

        return view('quizmanagement', compact('quizzes', 'stats'));
    }

    /** Show create quiz form */
    public function create()
    {
        return view('quiz-config');
    }

    /** Store a new quiz with questions and options */
    public function store(Request $request)
    {
        $request->validate([
            'quiz_title'   => 'required|string|max:255',
            'category'     => 'required|string',
            'quiz_date'    => 'required|date',
            'start_time'   => 'required',
            'duration'     => 'required|integer|min:5|max:180',
            'questions'    => 'required|array|min:1',
            'questions.*.text'  => 'required|string',
            'questions.*.type'  => 'required|in:MCQ,TrueFalse,Short',
            'questions.*.marks' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Create the quiz
            $quiz = Quiz::create([
                'lecturer_id'     => Auth::id(),
                'title'           => $request->quiz_title,
                'category'        => $request->category,
                'quiz_date'       => $request->quiz_date,
                'start_time'      => $request->start_time,
                'duration'        => $request->duration,
                'instructions'    => $request->instructions,
                'attempts_allowed'=> $request->attempts ?? 1,
                'shuffle'         => $request->boolean('shuffle'),
                'show_results'    => $request->boolean('show_results'),
                'status'          => $request->action === 'publish' ? 'published' : 'draft',
            ]);

            // 2. Create questions and options
            foreach ($request->questions as $order => $qData) {
                $question = Question::create([
                    'quiz_id'       => $quiz->id,
                    'question_text' => $qData['text'],
                    'type'          => $qData['type'],
                    'marks'         => $qData['marks'],
                    'order'         => $order,
                ]);

                // Create options for MCQ and TrueFalse
                if (in_array($qData['type'], ['MCQ', 'TrueFalse'])) {
                    $correctIndex = (int) ($qData['correct'] ?? 0);
                    foreach ($qData['options'] as $optOrder => $optText) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => $optText,
                            'is_correct'  => ($optOrder === $correctIndex),
                            'order'       => $optOrder,
                        ]);
                    }
                }
            }
        });

        $msg = $request->action === 'publish'
            ? '✅ Quiz published! Students can now see and attempt it.'
            : '💾 Quiz saved as draft.';

       
return redirect('/lecturer/dashboard')->with('success', $msg);
    }

    /** Show edit form */
    public function edit($id)
    {
        $quiz = Quiz::where('lecturer_id', Auth::id())
            ->with(['questions.options'])
            ->findOrFail($id);

        return view('lecturer.quiz-create', compact('quiz'));
    }

    /** Update existing quiz */
    public function update(Request $request, $id)
    {
        $quiz = Quiz::where('lecturer_id', Auth::id())->findOrFail($id);

        DB::transaction(function () use ($request, $quiz) {
            $quiz->update([
                'title'           => $request->quiz_title,
                'category'        => $request->category,
                'quiz_date'       => $request->quiz_date,
                'start_time'      => $request->start_time,
                'duration'        => $request->duration,
                'instructions'    => $request->instructions,
                'attempts_allowed'=> $request->attempts ?? 1,
                'shuffle'         => $request->boolean('shuffle'),
                'show_results'    => $request->boolean('show_results'),
                'status'          => $request->action === 'publish' ? 'published' : 'draft',
            ]);

            // Delete old questions and recreate
            $quiz->questions()->each(fn($q) => $q->options()->delete());
            $quiz->questions()->delete();

            foreach ($request->questions as $order => $qData) {
                $question = Question::create([
                    'quiz_id'       => $quiz->id,
                    'question_text' => $qData['text'],
                    'type'          => $qData['type'],
                    'marks'         => $qData['marks'],
                    'order'         => $order,
                ]);

                if (in_array($qData['type'], ['MCQ', 'TrueFalse'])) {
                    $correctIndex = (int) ($qData['correct'] ?? 0);
                    foreach ($qData['options'] as $optOrder => $optText) {
                        Option::create([
                            'question_id' => $question->id,
                            'option_text' => $optText,
                            'is_correct'  => ($optOrder === $correctIndex),
                            'order'       => $optOrder,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('quizzes.index')->with('success', '✅ Quiz updated.');
    }

    /** Publish a draft quiz */
    public function publish($id)
    {
        Quiz::where('lecturer_id', Auth::id())->findOrFail($id)
            ->update(['status' => 'published']);
        return back()->with('success', '✅ Quiz published! Students can now see it.');
    }

    /** Unpublish back to draft */
    public function unpublish($id)
    {
        Quiz::where('lecturer_id', Auth::id())->findOrFail($id)
            ->update(['status' => 'draft']);
        return back()->with('success', '📝 Quiz moved back to draft.');
    }

    /** Delete a quiz */
    public function destroy($id)
    {
        Quiz::where('lecturer_id', Auth::id())->findOrFail($id)->delete();
        return back()->with('success', '🗑 Quiz deleted.');
    }

    /** Lecturer feedback — see all attempts for a quiz */
    public function feedback($id)
    {
        $quiz = Quiz::where('lecturer_id', Auth::id())
            ->with(['questions.options'])
            ->findOrFail($id);

        $attempts = QuizAttempt::where('quiz_id', $id)
            ->where('status', '!=', 'in_progress')
            ->with(['student', 'answers.question', 'answers.selectedOption'])
            ->latest('submitted_at')
            ->get();

        $stats = [
            'total_students' => $attempts->count(),
            'avg_score'      => $attempts->count()
                ? round($attempts->avg('score') / max($quiz->total_marks, 1) * 100, 1)
                : null,
            'highest'        => $attempts->max('score'),
            'lowest'         => $attempts->min('score'),
            'pass_count'     => $attempts->filter(fn($a) => $a->passed)->count(),
        ];

        // Per-question stats
        $questionStats = $quiz->questions->map(function ($q) use ($id) {
            $answers       = StudentAnswer::where('question_id', $q->id)
                ->whereHas('attempt', fn($a) => $a->where('quiz_id', $id)->where('status','!=','in_progress'))
                ->get();
            $total         = $answers->count();
            $correctCount  = $answers->where('is_correct', true)->count();
            return [
                'question'      => $q,
                'total_answers' => $total,
                'correct'       => $correctCount,
                'wrong'         => $total - $correctCount,
                'success_rate'  => $total ? round($correctCount / $total * 100) : 0,
            ];
        });

        return view('quizfeedback', compact('quiz', 'attempts', 'stats', 'questionStats'));
    }

    // ══════════════════════════════════════════════════════
    //  STUDENT METHODS
    // ══════════════════════════════════════════════════════

    /** Show all published quizzes for student */
    public function studentIndex()
    {
        $quizzes = Quiz::published()
            ->with(['questions', 'attempts' => fn($q) => $q->where('student_id', Auth::id())])
            ->latest('quiz_date')
            ->get()
            ->map(function ($quiz) {
                $myAttempts  = $quiz->attempts->where('student_id', Auth::id());
                $lastAttempt = $myAttempts->sortByDesc('attempt_number')->first();
                $quiz->my_attempt    = $lastAttempt;
                $quiz->status_live   = $lastAttempt && $lastAttempt->status !== 'in_progress'
                    ? 'attempted'
                    : $quiz->status_live;
                $quiz->can_attempt   = $quiz->status_live === 'open'
                    && $myAttempts->where('status','!=','in_progress')->count() < $quiz->attempts_allowed;
                return $quiz;
            });

        return view('quiz', compact('quizzes'));
    }

    /** Start a quiz attempt */
    public function attempt($id)
    {
        $quiz = Quiz::with(['questions' => fn($q) => $q->with('options')])
            ->findOrFail($id);

        // Guard: quiz must be open
        abort_if($quiz->status_live !== 'open', 403, 'This quiz is not currently open.');

        // Guard: check attempt limit
        $attemptCount = QuizAttempt::where('quiz_id', $id)
            ->where('student_id', Auth::id())
            ->where('status', '!=', 'in_progress')
            ->count();

        abort_if($attemptCount >= $quiz->attempts_allowed, 403, 'You have used all your attempts for this quiz.');

        // Create or resume in-progress attempt
        $attempt = QuizAttempt::firstOrCreate(
            ['quiz_id' => $id, 'student_id' => Auth::id(), 'status' => 'in_progress'],
            [
                'attempt_number' => $attemptCount + 1,
                'total_marks'    => $quiz->total_marks,
                'started_at'     => now(),
            ]
        );

        // Shuffle if enabled
        if ($quiz->shuffle) {
            $quiz->setRelation('questions', $quiz->questions->shuffle());
        }

        return view('student.quiz-attempt', compact('quiz', 'attempt'));
    }

    /** Submit quiz answers and grade */
    public function submit(Request $request, $id)
    {
        $quiz = Quiz::with(['questions.options'])->findOrFail($id);

        $attempt = QuizAttempt::where('quiz_id', $id)
            ->where('student_id', Auth::id())
            ->where('status', 'in_progress')
            ->firstOrFail();

        DB::transaction(function () use ($request, $quiz, $attempt) {
            $score        = 0;
            $correctCount = 0;
            $wrongCount   = 0;
            $skippedCount = 0;

            foreach ($quiz->questions as $question) {
                $answerData = $request->input("answers.{$question->id}");

                $selectedOptionId = null;
                $shortAnswer      = null;
                $isCorrect        = false;
                $marksEarned      = 0;

                if ($answerData === null || $answerData === '') {
                    // Skipped
                    $skippedCount++;
                } elseif ($question->type === 'Short') {
                    $shortAnswer  = $answerData;
                    // Short answers need manual marking by lecturer — give 0 for now
                    $isCorrect    = false;
                    $marksEarned  = 0;
                    $correctCount++; // counted as answered, not skipped
                } else {
                    // MCQ or TrueFalse
                    $selectedOption   = $question->options->find($answerData);
                    $selectedOptionId = $selectedOption?->id;

                    if ($selectedOption?->is_correct) {
                        $isCorrect   = true;
                        $marksEarned = $question->marks;
                        $score      += $marksEarned;
                        $correctCount++;
                    } else {
                        $wrongCount++;
                    }
                }

                StudentAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'selected_option_id' => $selectedOptionId,
                        'short_answer'       => $shortAnswer,
                        'is_correct'         => $isCorrect,
                        'marks_earned'       => $marksEarned,
                    ]
                );
            }

            $attempt->update([
                'score'         => $score,
                'total_marks'   => $quiz->total_marks,
                'correct_count' => $correctCount,
                'wrong_count'   => $wrongCount,
                'skipped_count' => $skippedCount,
                'status'        => $request->input('auto_submit') ? 'auto_submitted' : 'submitted',
                'submitted_at'  => now(),
            ]);
        });

        return redirect()->route('quizzes.result', [$id, $attempt->id]);
    }

    /** Show result to student */
    public function result($quizId, $attemptId)
    {
        $attempt = QuizAttempt::where('id', $attemptId)
            ->where('student_id', Auth::id())
            ->with([
                'quiz.questions.options',
                'answers.question.options',
                'answers.selectedOption',
            ])
            ->firstOrFail();

        $quiz = $attempt->quiz;

        return view('student.result', compact('quiz', 'attempt'));
    }
}
