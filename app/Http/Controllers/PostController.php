<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display all posts in a topic.
     */
    public function index($topic_id)
    {
        $topic = Topic::findOrFail($topic_id);

        $posts = Post::with(['author'])
            ->where('topic_id', $topic_id)
            ->orderBy('posted_at', 'asc')
            ->get();

        return view('messaging.index', compact('topic', 'posts'));
    }

    /**
     * Store a new message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,topic_id',
            'content' => 'required|string|max:5000',
            'attachment_url' => 'nullable|string|max:255'
        ]);

        $post = Post::create([
            'topic_id' => $request->topic_id,
            'author_id' => Auth::id(),
            'content' => $request->content,
            'attachment_url' => $request->attachment_url,
            'posted_at' => now(),
            'is_flagged' => false,
        ]);

        return redirect()->back()->with('success', 'Message sent successfully.');
    }

    /**
     * Delete a message.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->author_id != Auth::id()) {
            abort(403);
        }

        $post->delete();

        return redirect()->back()->with('success', 'Message deleted.');
    }
}
