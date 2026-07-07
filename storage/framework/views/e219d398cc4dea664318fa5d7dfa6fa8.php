<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'Smart Discussion Forum'); ?></title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <style>
        :root {
            --ink: #1c2b33;
            --slate: #3d5a6c;
            --paper: #f6f4ee;
            --accent: #2f6f5e;
            --accent-dark: #204b3f;
            --warn: #b3542e;
            --line: #d8d2c4;
            --radius: 6px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Iowan Old Style', 'Georgia', serif;
            background: var(--paper);
            color: var(--ink);
        }
        header.topbar {
            background: var(--ink);
            color: var(--paper);
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            flex-wrap: wrap;
            gap: 8px;
        }
        header.topbar .brand { font-weight: 700; letter-spacing: .04em; text-transform: uppercase; font-size: 15px; }
        header.topbar nav { display: flex; align-items: center; flex-wrap: wrap; }
        header.topbar nav a {
            color: var(--paper);
            text-decoration: none;
            margin-left: 18px;
            font-size: 14px;
            opacity: .85;
            padding: 4px 2px;
            border-bottom: 2px solid transparent;
            transition: opacity 0.15s, border-color 0.15s;
        }
        header.topbar nav a:hover { opacity: 1; }
        header.topbar nav a.active { opacity: 1; border-bottom-color: var(--paper); font-weight: 600; }
        main { max-width: 880px; margin: 0 auto; padding: 32px 24px 80px; }
        h1, h2, h3 { font-family: 'Iowan Old Style', Georgia, serif; color: var(--ink); }
        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 22px;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn:hover { background: var(--accent-dark); }
        .btn.secondary { background: transparent; color: var(--accent); border: 1px solid var(--accent); }
        .btn.warn { background: var(--warn); }
        input, textarea, select {
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            margin-bottom: 12px;
            font-size: 14px;
        }
        label { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; color: var(--slate); display:block; margin-bottom: 4px; }
        .eyebrow { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; text-transform: uppercase; letter-spacing: .08em; font-size: 12px; color: var(--accent); font-weight: 600; }
        .muted { color: var(--slate); font-size: 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .flag { color: var(--warn); font-weight: 600; }
        .error { color: var(--warn); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; margin-top: -6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">Smart Discussion Forum</div>
        <nav>
            <a href="<?php echo e(route('dashboard')); ?>" class="<?php echo e(request()->is('dashboard') ? 'active' : ''); ?>">Dashboard</a>
            <a href="/profile" class="<?php echo e(request()->is('profile') ? 'active' : ''); ?>">My Profile</a>
            <a href="#" id="logoutLink">Log out</a>
        </nav>
    </header>
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
    <script>
        // Every API call attaches the bearer token stored at login.
        const apiToken = localStorage.getItem('sdf_token');
        async function api(path, options = {}) {
            const res = await fetch('/api' + path, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(apiToken ? { Authorization: 'Bearer ' + apiToken } : {}),
                    ...(options.headers || {}),
                },
                body: options.body ? JSON.stringify(options.body) : undefined,
            });
            if (res.status === 401) { window.location = '/'; return; }
            return res.json();
        }
        document.getElementById('logoutLink')?.addEventListener('click', async (e) => {
            e.preventDefault();
            await api('/logout', { method: 'POST' });
            localStorage.removeItem('sdf_token');
            window.location = '/';
        });
    </script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html><?php /**PATH /data/data/com.termux/files/home/forumG/resources/views/layouts/app.blade.php ENDPATH**/ ?>