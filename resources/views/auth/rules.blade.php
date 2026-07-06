@extends('layouts.app')

@section('title', 'Forum Rules')

@section('content')
<div class="eyebrow">On-boarding</div>
<h1>Forum rules & guidelines</h1>
<div class="card">
    <ul class="muted">
        <li>Keep posts on-topic; irrelevant material may be flagged and removed.</li>
        <li>Respect other members' selective-communication exclusions.</li>
        <li>Two unresolved inactivity warnings result in a temporary suspension.</li>
        <li>Quizzes are individual, timed, and auto-submitted when time expires.</li>
        <li>Be respectful — administrators may blacklist accounts that violate these rules.</li>
    </ul>
    <a class="btn" href="{{ route('dashboard') }}">I understand, continue</a>
</div>
@endsection
