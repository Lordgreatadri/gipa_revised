<x-layouts.workspace :title="__('Account')">
    <div class="grid gap-6 xl:grid-cols-[16rem_minmax(0,1fr)]">
        <nav class="h-max rounded-2xl border border-slate-200 bg-white p-3 shadow-card dark:border-slate-800 dark:bg-slate-900" aria-label="{{ __('Account sections') }}">
            <a href="#personal" class="block rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">{{ __('Personal details') }}</a>
            <a href="#email" class="mt-1 block rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">{{ __('E-mail') }}</a>
            <a href="#phone" class="mt-1 block rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">{{ __('Phone') }}</a>
            <a href="#password" class="mt-1 block rounded-xl px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5">{{ __('Password') }}</a>
            <a href="{{ route('investor.profile') }}" class="mt-1 block rounded-xl px-3 py-2 text-sm font-semibold text-brand-800 hover:bg-brand-50 dark:text-brand-200 dark:hover:bg-white/5">{{ __('Business profile') }}</a>
        </nav>

        <div class="grid gap-6">
            <section id="personal" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Personal details') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('The name on this login. Business information lives on the separate profile.') }}</p>
                <form method="POST" action="{{ route('investor.account.personal') }}" class="mt-5 max-w-lg">
                    @csrf
                    @method('PUT')
                    <x-ui.label for="name" required>{{ __('Full name') }}</x-ui.label>
                    <x-ui.input id="name" name="name" :value="old('name', $user->name)" required maxlength="150" />
                    <x-ui.input-error for="name" />
                    <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Role') }}</dt><dd class="mt-1 font-medium">{{ $user->roles->pluck('name')->join(', ') ?: __('Investor') }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('E-mail status') }}</dt><dd class="mt-1 font-medium">{{ $user->email_verified_at ? __('Verified') : __('Awaiting verification') }}</dd></div>
                    </dl>
                    <x-ui.button class="mt-5" variant="gold">{{ __('Save name') }}</x-ui.button>
                </form>
            </section>

            <section id="email" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('E-mail') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('A new address has to be verified before the investor workspace opens again.') }}</p>
                <form method="POST" action="{{ route('investor.account.email') }}" class="mt-5 max-w-lg grid gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-ui.label for="email" required>{{ __('E-mail address') }}</x-ui.label>
                        <x-ui.input id="email" name="email" type="email" :value="old('email', $user->email)" required />
                        <x-ui.input-error for="email" />
                    </div>
                    <div>
                        <x-ui.label for="email_password" required>{{ __('Current password') }}</x-ui.label>
                        <x-ui.input id="email_password" name="current_password" type="password" required autocomplete="current-password" />
                        <x-ui.input-error for="current_password" />
                    </div>
                    <x-ui.button variant="gold">{{ __('Update e-mail') }}</x-ui.button>
                </form>
            </section>

            <section id="phone" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Phone') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Use the international form, for example +233201234567. The new number must be verified.') }}</p>
                <form method="POST" action="{{ route('investor.account.phone') }}" class="mt-5 max-w-lg grid gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-ui.label for="phone" required>{{ __('Phone number') }}</x-ui.label>
                        <x-ui.input id="phone" name="phone" :value="old('phone', $user->phone)" required maxlength="20" />
                        <x-ui.input-error for="phone" />
                    </div>
                    <div>
                        <x-ui.label for="phone_password" required>{{ __('Current password') }}</x-ui.label>
                        <x-ui.input id="phone_password" name="current_password" type="password" required autocomplete="current-password" />
                    </div>
                    <x-ui.button variant="gold">{{ __('Update phone') }}</x-ui.button>
                </form>
            </section>

            <section id="password" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-card dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Password') }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Use at least 12 characters. This does not sign you out of this browser.') }}</p>
                <form method="POST" action="{{ route('investor.account.password') }}" class="mt-5 max-w-lg grid gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-ui.label for="current_password" required>{{ __('Current password') }}</x-ui.label>
                        <x-ui.input id="current_password" name="current_password" type="password" required autocomplete="current-password" />
                        <x-ui.input-error for="current_password" />
                    </div>
                    <div>
                        <x-ui.label for="password" required>{{ __('New password') }}</x-ui.label>
                        <x-ui.input id="password" name="password" type="password" required autocomplete="new-password" />
                        <x-ui.input-error for="password" />
                    </div>
                    <div>
                        <x-ui.label for="password_confirmation" required>{{ __('Confirm new password') }}</x-ui.label>
                        <x-ui.input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                    </div>
                    <x-ui.button variant="gold">{{ __('Update password') }}</x-ui.button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.workspace>
