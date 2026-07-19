<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Auto-open a Scheduled quiz the moment its configured start time arrives,
 * so students don't depend on the lecturer remembering to click Publish.
 * Pairs with CloseExpiredQuizzes, which auto-closes it again once its
 * window ends.
 */
class OpenScheduledQuizzes extends Command
{
    protected $signature = 'quizzes:open-scheduled';
    protected $description = 'Auto-open Scheduled quizzes whose scheduled start time (scheduled_date + start_time) has arrived';

    public function handle(): void
    {
        $opened = 0;

        Quiz::query()
            ->where('status', 'Scheduled')
            ->whereHas('configuration')
            ->with('configuration')
            ->chunkById(100, function ($quizzes) use (&$opened) {
                foreach ($quizzes as $quiz) {
                    $config = $quiz->configuration;
                    if (!$config) {
                        continue;
                    }

                    $opensAt = Carbon::parse($config->scheduled_date->toDateString() . ' ' . $config->start_time);
                    $endsAt = $opensAt->copy()->addMinutes((int) $config->duration_minutes);

                    // Only flip Scheduled -> Open while the window is still
                    // live; if it was never published and the window has
                    // already elapsed, leave it for a lecturer to review
                    // rather than silently opening-then-closing it.
                    if (now()->greaterThanOrEqualTo($opensAt) && now()->lessThan($endsAt)) {
                        $quiz->update(['status' => 'Open']);
                        $opened++;
                    }
                }
            }, 'quiz_id');

        $this->info("Opened {$opened} scheduled quiz(zes).");
    }
}
