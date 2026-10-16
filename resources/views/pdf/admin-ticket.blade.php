<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>Δελτίο Βλάβης PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111827;
            line-height: 1.5;
            margin: 24px;
        }

        .logo-wrap {
            text-align: center;
            margin-bottom: 12px;
        }

        .logo {
            max-width: 220px;
            max-height: 90px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 18px;
            border: 1px solid #d1d5db;
            padding: 12px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        table.meta {
            width: 100%;
            border-collapse: collapse;
        }

        table.meta td {
            padding: 6px 4px;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }

        td.label {
            width: 50%;
            font-weight: bold;
        }

        .text-block {
            white-space: pre-line;
            min-height: 20px;
        }

        ul {
            margin: 6px 0 0 18px;
            padding: 0;
        }

        .signatures {
            margin-top: 70px;
            width: 100%;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #111827;
            padding-top: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.png');
        $logoBase64 = null;

        if (file_exists($logoPath)) {
            $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoData = file_get_contents($logoPath);
            if ($logoData !== false) {
                $logoBase64 = 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
            }
        }
    @endphp

    @if($logoBase64)
        <div class="logo-wrap">
            <img src="{{ $logoBase64 }}" alt="Logo" class="logo">
        </div>
    @endif

    <h1>ΔΕΛΤΙΟ ΒΛΑΒΗΣ</h1>

    <div class="section">
        <div class="section-title">Στοιχεια ΔΕΛΤΙΟΥ ΒΛΑΒΗΣ</div>

        <table class="meta">
            <tr>
                <td class="label">Αριθμός Δελτίου Βλάβης</td>
                <td>{{ $ticket->reference_number ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Αριθμός Συστήματος</td>
                <td>{{ $ticket->registration_number ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Τμήμα / Διεύθυνση / Υπηρεσία / Μονάδα</td>
                <td>{{ $ticket->department ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Σταθμός / Κλιμάκιο</td>
                <td>{{ $ticket->station_unit ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Πρόσωπο Επικοινωνίας</td>
                <td>{{ $ticket->contact_person ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Τηλέφωνο Επικοινωνίας</td>
                <td>{{ $ticket->contact_phone ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Χώρος Εγκατάστασης</td>
                <td>{{ $ticket->installation_location ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Αριθμός Εγγραφής Οχήματος</td>
                <td>{{ $ticket->vehicle_registration ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Θέμα / Τίτλος Βλάβης</td>
                <td>{{ $ticket->title ?? '—' }}</td>
            </tr>
            <tr>
                <td class="label">Καταχωρήθηκε από</td>
                <td>{{ $ticket->recorded_by ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Στοιχεια Εξοπλισμού</div>

        @if(is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
            <ul>
                @foreach($ticket->equipment_types as $equipment)
                    <li>{{ $equipment }}</li>
                @endforeach
            </ul>
        @else
            <div>—</div>
        @endif
    </div>

    @if($ticket->has_request && !empty($ticket->request_text))
        <div class="section">
            <div class="section-title">Request</div>
            <div class="text-block">
                {{ $ticket->request_text }}
            </div>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Περιγραφη Βλάβης</div>
        <div class="text-block">
            {{ $ticket->fault_description ?: $ticket->description ?: '—' }}
        </div>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="signature-line">Υπογραφή Αναθέτουσας Αρχής</div>
            </td>
            <td>
                <div class="signature-line">Υπογραφή Αναδόχου</div>
            </td>
        </tr>
    </table>
</body>
</html>