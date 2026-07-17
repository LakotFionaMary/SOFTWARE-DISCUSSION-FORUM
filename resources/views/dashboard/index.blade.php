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
 
    
    (async () => {
        await loadCurrentUser();
 
        const destination = window.CURRENT_ROLE === 'administrator' ? '/dashboard/admin'
            : window.CURRENT_ROLE === 'lecturer' ? '/dashboard/lecturer'
            : '/dashboard/student';
 
   
        window.location.replace(destination + window.location.search);
 
        // Safety net in case replace() is blocked by the browser for any reason.
        setTimeout(() => document.getElementById('fallbackMsg').style.display = 'block', 1500);
    })();
</script>
@endsection
 