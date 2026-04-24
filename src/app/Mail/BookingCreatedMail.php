<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Booking $booking,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking confirmed: '.$this->booking->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.booking_created',
            with: [
                'title' => $this->booking->title,
                'roomName' => $this->booking->room?->name,
                'startsAt' => $this->booking->starts_at?->toDayDateTimeString(),
                'endsAt' => $this->booking->ends_at?->toDayDateTimeString(),
            ],
        );
    }
}
