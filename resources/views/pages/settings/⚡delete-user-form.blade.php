<?php

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component {
    use PasswordValidationRules;

    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form method="POST" wire:submit="deleteUser" class="space-y-6">
        <div>
            <flux:heading size="lg">Είστε βέβαιοι ότι θέλετε να διαγράψετε τον λογαριασμό σας;</flux:heading>

            <flux:subheading>
                Μόλις διαγραφεί ο λογαριασμός σας, όλα τα δεδομένα και οι σχετικοί πόροι θα διαγραφούν οριστικά. Παρακαλώ εισάγετε τον κωδικό σας για επιβεβαίωση.
            </flux:subheading>
        </div>

        <flux:input wire:model="password" label="Κωδικός" type="password" viewable />

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">Ακύρωση</flux:button>
            </flux:modal.close>

            <flux:button variant="danger" type="submit" data-test="confirm-delete-user-button">
                Διαγραφή λογαριασμού
            </flux:button>
        </div>
    </form>
</flux:modal>