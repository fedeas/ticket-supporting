<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
        public ?string $note = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->newStatus === 'done' ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Ολοκλήρωση Δελτίου Βλάβης')
            ->greeting('Γεια σας '.$notifiable->name.',')
            ->line('Το δελτίο βλάβης σας έχει ολοκληρωθεί.')
            ->line('Αριθμός Δελτίου: '.($this->ticket->reference_number ?? '—'))
            ->line('Θέμα: '.$this->ticket->title)
            ->line('Κατάσταση: '.$this->statusLabel($this->newStatus))
            ->action('Προβολή Δελτίου', route('customer.tickets.show', $this->ticket));

        if ($this->note) {
            $mail->line('Σχόλιο διαχειριστή: '.$this->note);
        }

        return $mail->salutation('Με εκτίμηση, Ticket Supporting');
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'in_progress' => 'Σε Εξέλιξη',
            'done' => 'Ολοκληρωμένο',
            default => 'Σε Εκκρεμότητα',
        };
    }
}