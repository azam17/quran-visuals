<?php

namespace App\Http\Controllers;

use App\Models\FeedbackItem;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function updateStatus(Request $request, FeedbackItem $feedbackItem)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:under_review,planned,in_progress,done'],
        ]);

        $feedbackItem->update($validated);

        return back()->with('success', 'Status updated.');
    }

    public function respond(Request $request, FeedbackItem $feedbackItem)
    {
        $validated = $request->validate([
            'admin_response' => ['required', 'string', 'max:2000'],
        ]);

        $feedbackItem->update($validated);

        return back()->with('success', 'Response posted.');
    }

    public function destroy(FeedbackItem $feedbackItem)
    {
        $feedbackItem->delete();

        return redirect('/feedback')->with('success', 'Item deleted.');
    }
}
