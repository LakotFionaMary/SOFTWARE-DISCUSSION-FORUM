<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseExpiredQuizzes extends Command
{
    protected $signature = 'quizzes:close-expired';
    protected $description = 'Auto-close quizzes whose scheduled window (scheduled_date + start_time + duration_minutes) has elapsed';

    public function handle(): void
    {
        $closed = 0;

        // Only "Open" quizzes with a configuration can be evaluated for auto-close.
        Quiz::query()
            ->where('status', 'Open')
            ->whereHas('configuration')
            ->with('configuration')
            ->chunkById(100, function ($quizzes) use (&$closed) {
                foreach ($quizzes as $quiz) {
                    $config = $quiz->configuration;
                    if (!$config) {
                        continue;
                    }

                    $endsAt = Carbon::parse($config->scheduled_date->toDateString() . ' ' . $config->start_time)
                        ->addMinutes((int) $config->duration_minutes);

                    if (now()->greaterThanOrEqualTo($endsAt)) {
                        $quiz->update(['status' => 'Closed']);
                        $closed++;
                    }
                }
            }, 'quiz_id');

        $this->info("Closed {$closed} expired quiz(zes).");
    }
}
