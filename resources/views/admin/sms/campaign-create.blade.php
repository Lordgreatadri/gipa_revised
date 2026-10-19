<x-layouts.workspace :title="__('New bulk SMS')">
    <p class="mb-6 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('Each block is a separate send. A message is limited to 520 characters. People already in IOMP are addressed with one placeholder. A list you type or upload is sent exactly as written.') }}</p>

    <div class="grid gap-6 xl:grid-cols-2">
        <form method="POST" action="{{ route('admin.sms.campaigns.store') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-card xl:col-span-2 dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <input type="hidden" name="batch" value="directory">
            <div class="flex items-start gap-3">
                <span class="inline-flex size-10 items-center justify-center rounded-xl bg-brand-50 text-brand-800 dark:bg-brand-950 dark:text-brand-200"><i data-lucide="users" class="size-5"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('People already in IOMP') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Staff are addressed with [[NAME]]. Investors use either [[NAME]] or [[BUSINESS_NAME]], taken from the account or the business profile. Do not use both in one message.') }}</p>
                </div>
            </div>
            @if ($errors->any() && old('batch') === 'directory')
                <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
            @endif
            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <x-ui.label for="directory-name" required>{{ __('Campaign name') }}</x-ui.label>
                    <x-ui.input id="directory-name" name="name" maxlength="120" required :value="old('batch') === 'directory' ? old('name') : ''" />
                </div>
                <div>
                    <x-ui.label for="directory-placeholder" required>{{ __('Address them as') }}</x-ui.label>
                    <select id="directory-placeholder" name="placeholder" class="form-control" required>
                        <option value="name" @selected(old('batch') !== 'directory' || old('placeholder', 'name') === 'name')>{{ __('[[NAME]] — account name, including staff') }}</option>
                        <option value="business_name" @selected(old('batch') === 'directory' && old('placeholder') === 'business_name')>{{ __('[[BUSINESS_NAME]] — investor business profile') }}</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ __('Who receives it') }}</p>
                    <div class="mt-2 grid gap-2 text-sm text-slate-800 sm:grid-cols-2 dark:text-slate-100">
                        @foreach ([
                            'active_investors' => __('Active approved investors'),
                            'all_investors' => __('All investors with a phone number'),
                            'overdue_certificates' => __('Investors with an overdue certificate renewal'),
                        ] as $value => $label)
                            <label class="flex items-start gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                                <input type="radio" name="audience" value="{{ $value }}" @checked((old('batch') === 'directory' ? old('audience') : 'active_investors') === $value) required>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                        @if ($canMessageStaff)
                            <label class="flex items-start gap-2 rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-700">
                                <input type="radio" name="audience" value="all_staff" @checked(old('batch') === 'directory' && old('audience') === 'all_staff')>
                                <span>{{ __('All staff — [[NAME]] only') }}</span>
                            </label>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('Certificate renewal dates are not recorded yet. That audience stays empty until certificates are stored.') }}</p>
                </div>
                <div class="lg:col-span-2">
                    <x-ui.label for="directory-body" required>{{ __('Text') }}</x-ui.label>
                    <textarea id="directory-body" name="body" class="form-control" rows="4" maxlength="520" required>{{ old('batch') === 'directory' ? old('body') : '' }}</textarea>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('Example: Hello [[NAME]], the register is open. Or: Hello [[BUSINESS_NAME]], the register is open.') }}</p>
                </div>
            </div>
            <div>
                <button class="rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800" type="submit">{{ __('Send to these people') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.sms.campaigns.store') }}" class="grid content-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <input type="hidden" name="batch" value="numbers">
            <input type="hidden" name="audience" value="numbers">
            <div class="flex items-start gap-3">
                <span class="inline-flex size-10 items-center justify-center rounded-xl bg-sky-50 text-sky-800 dark:bg-sky-950 dark:text-sky-200"><i data-lucide="smartphone" class="size-5"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Numbers you type') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('These contacts are not looked up in IOMP. Separate numbers with semicolons. Everyone receives the same text.') }}</p>
                </div>
            </div>
            @if ($errors->any() && old('batch') === 'numbers')
                <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
            @endif
            <div>
                <x-ui.label for="numbers-name" required>{{ __('Campaign name') }}</x-ui.label>
                <x-ui.input id="numbers-name" name="name" maxlength="120" required :value="old('batch') === 'numbers' ? old('name') : ''" />
            </div>
            <div>
                <x-ui.label for="numbers-list" required>{{ __('Phone numbers') }}</x-ui.label>
                <textarea id="numbers-list" name="numbers" class="form-control" rows="3" maxlength="5000" required placeholder="0244000001; 0244000002">{{ old('batch') === 'numbers' ? old('numbers') : '' }}</textarea>
            </div>
            <div>
                <x-ui.label for="numbers-body" required>{{ __('Text') }}</x-ui.label>
                <textarea id="numbers-body" name="body" class="form-control" rows="4" maxlength="520" required>{{ old('batch') === 'numbers' ? old('body') : '' }}</textarea>
            </div>
            <div>
                <button class="rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800" type="submit">{{ __('Send to these numbers') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.sms.campaigns.store') }}" enctype="multipart/form-data" class="grid content-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-card dark:border-slate-800 dark:bg-slate-900">
            @csrf
            <input type="hidden" name="batch" value="file">
            <input type="hidden" name="audience" value="file">
            <div class="flex items-start gap-3">
                <span class="inline-flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-200"><i data-lucide="file-check-2" class="size-5"></i></span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('A file of phone numbers') }}</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Upload CSV or Excel. The phone column is used. Names in the file are not filled into the message.') }}</p>
                </div>
            </div>
            @if ($errors->any() && old('batch') === 'file')
                <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
            @endif
            <div>
                <x-ui.label for="file-name" required>{{ __('Campaign name') }}</x-ui.label>
                <x-ui.input id="file-name" name="name" maxlength="120" required :value="old('batch') === 'file' ? old('name') : ''" />
            </div>
            <div>
                <x-ui.label for="contacts" required>{{ __('Contact file') }}</x-ui.label>
                <input id="contacts" name="contacts" type="file" accept=".csv,.txt,.xlsx" class="form-control" required>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('A header row named phone, mobile, number, or msisdn is optional.') }}</p>
            </div>
            <div>
                <x-ui.label for="file-body" required>{{ __('Text') }}</x-ui.label>
                <textarea id="file-body" name="body" class="form-control" rows="4" maxlength="520" required>{{ old('batch') === 'file' ? old('body') : '' }}</textarea>
            </div>
            <div>
                <button class="rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-800" type="submit">{{ __('Send to this file') }}</button>
            </div>
        </form>
    </div>
</x-layouts.workspace>
