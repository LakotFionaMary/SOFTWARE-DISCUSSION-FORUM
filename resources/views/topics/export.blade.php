<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1c2b33; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .meta { color: #3d5a6c; font-size: 11px; margin-bottom: 16px; }
        .post { border-bottom: 1px solid #d8d2c4; padding: 10px 0; }
        .author { font-weight: bold; }
        .reply { margin-left: 18px; padding: 6px 0; border-left: 2px solid #d8d2c4; padding-left: 10px; }
    </style>
</head>
<body>
    <h1>{{ $topic->title }}</h1>
    <div class="meta">
        Group: {{ $topic->group->name }} &middot;
        Started by {{ $topic->creator->full_name }} &middot;
        Category: {{ $topic->category ?? 'General' }} &middot;
        Exported {{ now()->format('d M Y, H:i') }}
    </div>

    @foreach ($topic->posts as $post)
        <div class="post">
            <div class="author">{{ $post->author->full_name }} <span class="meta">{{ $post->posted_at->format('d M Y, H:i') }}</span></div>
            <div>{{ $post->content }}</div>

            @foreach ($post->replies as $reply)
                <div class="reply">
                    <div class="author">{{ $reply->author->full_name }} <span class="meta">{{ $reply->replied_at->format('d M Y, H:i') }}</span></div>
                    <div>{{ $reply->content }}</div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
