@extends('layouts.feedback')

@section('title', $feedbackItem->title)

@section('extra-css')
<style>
    .detail-header {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 24px;
    }

    .detail-vote {
        flex-shrink: 0;
    }

    .vote-btn-lg {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 12px 18px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(8, 9, 12, 0.7);
        color: var(--muted);
        cursor: pointer;
        font-family: inherit;
        font-size: 0.85rem;
        transition: all 0.2s;
    }

    .vote-btn-lg:hover { border-color: var(--accent); color: var(--accent); }

    .vote-btn-lg.voted {
        border-color: var(--accent);
        background: rgba(194,139,59,0.15);
        color: var(--accent);
    }

    .vote-btn-lg .arrow { font-size: 1.2rem; }
    .vote-btn-lg .count { font-weight: 600; font-size: 1.1rem; }

    .status-badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: capitalize;
    }

    .status-under_review { background: rgba(178,176,189,0.15); color: var(--muted); }
    .status-planned { background: rgba(100,140,255,0.15); color: #8facff; }
    .status-in_progress { background: rgba(194,139,59,0.2); color: var(--accent); }
    .status-done { background: rgba(74,224,138,0.15); color: #6fe0a0; }

    .admin-response-box {
        margin-top: 24px;
        padding: 18px 20px;
        border-radius: 12px;
        border-left: 3px solid var(--accent);
        background: rgba(194,139,59,0.06);
    }

    .admin-response-box h3 {
        font-size: 0.85rem;
        color: var(--accent);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .admin-controls {
        margin-top: 28px;
        padding: 20px;
        border-radius: 14px;
        border: 1px solid rgba(194,139,59,0.2);
        background: rgba(194,139,59,0.04);
    }

    .admin-controls h3 {
        font-size: 0.9rem;
        color: var(--accent);
        margin-bottom: 16px;
        font-weight: 600;
    }

    .admin-controls .control-row {
        display: flex;
        gap: 8px;
        align-items: flex-end;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .admin-controls select,
    .admin-controls textarea {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(8, 9, 12, 0.7);
        color: var(--text);
        font-size: 0.85rem;
        font-family: inherit;
    }

    .admin-controls textarea {
        width: 100%;
        min-height: 80px;
        resize: vertical;
    }
</style>
@endsection

@section('content')
<p style="margin-bottom: 20px;">
    <a href="/feedback" style="color: var(--muted); font-size: 0.9rem;">&larr; Back to all requests</a>
</p>

<div class="card">
    <div class="detail-header">
        <div class="detail-vote">
            @auth
                <form method="POST" action="/feedback/{{ $feedbackItem->id }}/vote">
                    @csrf
                    <button type="submit" class="vote-btn-lg {{ $voted ? 'voted' : '' }}">
                        <span class="arrow">&#9650;</span>
                        <span class="count">{{ $feedbackItem->votes_count }}</span>
                    </button>
                </form>
            @else
                <a href="/login" class="vote-btn-lg">
                    <span class="arrow">&#9650;</span>
                    <span class="count">{{ $feedbackItem->votes_count }}</span>
                </a>
            @endauth
        </div>
        <div style="flex: 1;">
            <h1 style="font-family: 'Cinzel', serif; font-size: 1.4rem; margin-bottom: 8px;">{{ $feedbackItem->title }}</h1>
            <div style="display: flex; align-items: center; gap: 10px; font-size: 0.85rem; color: var(--muted);">
                <span class="status-badge status-{{ $feedbackItem->status }}">{{ str_replace('_', ' ', $feedbackItem->status) }}</span>
                <span>by {{ $feedbackItem->user->name }}</span>
                <span>{{ $feedbackItem->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <div style="font-size: 0.95rem; line-height: 1.7; color: var(--text); white-space: pre-wrap;">{{ $feedbackItem->description }}</div>

    @if ($feedbackItem->admin_response)
        <div class="admin-response-box">
            <h3>Admin Response</h3>
            <div style="font-size: 0.9rem; line-height: 1.6; white-space: pre-wrap;">{{ $feedbackItem->admin_response }}</div>
        </div>
    @endif
</div>

@auth
    @if (Auth::user()->isAdmin())
        <div class="admin-controls">
            <h3>Admin Controls</h3>

            <form method="POST" action="/admin/feedback/{{ $feedbackItem->id }}/status">
                @csrf
                @method('PATCH')
                <div class="control-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Status</label>
                        <select name="status">
                            <option value="under_review" {{ $feedbackItem->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="planned" {{ $feedbackItem->status === 'planned' ? 'selected' : '' }}>Planned</option>
                            <option value="in_progress" {{ $feedbackItem->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="done" {{ $feedbackItem->status === 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                </div>
            </form>

            <form method="POST" action="/admin/feedback/{{ $feedbackItem->id }}/respond">
                @csrf
                @method('PATCH')
                <div class="form-group">
                    <label>Admin Response</label>
                    <textarea name="admin_response" placeholder="Write a response..." maxlength="2000">{{ $feedbackItem->admin_response }}</textarea>
                    @error('admin_response')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Post Response</button>
            </form>

            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.08);">
                <form method="POST" action="/admin/feedback/{{ $feedbackItem->id }}" onsubmit="return confirm('Permanently delete this item?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete Item</button>
                </form>
            </div>
        </div>
    @endif
@endauth
@endsection
