<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $topic->title }} — Smart Discussion Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Open Graph tags so shared links render a real preview card in
         WhatsApp/Twitter/Facebook/LinkedIn instead of a bare URL --}}
    <meta property="og:title" content="{{ $topic->title }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($post->content, 150) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ url()->current() }}">

    <style>
        :root {
            --ink: #15202b;
            --slate: #55707d;
            --paper: #f4f1ea;
            --accent: #2f6f5e;
            --accent-light: #3f8b73;
            --accent-dark: #1f4c3f;
            --line: #e2ddd0;
            --card: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at 12% -10%, rgba(47,111,94,.14), transparent 45%),
                radial-gradient(circle at 90% 110%, rgba(47,111,94,.08), transparent 40%),
                var(--paper);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--ink);
        }

        .preview-wrap { width: 100%; max-width: 500px; }

        .preview-brand {
            display: flex; align-items: center; gap: 8px;
            font-size: 12.5px; font-weight: 700; letter-spacing: .04em;
            text-transform: uppercase; color: var(--slate);
            margin-bottom: 16px; padding-left: 2px;
        }
        .preview-brand-icon {
            width: 22px; height: 22px; border-radius: 6px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; flex-shrink: 0;
        }
        .preview-brand-icon svg { width: 12px; height: 12px; }

        .preview-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(21,32,43,.04), 0 12px 32px rgba(21,32,43,.08);
        }

        .preview-header {
            padding: 26px 28px 20px;
            border-bottom: 1px solid var(--line);
        }
        .preview-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11.5px; font-weight: 700; letter-spacing: .03em;
            text-transform: uppercase; color: var(--accent);
            background: rgba(47,111,94,.09);
            padding: 4px 10px; border-radius: 20px;
            margin-bottom: 14px;
        }
        .preview-badge svg { width: 12px; height: 12px; }

        .preview-topic {
            font-family: 'Iowan Old Style', Georgia, serif;
            font-size: 23px; font-weight: 600; line-height: 1.3;
            margin: 0 0 6px;
            color: var(--ink);
        }
        .preview-group {
            font-size: 13px; color: var(--slate); margin: 0;
            display: flex; align-items: center; gap: 6px;
        }
        .preview-group svg { width: 13px; height: 13px; opacity: .7; }

        .preview-body { padding: 22px 28px 26px; }

        .preview-author-row {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 14px;
        }
        .preview-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-light), var(--accent-dark));
            color: #fff; font-weight: 700; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .preview-author-name { font-size: 14px; font-weight: 600; color: var(--ink); }
        .preview-author-label { font-size: 12px; color: var(--slate); }

        .preview-text {
            font-size: 15px; line-height: 1.65; color: #2c3e46;
            margin: 0 0 24px;
            white-space: pre-wrap;
        }

        .preview-cta {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            background: linear-gradient(135deg, var(--accent-light), var(--accent-dark));
            color: #fff; text-decoration: none;
            padding: 13px; border-radius: 11px;
            font-weight: 600; font-size: 14.5px;
            transition: transform .15s ease, box-shadow .15s ease;
            box-shadow: 0 4px 14px rgba(47,111,94,.28);
        }
        .preview-cta:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(47,111,94,.35); }
        .preview-cta svg { width: 15px; height: 15px; }

        .preview-note {
            text-align: center; font-size: 12.5px; color: var(--slate);
            margin: 14px 0 0;
        }
        .preview-note a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .preview-note a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .preview-header { padding: 22px 20px 18px; }
            .preview-body { padding: 18px 20px 22px; }
            .preview-topic { font-size: 20px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition-duration: .001ms !important; }
        }
    </style>
</head>
<body>
    <div class="preview-wrap">
        <div class="preview-brand">
            <span class="preview-brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
                </svg>
            </span>
            Smart Discussion Forum
        </div>

        <div class="preview-card">
            <div class="preview-header">
                <span class="preview-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" opacity="0"/><circle cx="12" cy="12" r="9"/><path d="M12 8v4l2.5 2.5"/></svg>
                    Shared discussion
                </span>
                <h1 class="preview-topic">{{ $topic->title }}</h1>
                <p class="preview-group">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    {{ $topic->group->name ?? 'Discussion Group' }}
                </p>
            </div>

            <div class="preview-body">
                @php
                    $authorName = $post->author->full_name ?? $post->author->name ?? 'A student';
                    $initial = strtoupper(substr($authorName, 0, 1));
                @endphp
                <div class="preview-author-row">
                    <div class="preview-avatar">{{ $initial }}</div>
                    <div>
                        <div class="preview-author-name">{{ $authorName }}</div>
                        <div class="preview-author-label">posted in this discussion</div>
                    </div>
                </div>

                <p class="preview-text">{{ \Illuminate\Support\Str::limit($post->content, 400) }}</p>

                <a class="preview-cta" href="{{ route('login') }}?redirect={{ urlencode('/dashboard/student?group_id=' . $topic->group_id . '&group_name=' . urlencode($topic->group->name ?? '') . '&topic_id=' . $topic->topic_id . '&topic_title=' . urlencode($topic->title) . '&post_id=' . $post->post_id) }}">
                    View full discussion
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <p class="preview-note">
                    Not a member yet?
                    <a href="{{ route('register') }}?redirect={{ urlencode('/dashboard/student?group_id=' . $topic->group_id . '&group_name=' . urlencode($topic->group->name ?? '') . '&topic_id=' . $topic->topic_id . '&topic_title=' . urlencode($topic->title) . '&post_id=' . $post->post_id) }}">
                        Create an account
                    </a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
