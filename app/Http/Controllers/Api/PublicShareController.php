<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Reply;

class PublicShareController extends Controller
{
    public function show(Post $post)
    {
        if ($post->is_flagged) {
            abort(404);
        }

        $post->load('author', 'topic.group');

        return view('public.share-post', [
            'post' => $post,
            'topic' => $post->topic,
        ]);
    }

    public function showReply(Reply $reply)
    {
        if ($reply->is_flagged) {
            abort(404);
        }

        $reply->load('author', 'post.topic.group');

        // Also guard against the parent post being flagged/removed after
        // the reply link was already shared out.
        if (!$reply->post || $reply->post->is_flagged) {
            abort(404);
        }

        return view('public.share-post', [
            'post' => $reply,          // reuse the same view; it just reads ->content and ->author
            'topic' => $reply->post->topic,
        ]);
    }
}
