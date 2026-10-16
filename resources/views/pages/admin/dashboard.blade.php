<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $openSort = 'created_desc';
    public string $closedSort = 'closed_desc';
    public string $selectedStatusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage(pageName: 'openPage');
        $this->resetPage(pageName: 'closedPage');
    }

    public function updatingOpenSort(): void
    {
        $this->resetPage(pageName: 'openPage');
    }

    public function updatingClosedSort(): void
    {
        $this->resetPage(pageName: 'closedPage');
    }

    public function toggleStatusFilter(string $status): void
    {
        $this->selectedStatusFilter = $this->selectedStatusFilter === $status ? '' : $status;
        $this->resetPage(pageName: 'openPage');
        $this->resetPage(pageName: 'closedPage');
    }

    public function clearStatusFilter(): void
    {
        $this->selectedStatusFilter = '';
        $this->resetPage(pageName: 'openPage');
        $this->resetPage(pageName: 'closedPage');
    }

    protected function applySearch($query)
    {
        return $query->when($this->search !== '', function ($q) {
            $q->where(function ($sub) {
                $sub->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('registration_number', 'like', '%'.$this->search.'%')
                    ->orWhere('vehicle_registration', 'like', '%'.$this->search.'%')
                    ->orWhereHas('customer', function ($customerQuery) {
                        $customerQuery->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        });
    }

    protected function sortOpen($query)
    {
        return match ($this->openSort) {
            'created_asc' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    protected function sortClosed($query)
    {
        return match ($this->closedSort) {
            'closed_asc' => $query->orderBy('closed_at', 'asc'),
            'created_asc' => $query->orderBy('created_at', 'asc'),
            'created_desc' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('closed_at', 'desc'),
        };
    }

    public function with(): array
    {
        $openBase = Ticket::with([
                'customer',
                'updates' => fn ($q) => $q->latest('action_at'),
                'updates.admin',
            ])
            ->whereIn('status', ['backlog', 'in_progress']);

        $closedBase = Ticket::with([
                'customer',
                'updates' => fn ($q) => $q->latest('action_at'),
                'updates.admin',
            ])
            ->where('status', 'done');

        if ($this->selectedStatusFilter === 'backlog') {
            $openBase->where('status', 'backlog');
            $closedBase->whereRaw('1 = 0');
        } elseif ($this->selectedStatusFilter === 'in_progress') {
            $openBase->where('status', 'in_progress');
            $closedBase->whereRaw('1 = 0');
        } elseif ($this->selectedStatusFilter === 'done') {
            $openBase->whereRaw('1 = 0');
        }

        $openBase = $this->applySearch($openBase);
        $closedBase = $this->applySearch($closedBase);

        return [
            'totalTickets' => Ticket::count(),
            'backlogCount' => Ticket::where('status', 'backlog')->count(),
            'inProgressCount' => Ticket::where('status', 'in_progress')->count(),
            'doneCount' => Ticket::where('status', 'done')->count(),
            'openTickets' => $this->sortOpen($openBase)->paginate(10, ['*'], 'openPage'),
            'closedTickets' => $this->sortClosed($closedBase)->paginate(10, ['*'], 'closedPage'),
        ];
    }
};

?>

<div class="space-y-6">
    @if (session()->has('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 3500)"
            class="fixed right-4 top-4 z-[9999] w-full max-w-md rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-xl dark:border-emerald-900 dark:bg-emerald-900/90 dark:text-emerald-300"
        >
            <div class="flex items-start justify-between gap-3">
                <span>{{ session('success') }}</span>

                <button
                    type="button"
                    @click="show = false"
                    class="text-emerald-700 transition duration-150 ease-out transform hover:scale-125 hover:opacity-80 active:scale-90 dark:text-emerald-300"
                >
                    ✕
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4500)"
            class="fixed right-4 top-4 z-[9999] w-full max-w-md rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-xl dark:border-red-900 dark:bg-red-900/90 dark:text-red-300"
        >
            <div class="flex items-start justify-between gap-3">
                <span>{{ session('error') }}</span>

                <button
                    type="button"
                    @click="show = false"
                    class="text-red-700 transition duration-150 ease-out transform hover:scale-125 hover:opacity-80 active:scale-90 dark:text-red-300"
                >
                    ✕
                </button>
            </div>
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold">Πίνακας Διαχείρισης</h1>
        <p class="text-sm text-zinc-500">Παρακολούθηση ενεργών και ολοκληρωμένων δελτίων.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <button
            type="button"
            wire:click="clearStatusFilter"
            class="rounded-2xl border p-5 text-left shadow-sm transition duration-150 ease-out transform hover:scale-[1.02] hover:shadow-md active:scale-[0.97] active:shadow-sm
                {{ $selectedStatusFilter === '' ? 'border-zinc-400 bg-zinc-100 ring-2 ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-800' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Σύνολο Δελτίων</p>
            <p class="mt-2 text-3xl font-semibold">{{ $totalTickets }}</p>
        </button>

        <button
            type="button"
            wire:click="toggleStatusFilter('backlog')"
            class="rounded-2xl border p-5 text-left shadow-sm transition duration-150 ease-out transform hover:scale-[1.02] hover:shadow-md active:scale-[0.97] active:shadow-sm
                {{ $selectedStatusFilter === 'backlog' ? 'border-zinc-400 bg-zinc-100 ring-2 ring-zinc-300 dark:border-zinc-600 dark:bg-zinc-800' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Σε Εκκρεμότητα</p>
            <p class="mt-2 text-3xl font-semibold">{{ $backlogCount }}</p>
        </button>

        <button
            type="button"
            wire:click="toggleStatusFilter('in_progress')"
            class="rounded-2xl border p-5 text-left shadow-sm transition duration-150 ease-out transform hover:scale-[1.02] hover:shadow-md active:scale-[0.97] active:shadow-sm
                {{ $selectedStatusFilter === 'in_progress' ? 'border-amber-300 bg-amber-50 ring-2 ring-amber-200 dark:border-amber-700 dark:bg-amber-900/20' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Σε Εξέλιξη</p>
            <p class="mt-2 text-3xl font-semibold">{{ $inProgressCount }}</p>
        </button>

        <button
            type="button"
            wire:click="toggleStatusFilter('done')"
            class="rounded-2xl border p-5 text-left shadow-sm transition duration-150 ease-out transform hover:scale-[1.02] hover:shadow-md active:scale-[0.97] active:shadow-sm
                {{ $selectedStatusFilter === 'done' ? 'border-emerald-300 bg-emerald-50 ring-2 ring-emerald-200 dark:border-emerald-700 dark:bg-emerald-900/20' : 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' }}"
        >
            <p class="text-sm text-zinc-500">Ολοκληρωμένα</p>
            <p class="mt-2 text-3xl font-semibold">{{ $doneCount }}</p>
        </button>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="Αναζήτηση με θέμα, αριθμό δελτίου, αριθμό συστήματος, αριθμό οχήματος, όνομα ή email..."
            class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
        >
    </div>

    <section class="space-y-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-xl font-semibold">Ανοιχτά Δελτία</h2>
                <p class="text-sm text-zinc-500">Δελτία σε εκκρεμότητα και σε εξέλιξη.</p>
            </div>

            <div class="w-full md:w-64">
                <select
                    wire:model.live="openSort"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
                >
                    <option value="created_desc">Νεότερα πρώτα</option>
                    <option value="created_asc">Παλαιότερα πρώτα</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Θεμα</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Πελατης</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμος Δελτιου</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμος Συστηματος</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμος Οχηματος</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Κατασταση</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Τελευταια Ενεργεια Admin</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">Ενεργειες</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($openTickets as $ticket)
                        @php
                            $lastUpdate = $ticket->updates->sortByDesc('action_at')->first();
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="font-medium">{{ $ticket->title }}</p>
                                    </div>
                                    @if(
                                        (!empty($ticket->equipment_types) && is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
                                        || ($ticket->has_request && !empty($ticket->request_text))
                                    )
                                        <div class="space-y-1 text-zinc-500" style="font-size: 14px; line-height: 1.2;">
                                            @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
                                                <div class="space-y-1">
                                                    <div>
                                                        <span class="font-medium text-zinc-700 dark:text-zinc-300" style="font-size: 14px;">ΣΤΟΙΧΕΙΑ ΕΞΟΠΛΙΣΜΟΥ:</span>
                                                    </div>

                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($ticket->equipment_types as $equipment)
                                                            <span
                                                                class="inline-flex items-center rounded-full bg-zinc-100 font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                                                style="font-size: 12px; padding: 2px 8px; line-height: 1.1;"
                                                            >
                                                                {{ $equipment }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($ticket->has_request && !empty($ticket->request_text))
                                                <div>
                                                    <span class="font-medium text-zinc-700 dark:text-zinc-300" style="font-size: 10px;">REQUEST:</span>
                                                    <span style="font-size: 14px;">
                                                        {{ \Illuminate\Support\Str::limit($ticket->request_text, 80) }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-4 text-sm">
                                {{ $ticket->customer->name }}<br>
                                <span class="text-zinc-500">{{ $ticket->customer->email }}</span>
                            </td>

                            <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->reference_number ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->registration_number ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->vehicle_registration ?? '—' }}</td>

                            <td class="px-4 py-4">
                                <x-status-badge :status="$ticket->status" />
                            </td>

                            <td class="px-4 py-4 text-sm text-zinc-500">
                                @if($lastUpdate)
                                    <div class="space-y-1">
                                        <div class="font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ $lastUpdate->admin->name ?? 'Διαχειριστής' }}
                                        </div>
                                        <div>{{ $lastUpdate->action_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                        <div class="text-xs">
                                            {{ \Illuminate\Support\Str::limit($lastUpdate->note ?? '—', 90) }}
                                        </div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.tickets.show', $ticket) }}"
                                        wire:navigate
                                        class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition duration-150 ease-out transform hover:scale-[1.03] hover:bg-zinc-100 hover:shadow-md active:scale-[0.95] active:shadow-sm dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                    >
                                        Διαχείριση
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Δεν βρέθηκαν ανοιχτά δελτία.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $openTickets->links() }}
        </div>
    </section>

    <section class="space-y-3">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-xl font-semibold">Ολοκληρωμένα Δελτία</h2>
                <p class="text-sm text-zinc-500">Δελτία που έχουν κλείσει.</p>
            </div>

            <div class="w-full md:w-64">
                <select
                    wire:model.live="closedSort"
                    class="w-full rounded-xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950"
                >
                    <option value="closed_desc">Πρόσφατα κλεισμένα πρώτα</option>
                    <option value="closed_asc">Παλαιότερα κλεισμένα πρώτα</option>
                    <option value="created_desc">Νεότερα δημιουργημένα πρώτα</option>
                    <option value="created_asc">Παλαιότερα δημιουργημένα πρώτα</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Θεμα</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Πελατης</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμος Δελτιου</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμος Συστηματος</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμος Οχηματος</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Τελευταια Ενεργεια Admin</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">Ενεργειες</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($closedTickets as $ticket)
                        @php
                            $lastUpdate = $ticket->updates->sortByDesc('action_at')->first();
                        @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                            <td class="px-4 py-4">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="font-medium">{{ $ticket->title }}</p>
                                    </div>

                                    @if(
                                        (!empty($ticket->equipment_types) && is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
                                        || ($ticket->has_request && !empty($ticket->request_text))
                                    )
                                        <div class="space-y-1 text-xs text-zinc-500">
                                            @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
                                                <div>
                                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">ΣΤΟΙΧΕΙΑ ΕΞΟΠΛΙΣΜΟΥ:</span>
                                                    {{ implode(' / ', $ticket->equipment_types) }}
                                                </div>
                                            @endif

                                            @if($ticket->has_request && !empty($ticket->request_text))
                                                <div>
                                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">REQUEST:</span>
                                                    {{ \Illuminate\Support\Str::limit($ticket->request_text, 80) }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="px-4 py-4 text-sm">
                                {{ $ticket->customer->name }}<br>
                                <span class="text-zinc-500">{{ $ticket->customer->email }}</span>
                            </td>

                            <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->reference_number ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->registration_number ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-500">{{ $ticket->vehicle_registration ?? '—' }}</td>

                            <td class="px-4 py-4 text-sm text-zinc-500">
                                @if($lastUpdate)
                                    <div class="space-y-1">
                                        <div class="font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ $lastUpdate->admin->name ?? 'Διαχειριστής' }}
                                        </div>
                                        <div>{{ $lastUpdate->action_at?->format('d/m/Y H:i') ?? '—' }}</div>
                                        <div class="text-xs">
                                            {{ \Illuminate\Support\Str::limit($lastUpdate->note ?? '—', 90) }}
                                        </div>
                                    </div>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.tickets.show', $ticket) }}"
                                        wire:navigate
                                        class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition duration-150 ease-out transform hover:scale-[1.03] hover:bg-zinc-100 hover:shadow-md active:scale-[0.95] active:shadow-sm dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                    >
                                        Προβολή
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500">
                                Δεν βρέθηκαν ολοκληρωμένα δελτία.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $closedTickets->links() }}
        </div>
    </section>
</div>