<?php

use App\Models\Ticket;
use Livewire\Component;

new class extends Component {
    public Ticket $ticket;

    public string $reference_number_part_1 = '';
    public string $reference_number_part_2 = '';
    public string $reference_number_part_3 = '';

    public string $department = '';
    public string $station_unit = '';
    public string $contact_person = '';
    public string $contact_phone = '';
    public string $installation_location = '';
    public string $vehicle_registration = '';
    public string $report_channel = 'email';

    public array $equipment_types = [];

    public bool $has_request = false;
    public string $request_text = '';

    public string $title = '';
    public string $fault_description = '';

    public function mount(Ticket $ticket): void
    {
        abort_unless($ticket->customer_id === auth()->id(), 403);

        $this->ticket = $ticket->load('customer');

        $this->fillReferenceParts($ticket->reference_number ?? '');

        $this->department = $ticket->department ?? '';
        $this->station_unit = $ticket->station_unit ?? '';
        $this->contact_person = $ticket->contact_person ?? '';
        $this->contact_phone = $ticket->contact_phone ?? '';
        $this->installation_location = $ticket->installation_location ?? '';
        $this->vehicle_registration = $ticket->vehicle_registration ?? '';
        $this->report_channel = $ticket->report_channel ?: 'email';
        $this->equipment_types = is_array($ticket->equipment_types) ? $ticket->equipment_types : [];
        $this->has_request = (bool) $ticket->has_request;
        $this->request_text = $ticket->request_text ?? '';
        $this->title = $ticket->title ?? '';
        $this->fault_description = $ticket->fault_description ?: ($ticket->description ?? '');
    }

    private function fillReferenceParts(string $referenceNumber): void
    {
        $cleanReference = trim($referenceNumber);

        if (preg_match('/^AVL\s+([^\/]+)\/([^\/]+)\/(.+)$/u', $cleanReference, $matches)) {
            $this->reference_number_part_1 = trim($matches[1]);
            $this->reference_number_part_2 = trim($matches[2]);
            $this->reference_number_part_3 = trim($matches[3]);

            return;
        }

        $this->reference_number_part_1 = '';
        $this->reference_number_part_2 = '';
        $this->reference_number_part_3 = '';
    }

    public function updatedHasRequest($value): void
    {
        if (! $value) {
            $this->request_text = '';
        }
    }

    protected function messages(): array
    {
        return [
            'reference_number_part_1.required' => 'Το πρώτο πεδίο του αριθμού δελτίου είναι υποχρεωτικό.',
            'reference_number_part_2.required' => 'Το δεύτερο πεδίο του αριθμού δελτίου είναι υποχρεωτικό.',
            'reference_number_part_3.required' => 'Το τρίτο πεδίο του αριθμού δελτίου είναι υποχρεωτικό.',

            'department.required' => 'Το πεδίο Τμήμα / Διεύθυνση / Υπηρεσία / Μονάδα είναι υποχρεωτικό.',
            'contact_person.required' => 'Το Πρόσωπο Επικοινωνίας είναι υποχρεωτικό.',
            'contact_phone.required' => 'Το τηλέφωνο επικοινωνίας είναι υποχρεωτικό.',
            'contact_phone.regex' => 'Το τηλέφωνο πρέπει να περιέχει ακριβώς 8 ψηφία.',
            'report_channel.required' => 'Ο τρόπος αναγγελίας είναι υποχρεωτικός.',
            'report_channel.in' => 'Ο τρόπος αναγγελίας δεν είναι έγκυρος.',
            'title.required' => 'Το θέμα / τίτλος βλάβης είναι υποχρεωτικό.',
            'title.max' => 'Το θέμα / τίτλος βλάβης είναι πολύ μεγάλο.',
            'fault_description.required' => 'Η περιγραφή βλάβης είναι υποχρεωτική.',
            'fault_description.min' => 'Η περιγραφή βλάβης πρέπει να έχει τουλάχιστον 5 χαρακτήρες.',
            'request_text.required_if' => 'Πρέπει να συμπληρώσετε το αίτημα όταν είναι επιλεγμένο το Request.',
            'request_text.min' => 'Το αίτημα πρέπει να έχει τουλάχιστον 3 χαρακτήρες.',
        ];
    }

    public function save()
    {
        abort_unless($this->ticket->customer_id === auth()->id(), 403);

        $this->validate([
            'reference_number_part_1' => ['required', 'string', 'max:50'],
            'reference_number_part_2' => ['required', 'string', 'max:50'],
            'reference_number_part_3' => ['required', 'string', 'max:50'],

            'department' => ['required', 'string', 'max:255'],
            'station_unit' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'regex:/^[0-9]{8}$/'],
            'installation_location' => ['nullable', 'string', 'max:255'],
            'vehicle_registration' => ['nullable', 'string', 'max:255'],
            'report_channel' => ['required', 'in:email,fax,phone'],

            'equipment_types' => ['nullable', 'array'],
            'title' => ['required', 'string', 'max:255'],
            'fault_description' => ['required', 'string', 'min:5'],

            'has_request' => ['boolean'],
            'request_text' => ['nullable', 'string', 'min:3', 'max:5000', 'required_if:has_request,true'],
        ]);

        $hasEquipment = count($this->equipment_types) > 0;
        $hasValidRequest = $this->has_request && trim($this->request_text) !== '';

        if (! $hasEquipment && ! $hasValidRequest) {
            $this->addError(
                'equipment_or_request',
                'Πρέπει να επιλέξετε τουλάχιστον ένα στοιχείο εξοπλισμού ή να επιλέξετε Request και να συμπληρώσετε το αίτημα.'
            );

            return;
        }

        $referenceNumber =
            'AVL ' .
            trim($this->reference_number_part_1) . '/' .
            trim($this->reference_number_part_2) . '/' .
            trim($this->reference_number_part_3);

        $this->ticket->update([
            'reference_number' => $referenceNumber,
            'department' => $this->department,
            'station_unit' => $this->station_unit,
            'contact_person' => $this->contact_person,
            'contact_phone' => $this->contact_phone,
            'installation_location' => $this->installation_location,
            'vehicle_registration' => $this->vehicle_registration,
            'report_channel' => $this->report_channel,
            'equipment_types' => $this->equipment_types,
            'has_request' => $this->has_request,
            'request_text' => $this->has_request ? trim($this->request_text) : null,
            'title' => $this->title,
            'description' => $this->fault_description,
            'fault_description' => $this->fault_description,
        ]);

        session()->flash('success', 'Το δελτίο βλάβης ενημερώθηκε επιτυχώς.');

        return $this->redirect(route('customer.tickets.show', $this->ticket), navigate: true);
    }

    public function cancel(): mixed
    {
        return $this->redirect(route('customer.tickets.show', $this->ticket), navigate: true);
    }
};

