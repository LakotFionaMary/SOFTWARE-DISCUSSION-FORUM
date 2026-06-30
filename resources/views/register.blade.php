@extends('layout')

@section('title', 'Register')
@section('card-title', 'Create an Account')

@section('content')

{{-- Rules Modal Overlay --}}
<div id="rules-modal" style="
    position:fixed; inset:0; z-index:9999;
    background:rgba(0,0,0,0.55);
    display:flex; align-items:center; justify-content:center;
    padding:1rem;
">
    <div style="
        background:white; border-radius:12px;
        width:100%; max-width:460px;
        box-shadow:0 8px 32px rgba(0,0,0,0.18);
        overflow:hidden;
    ">
        {{-- Modal header --}}
        <div style="padding:1.5rem 1.5rem 0;">
            <h3 style="margin:0 0 0.4rem; color:#1e1e2e; font-size:1.2rem;">Forum rules and conduct</h3>
            <p style="margin:0 0 1rem; color:red; font-size:1em;">
                Before you can join, please review and accept the following:
            </p>
        </div>

        {{-- Rules list --}}
        <div style="padding:0 1.5rem;">
            <ul style="list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:0.6rem;">
                <li style="font-family:'sans-serif'; display:flex; gap:0.6rem; align-items:flex-start; font-size:1rem; color:green;">
                    <span style="color:#4f46e5; font-weight:bold; flex-shrink:0;">•</span>
                    Keep posts relevant to the topic — off-topic or repeated flooding may be moderated.
                </li>
                <li style="display:flex; gap:0.6rem; font-family:'sans-serif'; align-items:flex-start; font-size:1em; color:green;">
                    <span style="color:#4f46e5; font-weight:bold; flex-shrink:0;">•</span>
                    Treat all members with respect — no harassment or abusive language.
                </li>
                <li style="font-family:'sans-serif'; display:flex; gap:0.6rem; align-items:flex-start; font-size:1em; color:green;">
                    <span style="color:#4f46e5; font-weight:bold; flex-shrink:0;">•</span>
                    Stay active — prolonged inactivity may result in warnings and temporary blacklisting.
                </li>
                <li style="font-family:'sans-serif'; display:flex; gap:0.6rem; align-items:flex-start; font-size:1em; color:green;">
                    <span style="color:#4f46e5; font-weight:bold; flex-shrink:0;">•</span>
                    Quiz submissions and grading decisions made on the platform are final.
                </li>
            </ul>

            <div style="
                margin:1rem 0;
                background:#f5f5ff;
                border-left:3px solid #4f46e5;
                border-radius:0 6px 6px 0;
                padding:0.65rem 0.85rem;
                font-size:0.9rem; color:orange;
            ">
                Full terms and conditions available at any time from your profile settings.
            </div>

            {{-- Checkbox --}}
            <label style="font-weight:bold; display:flex; align-items:flex-start; gap:0.6rem; font-size:0.875rem; color:#333; cursor:pointer; margin-bottom:1.25rem;">
                <input type="checkbox" id="rules-checkbox" style=" margin-top:2px; accent-color:#4f46e5; width:15px; height:15px; flex-shrink:0;">
                I have read and agree to the rules of the Smart Discussion Forum
            </label>
        </div>

        {{-- Modal actions --}}
        <div style="
            display:flex; gap:0.75rem;
            padding:1rem 1.5rem;
            border-top:1px solid #eee;
            background:#fafafa;
        ">
            <a href="/login" style="
                flex:1; text-align:center;
                padding:0.65rem;
                border:1px solid #ccc;
                border-radius:6px;
                color:#555;
                text-decoration:none;
                font-size:0.9rem;
            ">Decline</a>

            <button id="agree-btn" onclick="acceptRules()" disabled style="
                flex:2;
                padding:0.65rem;
                background:#4f46e5;
                color:white;
                border:none;
                border-radius:6px;
                font-size:0.9rem;
                cursor:not-allowed;
                opacity:0.5;
                transition:opacity 0.2s, cursor 0.2s;
            ">Agree and continue</button>
        </div>
    </div>
</div>

{{-- Registration form (hidden until rules accepted) --}}
<div id="register-form" style="display:none;">
    <form method="POST" action="/register">
        @csrf

        <div class="form-group">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name') }}"
                placeholder="John Doe"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@example.com"
                required
            >
        </div>

        <div class="form-group">
            <label for="role">Register as</label>
            <select name="role" id="role" style="
                width:100%; padding:0.6rem 0.9rem;
                border:1px solid #ccc;
                border-radius:6px;
                font-size:1rem;
                color:#333;
                background:white;
            ">
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Min. 8 characters"
                required
            >
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="Repeat your password"
                required
            >
        </div>

        {{-- Hidden field confirming rules were accepted --}}
        <input type="hidden" name="rules_accepted" value="1">

        <button type="submit" class="btn">Create Account</button>
    </form>

    <p class="link-text">
        Already have an account? <a href="/login">Login here</a>
    </p>
</div>

<script>
    // Enable agree button only when checkbox is ticked
    document.getElementById('rules-checkbox').addEventListener('change', function () {
        const btn = document.getElementById('agree-btn');
        btn.disabled = !this.checked;
        btn.style.opacity = this.checked ? '1' : '0.5';
        btn.style.cursor = this.checked ? 'pointer' : 'not-allowed';
    });

    function acceptRules() {
        document.getElementById('rules-modal').style.display = 'none';
        document.getElementById('register-form').style.display = 'block';
    }

    // If validation failed and form was submitted, skip the modal
    @if($errors->any() || old('name'))
        document.getElementById('rules-modal').style.display = 'none';
        document.getElementById('register-form').style.display = 'block';
    @endif
</script>

@endsection
