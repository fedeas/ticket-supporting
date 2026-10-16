@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Ticket Supporting" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square items-center justify-center rounded-md overflow-hidden">
            <img src="{{ asset('images/logo_short.png') }}" width="100%" height="100%" alt="Ticket Supporting" class=" object-cover rounded-md">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Ticket Supporting" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square items-center justify-center rounded-md overflow-hidden">
            <img src="{{ asset('images/logo_short.png') }}" width="100%" height="100%" alt="Ticket Supporting" class="object-cover rounded-md">
        </x-slot>
    </flux:brand>
@endif