?>

<div class="mx-auto max-w-5xl space-y-6">
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

    <div class="space-y-4">
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Επεξεργασία Δελτίου Βλάβης</h1>
                <p class="text-sm text-zinc-500">
                    Αριθμός Συστήματος: {{ $ticket->registration_number ?? '—' }}
                </p>
            </div>

            <button
                type="button"
                wire:click="cancel"
                class="inline-flex items-center rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition duration-150 ease-out transform hover:scale-[1.03] hover:bg-zinc-100 hover:shadow-md active:scale-[0.95] active:shadow-sm dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                Πίσω
            </button>
        </div>

        <div class="flex justify-end">
            <div class="md:max-w-md rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-900" style="width:50%">
                <label class="mb-3 block text-sm font-semibold uppercase tracking-wide text-zinc-700 dark:text-zinc-200">
                    ΑΡΙΘΜΟΣ ΔΕΛΤΙΟΥ ΒΛΑΒΗΣ
                </label>

                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-zinc-100 px-3 py-3 text-sm font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                        AVL
                    </span>

                    <input
                        wire:model="reference_number_part_1"
                        type="text"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                    >

                    <span class="text-lg font-semibold text-zinc-500">/</span>

                    <input
                        wire:model="reference_number_part_2"
                        type="text"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                    >

                    <span class="text-lg font-semibold text-zinc-500">/</span>

                    <input
                        wire:model="reference_number_part_3"
                        type="text"
                        class="w-full rounded-xl border border-zinc-300 px-3 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                    >
                </div>

                @error('reference_number_part_1') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                @error('reference_number_part_2') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                @error('reference_number_part_3') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium">Τμήμα / Διεύθυνση / Υπηρεσία / Μονάδα</label>
                <input wire:model="department" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('department') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Σταθμός / Κλιμάκιο</label>
                <input wire:model="station_unit" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('station_unit') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Πρόσωπο Επικοινωνίας</label>
                <input wire:model="contact_person" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('contact_person') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Τηλέφωνο Επικοινωνίας</label>
                <div class="flex overflow-hidden rounded-xl border border-zinc-300 bg-white focus-within:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-950">
                    <div class="flex items-center border-e border-zinc-300 bg-zinc-100 px-4 text-sm font-medium text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        +357
                    </div>

                    <input
                        wire:model.live="contact_phone"
                        type="text"
                        inputmode="numeric"
                        maxlength="8"
                        placeholder="99999999"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,8)"
                        class="w-full border-0 bg-transparent px-4 py-3 text-sm focus:outline-none"
                    >
                </div>

                <p class="mt-1 text-xs text-zinc-500">Συμπληρώστε 8 ψηφία, π.χ. 99123456</p>
                @error('contact_phone') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Χώρος Εγκατάστασης</label>
                <input wire:model="installation_location" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('installation_location') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium">Αριθμός Εγγραφής Οχήματος</label>
                <input wire:model="vehicle_registration" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                @error('vehicle_registration') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Τρόπος Αναγγελίας</label>
            <select wire:model="report_channel" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <option value="email">Ηλεκτρονικό Ταχυδρομείο (e-mail)</option>
                <option value="fax">Τηλεομοιοτυπία (fax)</option>
                <option value="phone">Τηλέφωνο</option>
            </select>
            @error('report_channel') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-3 block text-sm font-medium">Στοιχεία Εξοπλισμού</label>

            <div class="grid gap-3 md:grid-cols-2">
                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="GPS/GPRS Module στο Αυτοκίνητο">
                    <span>GPS/GPRS Module στο Αυτοκίνητο</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Τερματικός Ηλεκτρονικός Υπολογιστής">
                    <span>Τερματικός Ηλεκτρονικός Υπολογιστής</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Android tablet στο Αυτοκίνητο">
                    <span>Android tablet στο Αυτοκίνητο</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Server (Λογισμικό)">
                    <span>Server (Λογισμικό)</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Φορητό Σύστημα Εντοπισμού">
                    <span>Φορητό Σύστημα Εντοπισμού</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Server (Hardware)">
                    <span>Server (Hardware)</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Buzzers">
                    <span>Buzzers</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="OBD">
                    <span>OBD</span>
                </label>

                <label class="flex items-center gap-2 text-sm">
                    <input wire:model="equipment_types" type="checkbox" value="Tags">
                    <span>Tags</span>
                </label>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/60">
            <label class="flex items-center gap-3 text-sm font-medium">
                <input wire:model.live="has_request" type="checkbox">
                <span>Request</span>
            </label>

            @if($has_request)
                <div class="mt-4">
                    <textarea
                        wire:model="request_text"
                        rows="5"
                        placeholder="Γράψτε εδώ το request..."
                        class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                    ></textarea>
                    @error('request_text') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            @endif

            @error('equipment_or_request')
                <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Θέμα / Τίτλος Βλάβης</label>
            <input wire:model="title" type="text" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950">
            @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Περιγραφή Βλάβης</label>
            <textarea wire:model="fault_description" rows="7" class="w-full rounded-xl border border-zinc-300 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-950"></textarea>
            @error('fault_description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-2 block text-sm font-medium">Καταχωρήθηκε από</label>
            <input
                type="text"
                value="{{ $ticket->opened_by_name }}"
                readonly
                class="w-full cursor-not-allowed rounded-xl border border-zinc-300 bg-zinc-100 px-4 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
            >
        </div>

        <div class="flex justify-end gap-3">
            <button
                type="button"
                wire:click="cancel"
                class="inline-flex items-center rounded-xl border border-zinc-300 px-5 py-3 text-sm font-medium text-zinc-700 transition duration-150 ease-out transform hover:scale-[1.03] hover:bg-zinc-100 hover:shadow-md active:scale-[0.95] active:shadow-sm dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
            >
                Ακύρωση
            </button>

            <button
                type="submit"
                class="inline-flex items-center rounded-xl bg-zinc-900 px-5 py-3 text-sm font-medium text-white shadow-sm transition duration-150 ease-out transform hover:scale-[1.03] hover:shadow-lg hover:brightness-110 active:scale-[0.95] active:shadow-sm dark:bg-white dark:text-zinc-900"
            >
                Αποθήκευση Αλλαγών
            </button>
        </div>
    </form>
</div>
