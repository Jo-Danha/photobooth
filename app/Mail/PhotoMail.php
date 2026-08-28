<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PhotoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $orderId;
    public $appName;

    protected $photoPath;

    public function __construct($photoPath, $orderId, $appName)
    {
        $this->photoPath = $photoPath;
        $this->orderId = $orderId;
        $this->appName = $appName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Foto Photobooth Anda - ' . $this->orderId,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.photo',
            with: [
                'orderId' => $this->orderId,
                'appName' => $this->appName,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->photoPath)
                ->as('photobooth_' . $this->orderId . '.png')
                ->withMime('image/png'),
        ];
    }
}
