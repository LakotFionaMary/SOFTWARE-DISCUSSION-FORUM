@extends('layouts.app')
 
@section('title', 'Dashboard')
 
@section('content')
<div class="eyebrow">Discussion Dashboard</div>
<h1>Taking you to your dashboard…</h1>

@endsection
 
@section('scripts')
<script>
    if (!localStorage.getItem('sdf_token')) {
        window.location.href = '/';
    }
 
    // Single entry point after login: figure out the user's highest role
    // and send them straight to the dashboard built for it, so nobody has
    // to hunt through one giant page for the controls that apply to them.
    (async () => {
        await loadCurrentUser();
 
        const destination = window.CURRENT_ROLE === 'administrator' ? '/dashboard/admin'
            : window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer'
            : '/dashboard/student';
 
        // Preserve ?panel=... (and anything else) so a sidebar link like
        // /dashboard?panel=panel-quizzes still lands on the Quizzes panel
        // after bouncing to the role-specific dashboard URL.
        window.location.replace(destination + window.location.search);
 
        // Safety net in case replace() is blocked by the browser for any reason.
        setTimeout(() => document.getElementById('fallbackMsg').style.display = 'block', 1500);
    })();
</script>
@endsection
 