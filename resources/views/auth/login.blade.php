@extends('layouts.feedback')

@section('title', 'Login')

@section('content')
<div style="max-width: 420px; margin: 60px auto;">
    <div class="card">
        <h2 style="font-family: 'Cinzel', serif; font-size: 1.4rem; margin-bottom: 24px; text-align: center;">Login</h2>

        <form method="POST" action="/login">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="remember" name="remember" style="width: auto;">
                <label for="remember" style="margin-bottom: 0; cursor: pointer;">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <p style="text-align: center; margin-top: 18px; font-size: 0.85rem; color: var(--muted);">
            Don't have an account? <a href="/register">Sign up</a>
        </p>
    </div>
</div>
@endsection
