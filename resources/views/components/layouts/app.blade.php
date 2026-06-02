<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

        @livewireStyles
        <style>
            [x-cloak] {
                display: none !important;
            }
            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #E2E8F0;
                border-radius: 10px;
            }
            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }
            .custom-glass {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
            }
        </style>
    </head>
    <body class="antialiased bg-background text-on-background">
        @auth
            <div x-data="{ mobileSidebarOpen: false }" class="flex h-screen font-manrope relative overflow-hidden">
                <!-- Sidebar Wrapper -->
                <div 
                    :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                    class="fixed inset-y-0 left-0 z-50 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 bg-surface shrink-0"
                >
                    <livewire:common.sidebar />
                </div>

                <!-- Overlay for mobile sidebar -->
                <div 
                    x-show="mobileSidebarOpen" 
                    x-cloak
                    @click="mobileSidebarOpen = false"
                    class="fixed inset-0 z-40 bg-black/40 lg:hidden transition-opacity duration-300"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                ></div>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col overflow-hidden min-w-0">
                    <!-- Header -->
                    <livewire:common.header :title="$title ?? ''" />

                    <!-- Dashboard Content -->
                    <main class="flex-1 overflow-y-auto custom-scrollbar">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        @else
            {{ $slot }}
        @endauth


        @livewireScripts
    </body>
</html>
