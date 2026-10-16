<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNoteAddedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $note,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Νέα Ενημέρωση στο Δελτίο Βλάβης')
            ->greeting('Γεια σας '.$notifiable->name.',')
            ->line('Υπάρχει νέα ενημέρωση στο δελτίο βλάβης σας.')
            ->line('Αριθμός Δελτίου: '.($this->ticket->reference_number ?? '—'))
            ->line('Θέμα: '.$this->ticket->title)
            ->line('Ενημέρωση: '.$this->note)
            ->action('Προβολή Δελτίου', route('customer.tickets.show', $this->ticket))
            ->salutation('Με εκτίμηση, Ticket Supporting');
    }
}