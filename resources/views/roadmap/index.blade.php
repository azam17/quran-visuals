@extends('layouts.feedback')

@section('title', 'Roadmap')

@section('extra-css')
<style>
    .roadmap-header {
        margin-bottom: 28px;
    }

    .roadmap-header h1 {
        font-family: "Cinzel", serif;
        font-size: 1.6rem;
        letter-spacing: 0.04em;
    }

    .roadmap-header p {
        color: var(--muted);
        font-size: 0.9rem;
        margin-top: 6px;
    }

    .roadmap-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
    }

    @media (max-width: 900px) {
        .roadmap-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 560px) {
        .roadmap-grid {
            grid-template-columns: 1fr;
        }
    }

    .roadmap-column h2 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 2px solid;
    }

    .col-under_review h2 { border-color: var(--muted); color: var(--muted); }
    .col-planned h2 { border-color: #8facff; color: #8facff; }
    .col-in_progress h2 { border-color: var(--accent); color: var(--accent); }
    .col-done h2 { border-color: #6fe0a0; color: #6fe0a0; }

    .roadmap-cards {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .roadmap-card {
        padding: 14px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(8, 9, 12, 0.5);
        transition: border-color 0.2s;
    }

    .roadmap-card:hover {
        border-color: rgba(255,255,255,0.16);
    }

    .roadmap-card h3 {
        font-size: 0.88rem;
        margin-bottom: 6px;
    }

    .roadmap-card h3 a {
        color: var(--text);
    }

    .roadmap-card h3 a:hover {
        color: var(--accent);
    }

    .roadmap-card-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.78rem;
        color: var(--muted);
    }

    .roadmap-votes {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        color: var(--accent);
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="roadmap-header">
    <h1>Roadmap</h1>
    <p>Track the progress of feature requests from the community.</p>
</div>

<div class="roadmap-grid">
    @foreach ($statuses as $key => $label)
        <div class="roadmap-column col-{{ $key }}">
            <h2>{{ $label }} ({{ isset($items[$key]) ? $items[$key]->count() : 0 }})</h2>
            <div class="roadmap-cards">
                @forelse ($items[$key] ?? collect() as $item)
                    <div class="roadmap-card">
                        <h3><a href="/feedback/{{ $item->id }}">{{ $item->title }}</a></h3>
                        <div class="roadmap-card-meta">
                            <span class="roadmap-votes">&#9650; {{ $item->votes_count }}</span>
                            <span>{{ $item->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 0.8rem; color: var(--muted); padding: 12px; text-align: center;">
                        No items yet
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@endsection
