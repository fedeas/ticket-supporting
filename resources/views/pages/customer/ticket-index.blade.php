<?php

use App\Models\Ticket;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $status = '';
    public string $search = '';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'tickets' => Ticket::query()
                ->where('customer_id', auth()->id())
                ->with('customer')
                ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
                ->when($this->search !== '', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('title', 'like', '%'.$this->search.'%')
                            ->orWhere('reference_number', 'like', '%'.$this->search.'%')
                            ->orWhere('registration_number', 'like', '%'.$this->search.'%')
                            ->orWhere('vehicle_registration', 'like', '%'.$this->search.'%')
                            ->orWhere('recorded_by', 'like', '%'.$this->search.'%')
                            ->orWhere('fault_description', 'like', '%'.$this->search.'%')
                            ->orWhereHas('customer', function ($customerQuery) {
                                $customerQuery->where('name', 'like', '%'.$this->search.'%')
                                    ->orWhere('email', 'like', '%'.$this->search.'%');
                            });
                    });
                })
                ->latest()
                ->paginate(10),
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

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Όλα τα Δελτία</h1>
            <p class="text-sm text-zinc-500">Παρακολούθηση όλων των δελτίων βλάβης.</p>
        </div>

        <a
            href="{{ route('customer.tickets.create') }}"
            wire:navigate
            class="inline-flex items-center rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-150 ease-out transform hover:scale-[1.03] hover:shadow-lg hover:brightness-110 active:scale-[0.95] active:shadow-sm dark:bg-white dark:text-zinc-900"
        >
            Νέο Δελτίο
        </a>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="md:col-span-2">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Αναζήτηση με θέμα, αρ. δελτίου, αρ. συστήματος, όχημα ή χρήστη..."
                class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900"
            >
        </div>

        <div>
            <select
                wire:model.live="status"
                class="w-full rounded-2xl border border-zinc-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900"
            >
                <option value="">Όλες οι Καταστάσεις</option>
                <option value="backlog">Σε Εκκρεμότητα</option>
                <option value="in_progress">Σε Εξέλιξη</option>
                <option value="done">Ολοκληρωμένο</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Θέμα</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμός Δελτίου</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμός Συστήματος</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Αριθμός Οχήματος</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Άνοιξε από</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Κατάσταση</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">Ώρα Αναγγελίας</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">Ενέργειες</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/30">
                            <td class="px-4 py-4 font-medium">
                                <div>{{ $ticket->title }}</div>

                                @if(
                                    (!empty($ticket->equipment_types) && is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
                                    || ($ticket->has_request && !empty($ticket->request_text))
                                )
                                    <div class="mt-1 space-y-1 text-xs text-zinc-500">
                                        @if(!empty($ticket->equipment_types) && is_array($ticket->equipment_types) && count($ticket->equipment_types) > 0)
                                            <div>
                                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Εξοπλισμός:</span>
                                                {{ implode(' / ', $ticket->equipment_types) }}
                                            </div>
                                        @endif

                                        @if($ticket->has_request && !empty($ticket->request_text))
                                            <div>
                                                <span class="font-medium text-zinc-700 dark:text-zinc-300">Request:</span>
                                                {{ \Illuminate\Support\Str::limit($ticket->request_text, 70) }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $ticket->reference_number ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $ticket->registration_number ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $ticket->vehicle_number }}</td>
                            <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $ticket->opened_by_name }}</td>
                            <td class="px-4 py-4"><x-status-badge :status="$ticket->status" /></td>
                            <td class="px-4 py-4 text-sm text-zinc-500">{{ optional($ticket->opened_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('customer.tickets.show', $ticket) }}"
                                        wire:navigate
                                        class="inline-flex items-center rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition duration-150 ease-out transform hover:scale-[1.03] hover:bg-zinc-100 hover:shadow-md active:scale-[0.95] active:shadow-sm dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                    >
                                        Προβολή
                                    </a>

                                    <a
                                        href="{{ route('customer.tickets.edit', $ticket) }}"
                                        wire:navigate
                                        class="inline-flex items-center rounded-lg bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition duration-150 ease-out transform hover:scale-[1.03] hover:shadow-md active:scale-[0.95] active:shadow-sm dark:bg-white dark:text-zinc-900"
                                    >
                                        Επεξεργασία
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-500">Δεν βρέθηκαν δελτία.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $tickets->links() }}
    </div>
</div>
