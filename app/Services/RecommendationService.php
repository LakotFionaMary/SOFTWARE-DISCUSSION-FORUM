<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecommendationService
{
    protected string $baseUrl;
    protected string $apiKey;

    /** Only keep this many top-scoring recommendations per user; anything
     * else falls out (deleted), so stale/irrelevant topics stop lingering
     * in the UI forever. */
    protected const TOP_N = 10;

    public function __construct()
    {
        $this->baseUrl = config('services.ml.url', 'http://127.0.0.1:5001');
        $this->apiKey = config('services.ml.key', '');
    }

    /**
     * Regenerate and store topic recommendations for a single user: a
     * persistent, leaderboard-style ranking of topics in the user's
     * joined groups, scored by relevance to their reply history.
     *
     * This does NOT exclude topics the user has already replied to —
     * every topic in their groups is a candidate, ranked by relevance,
     * so the leaderboard always has content and replying to a topic
     * re-ranks it rather than removing it.
     *
     * Scoring blends two signals (see the Flask /recommend endpoint):
     * category activity (how much of the user's history is in that
     * topic's category) and title content-similarity (how closely THIS
     * topic's title matches the titles they've actually engaged with) —
     * so individual topics in the same category no longer get one
     * identical score.
     */
    public function generateForUser(User $user): void
    {
        // Engagement = one history entry per reply, so categories/titles
        // the user replies in more often carry proportionally more weight.
        $history = Post::where('author_id', $user->user_id)
            ->join('topics', 'posts.topic_id', '=', 'topics.topic_id')
            ->whereNotNull('topics.category')
            ->get(['topics.category as category', 'topics.title as title'])
            ->map(fn ($row) => ['category' => $row->category, 'title' => $row->title])
            ->values()
            ->all();

        $groupIds = $user->groups()->pluck('groups.group_id');

        // Candidates: every classified topic in the user's groups,
        // including ones they've already replied to.
        $topics = Topic::whereIn('group_id', $groupIds)
            ->whereNotNull('category')
            ->latest()
            ->limit(50)
            ->get(['topic_id', 'category', 'title']);

        if ($topics->isEmpty()) {
            // No candidates at all — clear out any previously stored
            // recommendations so nothing stale lingers.
            $user->topicRecommendations()->delete();
            return;
        }

        $candidates = $topics->map(fn ($topic) => [
            'topic_id' => $topic->topic_id,
            'category' => $topic->category,
            'title' => $topic->title,
        ])->values()->all();

        $response = $this->callRecommendEndpoint($history, $candidates);

        if ($response === null) {
            return;
        }

        $this->storeRecommendations($user, $response);
    }

    /**
     * Calls the Flask /recommend endpoint. Returns the decoded
     * "recommendations" array, or null on failure.
     */
    protected function callRecommendEndpoint(array $history, array $candidates): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
            ])
                ->timeout(10)
                ->post("{$this->baseUrl}/recommend", [
                    'user_history' => $history,
                    'candidate_topics' => $candidates,
                ]);

            if (! $response->successful()) {
                Log::warning('ML /recommend call failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json('recommendations', []);
        } catch (\Throwable $e) {
            Log::error('ML /recommend call threw an exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Keeps only the top TOP_N recommendations by relevance_score, storing
     * those and deleting any previously stored recommendation for this
     * user that didn't make the cut — otherwise every topic ever scored
     * stays recommended forever, no matter how irrelevant it becomes.
     */
    protected function storeRecommendations(User $user, array $recommendations): void
    {
        $valid = array_values(array_filter(
            $recommendations,
            fn ($rec) => isset($rec['topic_id'], $rec['relevance_score'])
        ));

        usort($valid, fn ($a, $b) => $b['relevance_score'] <=> $a['relevance_score']);

        $top = array_slice($valid, 0, self::TOP_N);
        $topTopicIds = array_column($top, 'topic_id');

        // Drop anything not in the new top N — this is what makes the
        // list actually change over time instead of only ever growing.
        $user->topicRecommendations()
            ->whereNotIn('topic_id', $topTopicIds)
            ->delete();

        foreach ($top as $rec) {
            $user->topicRecommendations()->updateOrCreate(
                ['topic_id' => $rec['topic_id']],
                [
                    'relevance_score' => $rec['relevance_score'],
                    'generated_at' => now(),
                ]
            );
        }
    }
}
