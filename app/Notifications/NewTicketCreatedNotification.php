<?php

namespace App\Notifications;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->ticket->loadMissing('customer');

        $safeNumber = str_replace(['/', '\\'], '-', $this->ticket->registration_number ?: (string) $this->ticket->id);

        $pdf = Pdf::loadView('pdf.admin-ticket', [
            'ticket' => $this->ticket,
        ])->setPaper('a4', 'portrait');

        return (new MailMessage)
            ->subject('Άνοιξε νέο Δελτίο Βλάβης')
            ->greeting('Γεια σας '.$notifiable->name.',')
            ->line('Έχει ανοιχτεί νέο δελτίο βλάβης στο σύστημα.')
            ->line('Αριθμός Δελτίου: '.($this->ticket->reference_number ?? '—'))
            ->line('Αριθμός Συστήματος: '.($this->ticket->registration_number ?? '—'))
            ->line('Θέμα: '.$this->ticket->title)
            ->line('Πρόσωπο Επικοινωνίας: '.($this->ticket->contact_person ?? '—'))
            ->line('Κατάσταση: '.$this->statusLabel($this->ticket->status))
            ->action('Προβολή Δελτίου', route('admin.tickets.show', $this->ticket))
            ->line('Επισυνάπτεται και το PDF του δελτίου.')
            ->attachData(
                $pdf->output(),
                'ticket-'.$safeNumber.'.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            )
            ->salutation('Με εκτίμηση, Ticket Supporting');
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
