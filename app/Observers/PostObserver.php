<?php

namespace App\Observers;

use App\Jobs\GenerateUserRecommendations;
use App\Models\Post;

class PostObserver
{
    /**
     * A new reply changes the author's engagement history (category +
     * title profile), so their stored recommendations go stale the
     * instant this happens. Queue a refresh instead of leaving the old
     * scores sitting there until something else triggers a regen.
     */
    public function created(Post $post): void
    {
        GenerateUserRecommendations::dispatch($post->author);
    }
}
