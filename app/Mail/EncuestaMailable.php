<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EncuestaMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $urlEncuesta; // Propiedad para almacenar la URL de la encuesta

    /**
     * Create a new message instance.
     */
    public function __construct($urlEncuesta)
    {
        $this->urlEncuesta = $urlEncuesta; // Asignar la URL de la encuesta
    }

    public function build()
    {
        return $this->view('emails.mailable')
                    ->with(['urlEncuesta' => $this->urlEncuesta]); // Pasar la URL a la vista
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Encuesta Mailable',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mailable',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
