<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address as MailablesAddress;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PruebaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $sub_total;
    public $mes_actual;
    /**
     * Create a new message instance.
     */
    public function __construct($usuario, $mes)
    {
        $this->usuario = $usuario;
        $this->mes_actual = $mes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            //configuramos quien envia el correo, sino lo configuramos toma el que esta por default en el .env
            from: new MailablesAddress('paizkenia5@gmail.com','Kenia Paiz'),
            subject: 'Factura Mensual',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            //creamos una vista para lo que va contener el correo
            view: 'email.prueba',
            with: [
                'usuario' => $this->usuario,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath(public_path('storage').$this->usuario.'-'.$this->mes_actual.'.pdf')->as($this->usuario.'-'.$this->mes_actual.'.pdf')->withMime('application/pdf')
        ];
    }
}
