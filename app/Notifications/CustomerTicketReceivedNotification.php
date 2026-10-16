<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerTicketReceivedNotification extends Notification
{
    use Queueable;

    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Επιβεβαίωση Παραλαβής Δελτίου Βλάβης - ' . ($this->ticket->reference_number ?? ''))
            ->greeting('Γεια σας ' . $notifiable->name . ',')
            ->line('Έχουμε παραλάβει το δελτίο βλάβης σας.')
            ->line('Η ομάδα μας θα το εξετάσει το συντομότερο δυνατό.')
            ->line('Αριθμός Δελτίου: ' . ($this->ticket->reference_number ?? '—'))
            ->line('Θέμα: ' . ($this->ticket->title ?? '—'))
            ->action('Προβολή Δελτίου', route('customer.tickets.show', $this->ticket))
            ->line('Ευχαριστούμε.');
    }
}