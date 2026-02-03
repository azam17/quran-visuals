<?php

namespace App\Mail;

use App\Models\FeedbackItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFeedbackSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FeedbackItem $feedbackItem)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Feature Request: ' . $this->feedbackItem->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-feedback',
        );
    }
}
