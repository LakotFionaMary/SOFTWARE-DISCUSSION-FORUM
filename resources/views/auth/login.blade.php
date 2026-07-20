<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log in — Smart Discussion Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --ink: #1c2b33;
            --slate: #3d5a6c;
            --paper: #f6f4ee;
            --paper-dim: #ece8db;
            --accent: #2f6f5e;
            --accent-dark: #204b3f;
            --warn: #b3542e;
            --line: #d8d2c4;
            --seal: #a8792f;
            --seal-dim: #f2e8d5;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--ink);
            min-height: 100vh;
            display: flex;
        }

        .auth-shell {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        

        /* ---------- Left: brand / institution panel ---------- */
        .auth-brand {
    flex: 0 0 38%;
    min-width: 320px;
    background:
        radial-gradient(circle at 15% 20%, rgba(47,111,94,.12), transparent 45%),
        linear-gradient(160deg, var(--ink) 0%, #10191f 100%);
    color: var(--paper);
    padding: 56px 44px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}

.auth-formside {
    flex: 1;
    background: var(--paper);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}
        .auth-brand::before {
            /* faint concentric ring, evokes a seal/stamp motif in the background */
            content: '';
            position: absolute;
            width: 620px; height: 620px;
            border: 1px solid rgba(246,244,238,.06);
            border-radius: 50%;
            right: -220px; bottom: -260px;
        }
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 460px; height: 460px;
            border: 1px solid rgba(246,244,238,.05);
            border-radius: 50%;
            right: -140px; bottom: -180px;
        }
        .auth-brand-mark {
            display: flex; align-items: center; gap: 10px;
            font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            font-size: 14px; position: relative; z-index: 1;
        }
        .auth-brand-mark-icon {
            width: 30px; height: 30px; border-radius: 8px;
            background: rgba(47,111,94,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .auth-brand-body { position: relative; z-index: 1; max-width: 380px; }
        .auth-seal {
            width: 58px; height: 58px; border-radius: 50%;
            border: 1.5px solid var(--seal);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 22px;
            color: var(--seal);
        }
        .auth-seal svg { width: 26px; height: 26px; }
        .auth-brand h1 {
            font-family: 'Iowan Old Style', Georgia, serif;
            font-size: 32px; line-height: 1.25;
            margin: 0 0 14px;
            font-weight: 500;
        }
        .auth-brand p {
            font-size: 14.5px; line-height: 1.65;
            color: rgba(246,244,238,.68);
            margin: 0;
        }
        .auth-brand-footer {
            position: relative; z-index: 1;
            font-size: 12px; letter-spacing: .03em;
            color: rgba(246,244,238,.4);
            border-top: 1px solid rgba(246,244,238,.12);
            padding-top: 16px;
        }

        /* ---------- Right: form panel ---------- */
        .auth-formside {
            width: 460px;
            flex-shrink: 0;
            background: var(--paper);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .auth-card { width: 100%; max-width: 340px; }
        .auth-card h2 {
            font-family: 'Iowan Old Style', Georgia, serif;
            font-size: 50px; margin: 0 0 6px; color: var(--ink); font-weight: 500;
        }
        .auth-card p.sub {
            color: var(--slate); font-size: 13.5px; margin: 0 0 30px;
        }

        .field { margin-bottom: 16px; }
        .field label {
            font-size: 12.5px; font-weight: 600; letter-spacing: .02em;
            color: var(--slate); display: block; margin-bottom: 6px;
        }
        .field-input {
            position: relative;
            display: flex; align-items: center;
        }
        .field-input svg.field-icon {
            position: absolute; left: 12px; width: 16px; height: 16px;
            color: var(--slate); opacity: .55; pointer-events: none;
        }
        .field-input input {
            width: 100%; padding: 11px 12px 11px 38px;
            border: 1px solid var(--line); border-radius: 7px;
            font-size: 14px; font-family: inherit;
            background: #fff; color: var(--ink);
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .field-input input:focus {
            outline: none; border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(47,111,94,.14);
        }
        .field-input.has-toggle input { padding-right: 38px; }
        .toggle-visibility {
            position: absolute; right: 10px;
            background: none; border: none; cursor: pointer;
            width: 26px; height: 26px; padding: 0;
            display: flex; align-items: center; justify-content: center;
            color: var(--slate); opacity: .6; border-radius: 5px;
        }
        .toggle-visibility:hover { opacity: 1; background: rgba(47,111,94,.08); }
        .toggle-visibility svg { width: 16px; height: 16px; }

        .field-row-top { display: flex; justify-content: space-between; align-items: baseline; }
        .forgot-link { font-size: 12.5px; color: var(--accent); text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }

        button[type="submit"] {
            width: 100%;
            background: var(--accent); color: #fff; border: none;
            padding: 12px; border-radius: 7px; font-size: 14.5px; font-weight: 600;
            cursor: pointer; margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: background .15s ease, transform .1s ease;
            font-family: inherit;
        }
        button[type="submit"]:hover:not(:disabled) { background: var(--accent-dark); }
        button[type="submit"]:active:not(:disabled) { transform: translateY(1px); }
        button[type="submit"]:disabled { opacity: .75; cursor: default; }
        button[type="submit"] svg { width: 15px; height: 15px; }

        .spinner {
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .error-banner {
            display: none;
            align-items: flex-start; gap: 8px;
            background: #fbeee7; border: 1px solid #ecc9b6;
            color: var(--warn); font-size: 13px; line-height: 1.4;
            padding: 10px 12px; border-radius: 7px; margin-bottom: 16px;
        }
        .error-banner svg { width: 15px; height: 15px; flex-shrink: 0; margin-top: 1px; }

        .footer { text-align: center; margin-top: 26px; font-size: 13px; color: var(--slate); }
        .footer a { color: var(--accent); text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }

        input:focus-visible, button:focus-visible, a:focus-visible {
            outline: 2px solid var(--accent); outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: .001ms !important; transition-duration: .001ms !important; }
        }

        @media (max-width: 860px) {
            .auth-brand { display: none; }
            .auth-formside { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-mark">
                <span class="auth-brand-mark-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
  <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
  <path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.284a.75.75 0 0 1-.46.711 47.87 47.87 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.87 47.87 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286.921.304 1.83.634 2.726.99v1.27a1.5 1.5 0 0 0-.14 2.508c-.09.38-.222.753-.397 1.11.452.213.901.434 1.346.66a6.727 6.727 0 0 0 .551-1.607 1.5 1.5 0 0 0 .14-2.67v-.645a48.549 48.549 0 0 1 3.44 1.667 2.25 2.25 0 0 0 2.12 0Z" />
  <path d="M4.462 19.462c.42-.419.753-.89 1-1.395.453.214.902.435 1.347.662a6.742 6.742 0 0 1-1.286 1.794.75.75 0 0 1-1.06-1.06Z" />
</svg>
</span> Smart Discussion Forum
            </div>
            <div class="auth-brand-body">
                <div class="auth-seal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2 3 7v6c0 5 4 9 9 9s9-4 9-9V7l-9-5Z"/>
                        <path d="m8.5 12 2.5 2.5L16 9.5"/>
                    </svg>
                </div>
                <h1>Where the discussion continues.</h1>
                <p>Log in to reach your groups, follow  topics, and keep up with your quizzes and grades — all in one place.</p>
            </div>
            <div class="auth-brand-footer">A discussion &amp; learning space for students, lecturers, and administrators.</div>
        </div>

        <div class="auth-formside">
            <div class="auth-card">
                <h2>Welcome Back</h2>
                <p class="sub">Log in to your discussion forum account.</p>

                <div id="err" class="error-banner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span id="errText"></span>
                </div>

                <form id="loginForm">
                    <div class="field">
                        <label for="email">Email</label>
                        <div class="field-input">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" opacity="0"/><path d="M22 6 12 13 2 6"/><path d="M2 6h20v12H2z"/></svg>
                            <input type="email" id="email" placeholder="you@gmail.com" required autocomplete="email">
                        </div>
                    </div>

                    <div class="field">
                        <div class="field-row-top">
                            <label for="password">Password</label>
                        </div>
                        <div class="field-input has-toggle">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <input type="password" id="password" placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="toggle-visibility" id="togglePw" aria-label="Show password">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" id="submitBtn">
                        <span id="submitLabel">Log in</span>
                    </button>
                </form>

                <div class="footer">No account? <a href="{{ route('register') }}">Register here</a></div>
            </div>
        </div>
    </div>

    <script>
    const pwInput = document.getElementById('password');
    const toggleBtn = document.getElementById('togglePw');
    toggleBtn.addEventListener('click', () => {
        const showing = pwInput.type === 'text';
        pwInput.type = showing ? 'password' : 'text';
        toggleBtn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        toggleBtn.innerHTML = showing
            ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-5.94M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a21.6 21.6 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    });

    const form = document.getElementById('loginForm');
    const errDiv = document.getElementById('err');
    const errText = document.getElementById('errText');
    const submitBtn = document.getElementById('submitBtn');
    const submitLabel = document.getElementById('submitLabel');

    function setLoading(isLoading) {
        submitBtn.disabled = isLoading;
        submitLabel.innerHTML = isLoading
            ? '<span class="spinner"></span> Logging in…'
            : 'Log in';
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errDiv.style.display = 'none';
        setLoading(true);

        try {
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
                errText.textContent = data.message || Object.values(data.errors || {}).flat().join(' ') || 'Unable to log in.';
                errDiv.style.display = 'flex';
                setLoading(false);
                return;
            }

            if (data.token) {
                localStorage.setItem('sdf_token', data.token);
                window.location.href = '/dashboard';
            } else {
                errText.textContent = 'Authentication token missing from server.';
                errDiv.style.display = 'flex';
                setLoading(false);
            }
        } catch (networkErr) {
            errText.textContent = 'Server connection failure.';
            errDiv.style.display = 'flex';
            setLoading(false);
            console.error(networkErr);
        }
    });
    </script>
</body>
</html>
