<?php

namespace App\Console\Commands;

use App\Jobs\GenerateUserRecommendations;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateAllRecommendations extends Command
{
    protected $signature = 'recommendations:generate {user_id? : Only generate for a single user_id}';

    protected $description = 'Queue recommendation generation for one user, or all users with at least one post';

    public function handle(): int
    {
        $userId = $this->argument('user_id');

        $users = $userId
            ? User::where('user_id', $userId)->get()
            : User::whereHas('posts')->get();

        if ($users->isEmpty()) {
            $this->warn('No matching users found.');
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            GenerateUserRecommendations::dispatch($user);
        }

        $this->info("Queued recommendation generation for {$users->count()} user(s).");
        return self::SUCCESS;
    }
}
