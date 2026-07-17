<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\TopicRecommendation;
use Illuminate\Http\Request;

/**
 * ML Classification and Recommendation module (SDD 5.8).
 *
 * Topic classification happens automatically on creation (see
 * TopicController::store). This controller covers the recommendation
 * feed: every topic in a user's groups ranked by its share of posts,
 * seen and unseen topics alike.
 */
class RecommendationController extends Controller
{
    /**
     * Home feed recommendations for the authenticated user.
     *
     * Ranks every topic in the user's groups (both topics they've already
     * posted in and ones they haven't) by that topic's share of posts
     * relative to all posts across those topics — the topic with the most
     * posts shows up first, not just the "unseen" ones. Recomputed live
     * from the DB on every request (withCount('posts') below), so this
     * naturally auto-updates each time the dashboard loads — no stale
     * cached job data involved.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $groupIds = $user->groups()->pluck('groups.group_id');

        // withCount('posts') so the dashboard's "N posts" line and the
        // ranking itself are both driven by the real, current post count.
        $topics = Topic::whereIn('group_id', $groupIds)
            ->withCount('posts')
            ->get();

        $totalPosts = max($topics->sum('posts_count'), 1);

        $recommendations = $topics->map(function (Topic $topic) use ($user, $totalPosts) {
            $score = round(min(1, $topic->posts_count / $totalPosts), 3);

            $rec = TopicRecommendation::updateOrCreate(
                ['user_id' => $user->user_id, 'topic_id' => $topic->topic_id],
                ['relevance_score' => $score, 'generated_at' => now()]
            );
            // Attach the already-counted topic so the response doesn't need
            // a second query that would drop posts_count.
            $rec->setRelation('topic', $topic);

            return $rec;
        })->sortByDesc('relevance_score')->values()->take(10);

        return response()->json($recommendations);
    }
}
