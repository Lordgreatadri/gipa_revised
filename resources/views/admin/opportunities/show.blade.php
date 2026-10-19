<x-layouts.workspace :title="$opportunity->title">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $opportunity->reference }}</p>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $opportunity->district?->region?->name }} · {{ $opportunity->district?->name }}</p>
        </div>
        <span class="inline-flex w-fit rounded-full bg-brand-50 px-3 py-1.5 text-sm font-semibold text-brand-800 dark:bg-brand-950 dark:text-brand-200">{{ $opportunity->workflow_state->label() }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="grid gap-6">
            <x-ui.card :title="__('Record')" heading="h2">
                <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div><dt class="text-slate-500">{{ __('Sector') }}</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $opportunity->sector?->name }}@if ($opportunity->subSector) · {{ $opportunity->subSector->name }}@endif</dd></div>
                    <div><dt class="text-slate-500">{{ __('Enterprise') }}</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $opportunity->enterpriseType?->name }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Project status') }}</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $opportunity->project_status->label() }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Investment') }}</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $opportunity->currency }} {{ number_format((float) $opportunity->investment_amount, 2) }} · {{ $opportunity->investment_structure->label() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Location') }}</dt><dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ $opportunity->location_description }} ({{ $opportunity->latitude }}, {{ $opportunity->longitude }})</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Overview') }}</dt><dd class="mt-1 leading-6 text-slate-700 dark:text-slate-200">{{ $opportunity->description }}</dd></div>
                    @if ($opportunity->objectives)
                        <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Objectives') }}</dt><dd class="mt-1 leading-6 text-slate-700 dark:text-slate-200">{{ $opportunity->objectives }}</dd></div>
                    @endif
                </dl>
            </x-ui.card>

            <x-ui.card :title="__('Contact')" :description="__('Visible to authorized staff only.')" heading="h2">
                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $opportunity->contact_name }}@if ($opportunity->contact_organization) · {{ $opportunity->contact_organization }}@endif</p>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $opportunity->contact_phone }} · {{ $opportunity->contact_email }}</p>
            </x-ui.card>

            <x-ui.card :title="__('Supporting documents')" heading="h2">
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($opportunity->documents as $document)
                        <li class="flex items-center justify-between gap-3 py-4">
                            <span>
                                <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $document->category->label() }}</span>
                                <span class="text-xs text-slate-500">{{ $document->original_name }}</span>
                            </span>
                            <a href="{{ route('admin.opportunities.documents.download', [$opportunity, $document]) }}" class="link">{{ __('Download') }}</a>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-slate-500">{{ __('No documents stored.') }}</li>
                    @endforelse
                </ul>
                @if ($opportunity->workflow_state->isEditable())
                    <form method="POST" action="{{ route('admin.opportunities.documents.store', $opportunity) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-[12rem_minmax(0,1fr)_auto] sm:items-end">
                        @csrf
                        <div>
                            <x-ui.label for="category">{{ __('Category') }}</x-ui.label>
                            <select id="category" name="category" class="form-control">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->value }}">{{ $category->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-ui.label for="document">{{ __('File') }}</x-ui.label>
                            <input id="document" name="document" type="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                            <x-ui.input-error for="document" />
                        </div>
                        <x-ui.button variant="secondary">{{ __('Upload') }}</x-ui.button>
                    </form>
                @endif
            </x-ui.card>
        </div>

        <div class="grid gap-6 content-start">
            <x-ui.card :title="__('Decision')" heading="h2">
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Assigned to') }} {{ $opportunity->assignee?->name ?? __('nobody') }}.</p>
                <div class="mt-4 flex flex-col gap-3">
                    @can('opportunities.update')
                        @if ($opportunity->workflow_state->isEditable())
                            <a href="{{ route('admin.opportunities.edit', $opportunity) }}" class="cta border border-slate-300 text-slate-800 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-100 dark:hover:bg-slate-800">{{ __('Edit draft') }}</a>
                        @endif
                    @endcan
                    @foreach ($actions as $action)
                        <form method="POST" action="{{ route('admin.opportunities.transition', $opportunity) }}" class="grid gap-2 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            @csrf
                            <input type="hidden" name="action" value="{{ $action['action'] }}">
                            @if ($action['action'] === 'reassign')
                                <label class="text-xs font-semibold text-slate-500" for="assignee_id">{{ __('Staff member') }}</label>
                                <select id="assignee_id" name="assignee_id" class="form-control">
                                    @foreach ($staff as $member)
                                        <option value="{{ $member->uuid }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @if ($action['reason'])
                                <label class="text-xs font-semibold text-slate-500" for="reason-{{ $action['action'] }}">{{ __('Reason') }}</label>
                                <textarea id="reason-{{ $action['action'] }}" name="reason" rows="2" class="form-control" required></textarea>
                            @endif
                            <x-ui.button variant="{{ $action['action'] === 'reject' || $action['action'] === 'cancel' ? 'secondary' : 'primary' }}">{{ __($action['label']) }}</x-ui.button>
                        </form>
                    @endforeach
                </div>
                <x-ui.input-error for="reason" class="mt-3" />
                <x-ui.input-error for="assignee_id" />
            </x-ui.card>

            <x-ui.card :title="__('History')" heading="h2">
                <ol class="grid gap-4">
                    @forelse ($opportunity->transitions as $transition)
                        <li class="border-l-2 border-brand-200 pl-4 dark:border-brand-800">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ str_replace('_', ' ', ucfirst($transition->action)) }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $transition->actor?->name }} · {{ $transition->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                            @if ($transition->reason)
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $transition->reason }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">{{ __('No decisions recorded yet.') }}</li>
                    @endforelse
                </ol>
            </x-ui.card>
        </div>
    </div>
</x-layouts.workspace>
