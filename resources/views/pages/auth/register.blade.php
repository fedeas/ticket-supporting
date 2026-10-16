<x-layouts::auth :title="'Εγγραφή'">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Δημιουργία λογαριασμού'" :description="'Συμπληρώστε τα στοιχεία σας για να δημιουργήσετε λογαριασμό'" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="name"
                label="Ονοματεπώνυμο"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Ονοματεπώνυμο"
            />

            <flux:input
                name="email"
                label="Διεύθυνση email"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div x-data="{ capsLockPassword: false, capsLockConfirmation: false }" class="flex flex-col gap-6">
                <div>
                    <flux:input
                        name="password"
                        label="Κωδικός"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Κωδικός"
                        viewable
                        @keydown="capsLockPassword = $event.getModifierState('CapsLock')"
                        @keyup="capsLockPassword = $event.getModifierState('CapsLock')"
                        @click="capsLockPassword = $event.getModifierState('CapsLock')"
                        @blur="capsLockPassword = false"
                    />

                    <p
                        x-show="capsLockPassword"
                        x-transition
                        class="mt-2 text-sm font-medium text-amber-600 dark:text-amber-400"
                    >
                        Προσοχή: Το Caps Lock είναι ενεργό.
                    </p>
                </div>

                <div>
                    <flux:input
                        name="password_confirmation"
                        label="Επιβεβαίωση κωδικού"
                        type="password"
                        required
                        autocomplete="new-password"
                        placeholder="Επιβεβαίωση κωδικού"
                        viewable
                        @keydown="capsLockConfirmation = $event.getModifierState('CapsLock')"
                        @keyup="capsLockConfirmation = $event.getModifierState('CapsLock')"
                        @click="capsLockConfirmation = $event.getModifierState('CapsLock')"
                        @blur="capsLockConfirmation = false"
                    />

                    <p
                        x-show="capsLockConfirmation"
                        x-transition
                        class="mt-2 text-sm font-medium text-amber-600 dark:text-amber-400"
                    >
                        Προσοχή: Το Caps Lock είναι ενεργό.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    Δημιουργία λογαριασμού
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>Έχετε ήδη λογαριασμό;</span>
            <flux:link :href="route('login')" wire:navigate>Σύνδεση</flux:link>
        </div>
    </div>
</x-layouts::auth>