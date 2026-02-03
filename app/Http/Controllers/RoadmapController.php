<?php

namespace App\Http\Controllers;

use App\Models\FeedbackItem;

class RoadmapController extends Controller
{
    public function index()
    {
        $items = FeedbackItem::withCount('votes')
            ->orderByDesc('votes_count')
            ->get()
            ->groupBy('status');

        $statuses = [
            FeedbackItem::STATUS_UNDER_REVIEW => 'Under Review',
            FeedbackItem::STATUS_PLANNED => 'Planned',
            FeedbackItem::STATUS_IN_PROGRESS => 'In Progress',
            FeedbackItem::STATUS_DONE => 'Done',
        ];

        return view('roadmap.index', compact('items', 'statuses'));
    }
}
