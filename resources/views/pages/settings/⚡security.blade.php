<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Ρυθμίσεις Ασφάλειας')] class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Ο τρέχων κωδικός είναι υποχρεωτικός.',
            'current_password.current_password' => 'Ο τρέχων κωδικός δεν είναι σωστός.',
            'password.required' => 'Ο νέος κωδικός είναι υποχρεωτικός.',
            'password.confirmed' => 'Η επιβεβαίωση κωδικού δεν ταιριάζει.',
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">Ρυθμίσεις Ασφάλειας</flux:heading>

    <x-pages::settings.layout :heading="'Ασφάλεια'" :subheading="'Αλλάξτε τον κωδικό πρόσβασής σας'">
        <form wire:submit="updatePassword" class="my-6 w-full space-y-6">
            <flux:input
                wire:model="current_password"
                label="Τρέχων Κωδικός"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />

            <flux:input
                wire:model="password"
                label="Νέος Κωδικός"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <flux:input
                wire:model="password_confirmation"
                label="Επιβεβαίωση Νέου Κωδικού"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">
                        Αλλαγή Κωδικού
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    Ο κωδικός ενημερώθηκε.
                </x-action-message>
            </div>
        </form>
    </x-pages::settings.layout>
</section>