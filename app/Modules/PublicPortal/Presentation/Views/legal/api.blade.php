<x-layouts.public :title="__('API documentation')" :description="__('How to call IOMP API version 1 from cURL, PHP, or Node.js, with every field and response.')">
    <section class="border-b border-slate-200 bg-brand-900 py-10 text-white sm:py-14 dark:border-slate-800">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <nav class="flex flex-wrap items-center gap-2 text-xs font-semibold text-brand-100" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="hover:text-white">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white" aria-current="page">{{ __('API reference') }}</span>
            </nav>
            <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-gold-400">{{ __('OpenAPI 3.0 · version 1') }}</p>
            <h1 class="font-display mt-3 text-3xl font-bold text-balance sm:text-4xl">{{ __('API reference') }}</h1>
            <p class="mt-4 max-w-3xl text-sm leading-7 text-brand-100 sm:text-base">{{ __('Choose an endpoint, read what it does, then copy the call in cURL, PHP, or Node.js. Each field lists its format. Each response status shows the body you should expect.') }}</p>
            <p class="mt-4 font-mono text-sm text-gold-300">{{ $baseUrl }}</p>
        </div>
    </section>

    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[16rem_minmax(0,1fr)] lg:py-10">
        <nav class="h-fit rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-24" aria-label="{{ __('Endpoints') }}">
            <form method="GET" action="{{ route('api.documentation') }}" class="grid gap-2" role="search">
                <label class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500" for="api-search">{{ __('Find an endpoint') }}</label>
                <input id="api-search" name="q" value="{{ $query }}" type="search" placeholder="{{ __('Path, method, or purpose') }}" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                <button type="submit" class="rounded-lg bg-brand-800 px-3 py-2 text-sm font-bold text-white">{{ __('Search') }}</button>
            </form>
            <ol class="mt-4 space-y-4 text-sm">
                @foreach (collect($operations)->groupBy('tag') as $tag => $group)
                    <li>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-slate-500">{{ $tag }}</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($group as $operation)
                                <li>
                                    <a class="flex items-start gap-2 rounded-lg px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-white/5" href="#{{ $operation['operation_id'] }}">
                                        <span @class([
                                            'mt-0.5 rounded px-1.5 py-0.5 font-mono text-[10px] font-bold text-white',
                                            'bg-emerald-700' => $operation['method'] === 'GET',
                                            'bg-sky-700' => $operation['method'] === 'POST',
                                        ])>{{ $operation['method'] }}</span>
                                        <span class="min-w-0 break-all font-mono text-xs text-slate-700 dark:text-slate-200">{{ $operation['path'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ol>
        </nav>

        <div class="min-w-0 space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('How every response is shaped') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ __('A successful call returns data and meta. A failed call returns error and meta. Branch on error.code. The request id is also the X-Request-Id response header.') }}</p>
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700 dark:text-emerald-300">{{ __('Success') }}</p>
                        <pre class="mt-2 overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-100">{!! e('{
  "data": {},
  "meta": { "request_id": "uuid" }
}') !!}</pre>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-700 dark:text-rose-300">{{ __('Failure') }}</p>
                        <pre class="mt-2 overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-100">{!! e('{
  "error": {
    "code": "validation_failed",
    "message": "The request is invalid.",
    "details": {}
  },
  "meta": { "request_id": "uuid" }
}') !!}</pre>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ __('Send Authorization: Bearer {access_token} on routes marked Bearer JWT. The token comes from POST /api/v1/auth/token. Protected investor routes also require a verified investor. An inquiry additionally requires an approved business profile.') }}</p>
            </section>

            @if ($operations === [])
                <p class="rounded-2xl border border-slate-200 bg-white px-4 py-6 text-sm text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200">{{ __('No endpoints match that search.') }}</p>
            @endif

            @foreach ($operations as $operation)
                <article id="{{ $operation['operation_id'] }}" class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                    <header class="border-b border-slate-200 px-5 py-4 dark:border-slate-800">
                        <div class="flex flex-wrap items-center gap-2">
                            <span @class([
                                'rounded-md px-2 py-1 font-mono text-xs font-bold text-white',
                                'bg-emerald-700' => $operation['method'] === 'GET',
                                'bg-sky-700' => $operation['method'] === 'POST',
                            ])>{{ $operation['method'] }}</span>
                            <code class="text-sm font-semibold text-slate-900 dark:text-white">{{ $operation['path'] }}</code>
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $operation['auth'] === 'bearer' ? __('Bearer JWT') : __('No token') }}</span>
                        </div>
                        <h2 class="mt-3 text-xl font-bold text-slate-900 dark:text-white">{{ $operation['summary'] }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $operation['description'] }}</p>
                    </header>

                    <div class="space-y-8 px-5 py-5">
                        @if ($operation['parameters'] !== [])
                            <section>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Parameters') }}</h3>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="w-full min-w-[44rem] text-left text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 dark:border-slate-800">
                                                <th class="py-2 pr-3 font-semibold">{{ __('Field') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('In') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Required') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Format') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Example') }}</th>
                                                <th class="py-2 font-semibold">{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($operation['parameters'] as $parameter)
                                                <tr class="border-b border-slate-100 align-top dark:border-slate-800">
                                                    <td class="py-2 pr-3 font-mono text-xs">{{ $parameter['name'] }}</td>
                                                    <td class="py-2 pr-3">{{ $parameter['in'] }}</td>
                                                    <td class="py-2 pr-3">{{ $parameter['required'] ? __('required') : __('optional') }}</td>
                                                    <td class="py-2 pr-3">{{ $parameter['format'] }}</td>
                                                    <td class="py-2 pr-3 font-mono text-xs">{{ $parameter['example'] }}</td>
                                                    <td class="py-2 text-slate-600 dark:text-slate-300">{{ $parameter['description'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif

                        @if ($operation['request_fields'] !== [])
                            <section>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Request body') }}</h3>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Content-Type: application/json') }}</p>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="w-full min-w-[44rem] text-left text-sm">
                                        <thead>
                                            <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 dark:border-slate-800">
                                                <th class="py-2 pr-3 font-semibold">{{ __('Field') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Type') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Format') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Required') }}</th>
                                                <th class="py-2 pr-3 font-semibold">{{ __('Example') }}</th>
                                                <th class="py-2 font-semibold">{{ __('Description') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($operation['request_fields'] as $field)
                                                <tr class="border-b border-slate-100 align-top dark:border-slate-800">
                                                    <td class="py-2 pr-3 font-mono text-xs">{{ $field['name'] }}</td>
                                                    <td class="py-2 pr-3">{{ $field['type'] }}</td>
                                                    <td class="py-2 pr-3">{{ $field['format'] }}</td>
                                                    <td class="py-2 pr-3">{{ $field['required'] }}</td>
                                                    <td class="py-2 pr-3 font-mono text-xs">{{ $field['example'] }}</td>
                                                    <td class="py-2 text-slate-600 dark:text-slate-300">{{ $field['description'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif

                        <section class="api-switch" aria-label="{{ __('Sample requests') }}">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Sample request') }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('The same call in the stack you use. Replace YOUR_ACCESS_TOKEN and the sample values.') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2" role="tablist">
                                @foreach (['curl' => 'cURL', 'php' => 'PHP', 'node' => 'Node.js'] as $panel => $label)
                                    <input class="api-tab-input" type="radio" name="samples-{{ $operation['operation_id'] }}" id="samples-{{ $operation['operation_id'] }}-{{ $panel }}" data-panel="{{ $panel }}" @checked($panel === 'curl')>
                                    <label class="api-tab cursor-pointer rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 dark:border-slate-600 dark:text-slate-200" for="samples-{{ $operation['operation_id'] }}-{{ $panel }}">{{ $label }}</label>
                                @endforeach
                            </div>
                            @foreach (['curl', 'php', 'node'] as $panel)
                                <div class="api-panel mt-3" data-panel="{{ $panel }}" role="tabpanel">
                                    <pre class="overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-100">{{ $operation['samples'][$panel] }}</pre>
                                </div>
                            @endforeach
                        </section>

                        <section class="api-switch" aria-label="{{ __('Responses') }}">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('Responses') }}</h3>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ __('Every status this endpoint returns, with the fields in that body.') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2" role="tablist">
                                @foreach ($operation['responses'] as $response)
                                    <input class="api-tab-input" type="radio" name="responses-{{ $operation['operation_id'] }}" id="responses-{{ $operation['operation_id'] }}-{{ $response['status'] }}" data-status="{{ $response['status'] }}" @checked($loop->first)>
                                    <label class="api-tab cursor-pointer rounded-lg border border-slate-300 px-3 py-1.5 font-mono text-sm font-bold text-slate-700 dark:border-slate-600 dark:text-slate-200" for="responses-{{ $operation['operation_id'] }}-{{ $response['status'] }}">{{ $response['status'] }}</label>
                                @endforeach
                            </div>
                            @foreach ($operation['responses'] as $response)
                                <div class="api-panel mt-3" data-status="{{ $response['status'] }}" role="tabpanel">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $response['status'] }} {{ $response['title'] }}</p>
                                    <p class="mt-1 text-sm leading-6 text-slate-700 dark:text-slate-300">{{ $response['description'] }}</p>
                                    <div class="mt-3 overflow-x-auto">
                                        <table class="w-full min-w-[44rem] text-left text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-200 text-xs uppercase tracking-wider text-slate-500 dark:border-slate-800">
                                                    <th class="py-2 pr-3 font-semibold">{{ __('Field') }}</th>
                                                    <th class="py-2 pr-3 font-semibold">{{ __('Type') }}</th>
                                                    <th class="py-2 pr-3 font-semibold">{{ __('Format') }}</th>
                                                    <th class="py-2 pr-3 font-semibold">{{ __('Example') }}</th>
                                                    <th class="py-2 font-semibold">{{ __('Description') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($response['fields'] as $field)
                                                    <tr class="border-b border-slate-100 align-top dark:border-slate-800">
                                                        <td class="py-2 pr-3 font-mono text-xs">{{ $field['name'] }}</td>
                                                        <td class="py-2 pr-3">{{ $field['type'] }}</td>
                                                        <td class="py-2 pr-3">{{ $field['format'] }}</td>
                                                        <td class="py-2 pr-3 font-mono text-xs">{{ $field['example'] }}</td>
                                                        <td class="py-2 text-slate-600 dark:text-slate-300">{{ $field['description'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-5 text-slate-100">{{ $response['body'] }}</pre>
                                </div>
                            @endforeach
                        </section>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-layouts.public>
