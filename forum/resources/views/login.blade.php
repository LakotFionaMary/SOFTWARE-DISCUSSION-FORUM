@extends('layout')

@section('title', 'Login')

@section('card-title', 'Welcome Back')

@section('content')

    <form method="POST" action="/login">
        @csrf

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
            <label for="password">Password</label>
            <input 
                type="password" 
                id="password"
                name="password" 
                placeholder="••••••••"
                required
            >
        </div>

        <div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
            <input type="checkbox" name="remember" id="remember" style="width:auto;">
            <label for="remember" style="margin:0;">Remember me</label>
        </div>

        <button type="submit" class="btn">Login</button>
    </form>

    <p class="link-text">
        Don't have an account? <a href="/register">Register here</a>
    </p>

@endsection
