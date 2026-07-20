<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Topic;
use App\Services\TopicClassifierService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Topic Management and Export Module (SDD 5.3).
 *
 * Lets a member launch a new discussion thread (auto-classified by the ML
 * module), view a topic-focused filtered stream of posts, and export a
 * full topic thread to PDF.
 */
class TopicController extends Controller
{
    public function __construct(private TopicClassifierService $classifier)
    {
    }

    /**
     * Supports optional ?search= (title match) and ?category= filters on
     * top of the existing pagination, so the group page can narrow down
     * the full topic list instead of only ever showing page 1.
     */
    public function index(Group $group, Request $request)
    {
        $query = $group->topics()->withCount('posts')->latest();

        if ($request->filled('search')) {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $request->query('search'));
            $query->where('title', 'like', '%' . $escaped . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return response()->json(
            $query->paginate(20)->withQueryString()
        );
    }

    /**
     * Distinct category list for this group, used to populate the filter
     * dropdown. Deliberately separate from index() so the dropdown's
     * options aren't limited to whatever happens to be on the current
     * page/search results.
     */
    public function categories(Group $group)
    {
        $categories = $group->topics()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            // FIXED: pluck() keeps each row's own primary key as the
            // collection key (e.g. topic_id), which is essentially never a
            // clean 0..n-1 sequence. json_encode()'ing a non-sequential
            // collection serializes it as a JSON *object* instead of an
            // array, which is exactly why the frontend's `cats.map(...)`
            // was throwing "cats.map is not a function" — it received
            // {"5": "Networking", "12": "Security"} instead of
            // ["Networking", "Security"]. ->values() reindexes to a clean
            // array before it goes out.
            ->values();

        return response()->json($categories);
    }

    /** Launching new discussion topic thread use case (SDD Table 33). */
    public function store(Request $request, Group $group)
    {
        $request->validate(['title' => 'required|string|max:255']);

        if ($request->user()->isBlacklistedIn($group->group_id)) {
            return response()->json(['message' => 'You are blacklisted from posting in this group.'], 403);
        }

        $topic = Topic::create([
            'group_id' => $group->group_id,
            'title' => $request->title,
            'created_by' => $request->user()->user_id,
            'category' => $this->classifier->classify($request->title),
        ]);

        return response()->json($topic, 201);
    }

    /** Topic-Focused View: only chats belonging to this topic, isolated from unrelated discussion. */
    public function show(Request $request, Topic $topic)
    {
        $userId = $request->user()->user_id;

        return response()->json(
            $topic->load([
                'creator',
                // Selective communication: hide posts that exclude the requesting user
                // (mirrors PostController::index()).
                'posts' => fn ($q) => $q->whereDoesntHave('exclusions', fn ($q2) => $q2->where('excluded_user_id', $userId)),
                'posts.author',
                'posts.replies.author',
            ])
        );
    }

    /** Post export use case (SDD Table 34): export a filtered topic thread to PDF. */
    public function export(Topic $topic)
    {
        $topic->load(['creator', 'group', 'posts.author', 'posts.replies.author']);

        $pdf = Pdf::loadView('topics.export', ['topic' => $topic]);

        return $pdf->download("topic-{$topic->topic_id}-{$topic->title}.pdf");
    }
}
