@props(['status'])

@php
    $map = [
        'backlog' => 'bg-zinc-100 text-zinc-800 ring-zinc-200 dark:bg-zinc-800 dark:text-zinc-100 dark:ring-zinc-700',
        'in_progress' => 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800',
        'done' => 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800',
    ];

    $label = match($status) {
        'in_progress' => 'Σε Εξέλιξη',
        'done' => 'Ολοκληρωμένο',
        default => 'Σε Εκκρεμότητα',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ' . $map[$status]]) }}>
    {{ $label }}
</span>