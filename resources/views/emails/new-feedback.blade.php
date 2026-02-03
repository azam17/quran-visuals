<!doctype html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #0b0b0d; color: #f2f2f2; padding: 32px; }
        .card { background: #141318; border: 1px solid #2a2833; border-radius: 12px; padding: 24px; max-width: 560px; margin: 0 auto; }
        h1 { color: #c28b3b; font-size: 14px; text-transform: uppercase; letter-spacing: 0.06em; margin: 0 0 20px; }
        h2 { font-size: 18px; margin: 0 0 8px; }
        .meta { color: #b2b0bd; font-size: 13px; margin-bottom: 16px; }
        .description { background: #1b1a22; border-radius: 8px; padding: 16px; font-size: 14px; line-height: 1.6; white-space: pre-wrap; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #c28b3b; color: #fff; text-decoration: none; border-radius: 8px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Quran Visuals - New Feature Request</h1>
        <h2>{{ $feedbackItem->title }}</h2>
        <div class="meta">by {{ $feedbackItem->user->name }} ({{ $feedbackItem->user->email }}) &middot; {{ $feedbackItem->created_at->format('M j, Y g:ia') }}</div>
        <div class="description">{{ $feedbackItem->description }}</div>
        <a href="{{ url('/feedback/' . $feedbackItem->id) }}" class="btn">View Request</a>
    </div>
</body>
</html>
