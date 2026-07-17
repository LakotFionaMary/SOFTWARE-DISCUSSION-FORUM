<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Post;
use App\Models\Quiz;
use App\Models\Reply;
use App\Models\Topic;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class BlacklistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $groupId = $this->resolveGroupId($request);

        if ($user && $groupId && $user->isBlacklistedIn((int) $groupId)) {
            return response()->json([
                'message' => 'Your account is currently suspended from this group.',
            ], 403);
        }

        return $next($request);
    }

    private function resolveGroupId(Request $request): ?int
    {
        if ($groupRoute = $request->route('group')) {
            // Extract ID if it's an object model, otherwise fallback to the raw route value.
            return is_object($groupRoute) ? ($groupRoute->group_id ?? $groupRoute->id) : (int) $groupRoute;
        }

        if ($topicRoute = $request->route('topic')) {
            $topic = $topicRoute instanceof Topic ? $topicRoute : Topic::find($topicRoute);

            return $topic?->group_id;
        }

        if ($postRoute = $request->route('post')) {
            $post = $postRoute instanceof Post ? $postRoute : Post::with('topic')->find($postRoute);

            return $post?->topic?->group_id;
        }

        if ($replyRoute = $request->route('reply')) {
            $reply = $replyRoute instanceof Reply ? $replyRoute : Reply::with('post.topic')->find($replyRoute);

            return $reply?->post?->topic?->group_id;
        }

        if ($quizRoute = $request->route('quiz')) {
            $quiz = $quizRoute instanceof Quiz ? $quizRoute : Quiz::find($quizRoute);

            return $quiz?->group_id;
        }

        return $request->input('group_id') ? (int) $request->input('group_id') : null;
    }
}
