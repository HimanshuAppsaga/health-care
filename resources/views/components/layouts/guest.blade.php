<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $title ?? 'ClinicOS' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Inter:wght@100..900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .geometric-bg {
            background-color: var(--color-background);
            background-image: radial-gradient(var(--color-primary) 1px, transparent 1px);
            background-size: 40px 40px;
            background-repeat: repeat;
            opacity: 0.03;
            position: fixed;
            inset: 0;
            z-index: -1;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-background text-on-surface min-h-screen flex flex-col justify-center items-center p-4 antialiased font-inter">
    <div class="geometric-bg"></div>
    {{ $slot }}

    @livewireScripts
</body>
</html>
