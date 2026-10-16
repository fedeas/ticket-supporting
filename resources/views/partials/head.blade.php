<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Ticket Supporting') : config('app.name', 'Ticket Supporting') }}
</title>

<link rel="icon" href="{{ asset('images/logo_short.png') }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('images/logo_short.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
