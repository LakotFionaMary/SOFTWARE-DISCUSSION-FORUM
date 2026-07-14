<?php

namespace App\Observers;

use App\Jobs\GenerateUserRecommendations;
use App\Models\Post;

class PostObserver
{
    /**
     * Whenever a user replies to a topic, that's an engagement signal —
     * refresh their recommendations in the background so it's cheap
     * and never blocks the reply request itself.
     */
    public function created(Post $post): void
    {
        $author = $post->author;

        if ($author) {
            GenerateUserRecommendations::dispatch($author);
        }
    }
}
