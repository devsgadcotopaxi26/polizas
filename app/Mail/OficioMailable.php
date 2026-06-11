<?php

namespace App\Mail;

use App\Models\Poliza;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class OficioMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Poliza $poliza,
        public string $asunto,
        public string $cuerpo,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->asunto,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.oficio',
            with: [
                'cuerpo' => $this->cuerpo,
                'poliza' => $this->poliza,
            ]
        );
    }

    public function attachments(): array
    {
        if (!$this->poliza->oficio_path) {
            return [];
        }

        $path = Storage::disk('local')->path($this->poliza->oficio_path);

        if (!file_exists($path)) {
            return [];
        }

        return [
            Attachment::fromPath($path)
                ->as('Oficio_' . $this->poliza->numero_poliza . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
