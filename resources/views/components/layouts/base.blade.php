@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#0f5132">
    @if ($description)
        <meta name="description" content="{{ $description }}">
    @endif

    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'IOMP') }}</title>

    {{-- Page icons are derived from public/images/gipa-logo.png (the GIPA mark). --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/gipa-icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    {{-- Apply the remembered theme before first paint. External file so CSP never needs an inline script. --}}
    <script src="{{ asset('js/theme-boot.js') }}"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body {{ $attributes->merge(['class' => 'min-h-full flex flex-col']) }}>
    {{ $slot }}
</body>
</html>
