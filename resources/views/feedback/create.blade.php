@extends('layouts.feedback')

@section('title', 'New Request')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <h1 style="font-family: 'Cinzel', serif; font-size: 1.5rem; margin-bottom: 24px;">Submit Feature Request</h1>

    <div class="card">
        <form method="POST" action="/feedback">
            @csrf

            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="150" required placeholder="Short, descriptive title">
                @error('title')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" maxlength="2000" required placeholder="Describe the feature you'd like to see...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="btn btn-primary">Submit Request</button>
                <a href="/feedback" style="color: var(--muted); font-size: 0.9rem;">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
