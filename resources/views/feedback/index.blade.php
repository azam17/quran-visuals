@extends('layouts.feedback')

@section('title', 'Feature Requests')

@section('extra-css')
<style>
    .feedback-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .feedback-header h1 {
        font-family: "Cinzel", serif;
        font-size: 1.6rem;
        letter-spacing: 0.04em;
    }

    .sort-tabs {
        display: flex;
        gap: 4px;
        background: rgba(8, 9, 12, 0.5);
        border-radius: 10px;
        padding: 4px;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .sort-tabs a {
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        color: var(--muted);
        transition: all 0.2s;
    }

    .sort-tabs a:hover { color: var(--text); text-decoration: none; }

    .sort-tabs a.active {
        background: rgba(194,139,59,0.2);
        color: var(--text);
    }

    .search-form {
        margin-bottom: 20px;
    }

    .search-form input {
        width: 100%;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(8, 9, 12, 0.7);
        color: var(--text);
        font-size: 0.9rem;
        font-family: inherit;
    }

    .feedback-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .feedback-card {
        display: grid;
        grid-template-columns: 60px 1fr;
        gap: 16px;
        padding: 18px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(8, 9, 12, 0.5);
        transition: border-color 0.2s;
    }

    .feedback-card:hover {
        border-color: rgba(255,255,255,0.16);
    }

    .vote-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .vote-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 8px 12px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(8, 9, 12, 0.7);
        color: var(--muted);
        cursor: pointer;
        font-family: inherit;
        font-size: 0.8rem;
        transition: all 0.2s;
    }

    .vote-btn:hover { border-color: var(--accent); color: var(--accent); }

    .vote-btn.voted {
        border-color: var(--accent);
        background: rgba(194,139,59,0.15);
        color: var(--accent);
    }

    .vote-btn .arrow { font-size: 1rem; line-height: 1; }
    .vote-btn .count { font-weight: 600; font-size: 0.95rem; }

    .feedback-content h3 {
        font-size: 1rem;
        margin-bottom: 4px;
    }

    .feedback-content h3 a {
        color: var(--text);
    }

    .feedback-content h3 a:hover {
        color: var(--accent);
    }

    .feedback-meta {
        font-size: 0.8rem;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
    }

    .status-under_review { background: rgba(178,176,189,0.15); color: var(--muted); }
    .status-planned { background: rgba(100,140,255,0.15); color: #8facff; }
    .status-in_progress { background: rgba(194,139,59,0.2); color: var(--accent); }
    .status-done { background: rgba(74,224,138,0.15); color: #6fe0a0; }

    .admin-inline {
        display: flex;
        gap: 6px;
        align-items: center;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .admin-inline select,
    .admin-inline button {
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(8, 9, 12, 0.7);
        color: var(--text);
        font-size: 0.78rem;
        font-family: inherit;
        cursor: pointer;
    }

    .pagination-wrapper {
        margin-top: 24px;
        display: flex;
        justify-content: center;
        gap: 4px;
    }

    .pagination-wrapper nav span,
    .pagination-wrapper nav a {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.08);
        font-size: 0.85rem;
        color: var(--muted);
    }

    .pagination-wrapper nav span[aria-current] {
        background: rgba(194,139,59,0.2);
        color: var(--text);
        border-color: var(--accent);
    }

    .pagination-wrapper nav a:hover {
        border-color: rgba(255,255,255,0.2);
        text-decoration: none;
    }
</style>
@endsection

@section('content')
<div class="feedback-header">
    <h1>Feature Requests</h1>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div class="sort-tabs">
            <a href="/feedback?sort=new{{ $search ? '&search='.$search : '' }}" class="{{ $sort === 'new' ? 'active' : '' }}">New</a>
            <a href="/feedback?sort=top{{ $search ? '&search='.$search : '' }}" class="{{ $sort === 'top' ? 'active' : '' }}">Top</a>
            <a href="/feedback?sort=trending{{ $search ? '&search='.$search : '' }}" class="{{ $sort === 'trending' ? 'active' : '' }}">Trending</a>
        </div>
        @auth
            <a href="/feedback/create" class="btn btn-primary">+ New Request</a>
        @else
            <a href="/login" class="btn btn-primary">Login to Submit</a>
        @endauth
    </div>
</div>

<form class="search-form" method="GET" action="/feedback">
    <input type="hidden" name="sort" value="{{ $sort }}">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search requests...">
</form>

<div class="feedback-list">
    @forelse ($items as $item)
        <div class="feedback-card">
            <div class="vote-box">
                @auth
                    <form method="POST" action="/feedback/{{ $item->id }}/vote">
                        @csrf
                        <button type="submit" class="vote-btn {{ in_array($item->id, $votedIds) ? 'voted' : '' }}">
                            <span class="arrow">&#9650;</span>
                            <span class="count">{{ $item->votes_count }}</span>
                        </button>
                    </form>
                @else
                    <a href="/login" class="vote-btn">
                        <span class="arrow">&#9650;</span>
                        <span class="count">{{ $item->votes_count }}</span>
                    </a>
                @endauth
            </div>
            <div class="feedback-content">
                <h3><a href="/feedback/{{ $item->id }}">{{ $item->title }}</a></h3>
                <div class="feedback-meta">
                    <span class="status-badge status-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span>
                    <span>by {{ $item->user->name }}</span>
                    <span>{{ $item->created_at->diffForHumans() }}</span>
                </div>

                @auth
                    @if (Auth::user()->isAdmin())
                        <div class="admin-inline">
                            <form method="POST" action="/admin/feedback/{{ $item->id }}/status" style="display:flex;gap:4px;align-items:center;">
                                @csrf
                                @method('PATCH')
                                <select name="status">
                                    <option value="under_review" {{ $item->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                                    <option value="planned" {{ $item->status === 'planned' ? 'selected' : '' }}>Planned</option>
                                    <option value="in_progress" {{ $item->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="done" {{ $item->status === 'done' ? 'selected' : '' }}>Done</option>
                                </select>
                                <button type="submit" class="btn btn-sm">Update</button>
                            </form>
                            <form method="POST" action="/admin/feedback/{{ $item->id }}" onsubmit="return confirm('Delete this item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    @empty
        <div class="card" style="text-align: center; padding: 40px; color: var(--muted);">
            @if ($search)
                No results for "{{ $search }}".
            @else
                No feature requests yet. Be the first to submit one!
            @endif
        </div>
    @endforelse
</div>

@if ($items->hasPages())
    <div class="pagination-wrapper">
        {{ $items->links('pagination::simple-default') }}
    </div>
@endif
@endsection
