<?php

namespace App\Http\Controllers;

use App\Mail\NewFeedbackSubmitted;
use App\Models\FeedbackItem;
use App\Models\FeedbackVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'new');
        $search = $request->get('search');

        $query = FeedbackItem::withCount('votes')->with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'top':
                $query->orderByDesc('votes_count')->orderByDesc('created_at');
                break;
            case 'trending':
                $query->where('created_at', '>=', now()->subDays(30))
                      ->orderByDesc('votes_count')
                      ->orderByDesc('created_at');
                break;
            default: // new
                $query->orderByDesc('created_at');
                break;
        }

        $items = $query->paginate(20)->withQueryString();

        $votedIds = [];
        if ($user = $request->user()) {
            $votedIds = FeedbackVote::where('user_id', $user->id)
                ->whereIn('feedback_item_id', $items->pluck('id'))
                ->pluck('feedback_item_id')
                ->all();
        }

        return view('feedback.index', compact('items', 'sort', 'search', 'votedIds'));
    }

    public function create()
    {
        return view('feedback.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $item = $request->user()->feedbackItems()->create($validated);

        try {
            $adminEmail = config('quran.admin_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new NewFeedbackSubmitted($item));
            }
        } catch (\Throwable $e) {
            // Don't block the user if mail fails
        }

        return redirect('/feedback')->with('success', 'Your request has been submitted!');
    }

    public function show(FeedbackItem $feedbackItem)
    {
        $feedbackItem->loadCount('votes')->load('user');

        $voted = false;
        if ($user = request()->user()) {
            $voted = $feedbackItem->hasVoteFrom($user);
        }

        return view('feedback.show', compact('feedbackItem', 'voted'));
    }

    public function vote(Request $request, FeedbackItem $feedbackItem)
    {
        $user = $request->user();

        $existing = FeedbackVote::where('user_id', $user->id)
            ->where('feedback_item_id', $feedbackItem->id)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            FeedbackVote::create([
                'user_id' => $user->id,
                'feedback_item_id' => $feedbackItem->id,
            ]);
        }

        return back();
    }
}
