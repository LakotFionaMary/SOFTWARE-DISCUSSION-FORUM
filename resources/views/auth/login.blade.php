<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log in — Smart Discussion Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin:0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #1c2b33; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .panel { background:#f6f4ee; width: 380px; padding: 36px 32px; border-radius: 8px; }
        h1 { font-family: 'Iowan Old Style', Georgia, serif; font-size: 22px; margin: 0 0 4px; color:#1c2b33; }
        p.sub { color:#3d5a6c; font-size: 13px; margin: 0 0 24px; }
        label { font-size: 13px; color:#3d5a6c; display:block; margin-bottom:4px; }
        input { width:100%; padding:10px 12px; border:1px solid #d8d2c4; border-radius:6px; margin-bottom:14px; font-size:14px; box-sizing:border-box; }
        button { width:100%; background:#2f6f5e; color:#fff; border:none; padding:12px; border-radius:6px; font-size:14px; cursor:pointer; }
        button:hover { background:#204b3f; }
        .error { color:#b3542e; font-size:13px; margin:-8px 0 12px; }
        .footer { text-align:center; margin-top:18px; font-size:13px; color:#3d5a6c; }
        .footer a { color:#2f6f5e; }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Welcome back</h1>
        <p class="sub">Log in to your discussion forum account.</p>
        <div id="err" class="error" style="display:none;"></div>
        <form id="loginForm">
            <label for="email">Email</label>
            <input type="email" id="email" required>
            <label for="password">Password</label>
            <input type="password" id="password" required>
            <button type="submit">Log in</button>
        </form>
        <div class="footer">No account? <a href="{{ route('register') }}">Register here</a></div>
    </div>
    <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errDiv = document.getElementById('err');
        errDiv.style.display = 'none';

        try {
            // Clean token authentication call
            const res = await fetch('/api/login', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                errDiv.textContent = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Unable to log in.';
                errDiv.style.display = 'block';
                return;
            }

            // Save the bearer token returned by AuthController
            if (data.token) {
                localStorage.setItem('sdf_token', data.token);
                window.location.href = '/dashboard';
            } else {
                errDiv.textContent = 'Authentication token missing from server.';
                errDiv.style.display = 'block';
            }

        } catch (networkErr) {
            errDiv.textContent = 'Server connection failure.';
            errDiv.style.display = 'block';
            console.error(networkErr);
        }
    });
</script>


</body>
</html>
