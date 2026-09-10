<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $otp,
        public readonly string $name,
    ) {}

    public function envelope(): Envelope
    {
        // Jangan taruh OTP di subject — subject terekspos di email header,
        // log server, dan notifikasi push yang bisa dibaca tanpa buka email.
        return new Envelope(
            subject: 'Kode Verifikasi Pengisian Kuesioner KPI 360',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.otp',
        );
    }
}
