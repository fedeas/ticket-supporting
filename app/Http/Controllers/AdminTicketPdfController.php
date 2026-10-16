<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminTicketPdfController extends Controller
{
    public function export(Ticket $ticket)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $pdf = Pdf::loadView('pdf.admin-ticket', [
            'ticket' => $ticket,
        ])->setPaper('a4', 'portrait');

        $safeNumber = str_replace(['/', '\\'], '-', $ticket->registration_number ?: (string) $ticket->id);
        $filename = 'ticket-' . $safeNumber . '.pdf';

        return $pdf->download($filename);
    }
}