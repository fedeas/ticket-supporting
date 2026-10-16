<x-layouts::auth :title="'Σύνδεση'">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Σύνδεση στον λογαριασμό σας'" :description="'Συμπληρώστε το email και τον κωδικό σας για να συνδεθείτε'" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                label="Διεύθυνση email"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div x-data="{ capsLock: false }" class="relative">
                <flux:input
                    name="password"
                    label="Κωδικός"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Κωδικός"
                    viewable
                    @keydown="capsLock = $event.getModifierState('CapsLock')"
                    @keyup="capsLock = $event.getModifierState('CapsLock')"
                    @click="capsLock = $event.getModifierState('CapsLock')"
                    @blur="capsLock = false"
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        Ξεχάσατε τον κωδικό σας;
                    </flux:link>
                @endif

                <p
                    x-show="capsLock"
                    x-transition
                    class="mt-2 text-sm font-medium text-amber-600 dark:text-amber-400"
                >
                    Προσοχή: Το Caps Lock είναι ενεργό.
                </p>
            </div>

            <flux:checkbox name="remember" label="Να με θυμάσαι" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    Σύνδεση
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
                <span>Δεν έχετε λογαριασμό;</span>
                <flux:link :href="route('register')" wire:navigate>Εγγραφή</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>