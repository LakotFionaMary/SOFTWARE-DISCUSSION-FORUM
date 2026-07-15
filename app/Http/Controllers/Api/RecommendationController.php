<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;
use Illuminate\Http\Request;

/**
 * ML Classification and Recommendation module (SDD 5.8).
 *
 * Topic classification happens automatically on creation (see
 * TopicController::store). This controller covers the recommendation
 * feed: a persistent, leaderboard-style ranking of topics in the user's
 * joined groups, scored by relevance to their reply history — see
 * RecommendationService::generateForUser() for the actual scoring logic
 * (category activity + title content-similarity via the Flask ML
 * service).
 *
 * Re-ranks live on every load, since the leaderboard includes topics the
 * user has already replied to (replying re-ranks a topic rather than
 * removing it) — this is intentionally NOT a cached/stale read.
 *
 * (A previous version of this controller recomputed scores locally using
 * TopicClassifierService::relevanceScore(), a placeholder formula that
 * ignored the ML service and title content entirely. Removed in favor of
 * routing through RecommendationService, which is also what keeps
 * recommendations fresh in the background after each post — see
 * App\Jobs\GenerateUserRecommendations, dispatched from
 * PostController::store.)
 */
class RecommendationController extends Controller
{
    public function __construct(private RecommendationService $recommendations)
    {
    }

    /** Home feed recommendations for the authenticated user. */
    public function index(Request $request)
    {
        $user = $request->user();

        // Re-rank live, every load — this is the leaderboard behavior:
        // topics already replied to stay in the list, just re-scored.
        $this->recommendations->generateForUser($user);

        $recommendations = $user->topicRecommendations()
            ->with('topic')
            ->orderByDesc('relevance_score')
            ->get();

        return response()->json($recommendations);
    }
}
