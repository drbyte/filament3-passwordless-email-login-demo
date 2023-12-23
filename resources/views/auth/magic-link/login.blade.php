<x-filament-panels::page.simple>
    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.before') }}


    <x-filament-panels::form wire:submit.prevent="authenticate" class="space-y-8">

        @if(session()->has('danger'))
            <div class="p-4 mb-4 text-sm text-danger-700 bg-danger-500/10 rounded-lg dark:bg-danger-900/50 dark:text-danger-700" role="alert">
                {{ session('danger') }}
            </div>
        @endif

        @if (! $this->submitted)
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />

            <p><em>{{ $login_message }}</em></p>
        @else
            <p>
                A login link has been sent via email.<br>
                Simply click the link in the email to access this site.<br>
                (This saves having to remember more passwords!)<br>
                <p>The email title is "<span style="font-weight: bold">{{ $email_subject }}</span>".</p>
                <p class="text-sm">If you don't see the email within 2 minutes, check your junk/spam folders just in case they're misdirected!</p>
            </p>
        @endif
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}
</x-filament-panels::page.simple>
