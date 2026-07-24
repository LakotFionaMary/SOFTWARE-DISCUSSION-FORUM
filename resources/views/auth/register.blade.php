<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register — Smart Discussion Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin:0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #1c2b33; display:flex; align-items:center; justify-content:center; min-height:100vh; padding: 24px; }
        .panel { background:#f6f4ee; width: 420px; padding: 36px 32px; border-radius: 8px; }
        h1 { font-family: 'Iowan Old Style', Georgia, serif; font-size: 22px; margin: 0 0 4px; color:#1c2b33; }
        p.sub { color:#3d5a6c; font-size: 13px; margin: 0 0 20px; }
        label { font-size: 13px; color:#3d5a6c; display:block; margin-bottom:4px; }
        input, select { width:100%; padding:10px 12px; border:1px solid #d8d2c4; border-radius:6px; margin-bottom:14px; font-size:14px; box-sizing:border-box; }
        .rules { background:#fff; border:1px solid #d8d2c4; border-radius:6px; padding:12px 14px; font-size:13px; color:#3d5a6c; max-height:120px; overflow-y:auto; margin-bottom:12px; }
        .agree { display:flex; align-items:flex-start; gap:8px; font-size:13px; color:#1c2b33; margin-bottom:16px; }
        .agree input { width:auto; margin:2px 0 0; }
        button { width:100%; background:#2f6f5e; color:#fff; border:none; padding:12px; border-radius:6px; font-size:14px; cursor:pointer; }
        button:hover { background:#204b3f; }
        .error { color:#b3542e; font-size:13px; margin:-8px 0 12px; }
        .footer { text-align:center; margin-top:18px; font-size:13px; color:#3d5a6c; }
        .footer a { color:#2f6f5e; }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Create your account</h1>
        <p class="sub">Join the academic discussion forum for your class.</p>
        <div id="err" class="error" style="display:none;"></div>
        <form id="registerForm">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" required>
            <label for="email">Email</label>
            <input type="email" id="email" required>
            <label for="password">Password</label>
            <input type="password" id="password" minlength="8" required>
            <label for="password_confirmation">Confirm password</label>
            <input type="password" id="password_confirmation" minlength="8" required>

            <div class="rules" style="margin-bottom: 14px;">
                Every new account starts as a <strong>Student</strong>. If you're a
                lecturer, contact your system administrator after registering and
                they'll assign your account the Lecturer role.
            </div>

            <div class="rules">
                By joining, you agree to keep discussion on-topic, avoid flooding
                threads with irrelevant material, respect selective-communication
                exclusions set by other members, and understand that prolonged
                inactivity may result in warnings and temporary suspension.
            </div>
            <label class="agree">
                <input type="checkbox" id="rules_accepted" required>
                I agree to the forum rules and guidelines
            </label>
            <button type="submit">Register now</button>
        </form>
        <div class="footer">Already registered? <a href="{{ route('login') }}">Log in</a></div>
    </div>
    <script>
    // Bulletproof cookie extraction method
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
        return null;
    }

    async function ensureCsrfCookie() {
        try {
            await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
        } catch (err) {
            console.error('Failed to fetch CSRF baseline cookie:', err);
        }
    }

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const errDiv = document.getElementById('err');
        errDiv.style.display = 'none';

        // Fetch the token fresh before submitting
        await ensureCsrfCookie();

        const token = getCookie('XSRF-TOKEN');

        try {
            const res = await fetch('/api/register', {
                method: 'POST',
                credentials: 'include', 
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': token
                },
                body: JSON.stringify({
                    full_name: document.getElementById('full_name').value,
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value,
                    password_confirmation: document.getElementById('password_confirmation').value,
                    rules_accepted: document.getElementById('rules_accepted').checked,
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                errDiv.textContent = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Registration failed.';
                errDiv.style.display = 'block';
                return;
            }

            if (data.token) {
               localStorage.setItem('sdf_token', data.token);
    const params = new URLSearchParams(window.location.search);
    const redirectTo = params.get('redirect');
    window.location.href = redirectTo || '/dashboard';
            }
            window.location = '/dashboard';

        } catch (networkErr) {
            errDiv.textContent = 'Server response parse failure or dropped connection.';
            errDiv.style.display = 'block';
            console.error(networkErr);
        }
    });
</script>



</body>
</html>
