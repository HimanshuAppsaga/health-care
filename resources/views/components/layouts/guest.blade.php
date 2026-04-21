<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $title ?? 'Indigo Clinical' }}</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&family=Inter:wght@100..900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "tertiary": "#002e69",
                        "on-secondary-fixed": "#00201b",
                        "on-error": "#ffffff",
                        "on-primary": "#ffffff",
                        "surface": "#f8fafa",
                        "surface-tint": "#6b35e4",
                        "error-container": "#ffdad6",
                        "on-error-container": "#93000a",
                        "primary": "#390092",
                        "tertiary-container": "#004394",
                        "surface-dim": "#d8dada",
                        "on-secondary-fixed-variant": "#005046",
                        "secondary-fixed-dim": "#49dcc4",
                        "secondary-fixed": "#6cf9e0",
                        "inverse-primary": "#cebdff",
                        "on-surface-variant": "#494455",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-highest": "#e1e3e3",
                        "on-background": "#191c1d",
                        "surface-variant": "#e1e3e3",
                        "on-tertiary-container": "#90b4ff",
                        "inverse-on-surface": "#eff1f1",
                        "primary-fixed-dim": "#cebdff",
                        "secondary": "#006b5d",
                        "on-secondary-container": "#006f61",
                        "on-primary-fixed-variant": "#5202cc",
                        "surface-container": "#eceeee",
                        "on-surface": "#191c1d",
                        "surface-container-low": "#f2f4f4",
                        "primary-fixed": "#e8ddff",
                        "outline": "#7a7487",
                        "on-tertiary-fixed-variant": "#004395",
                        "on-secondary": "#ffffff",
                        "surface-container-high": "#e6e8e8",
                        "secondary-container": "#68f6dd",
                        "outline-variant": "#cbc3d8",
                        "tertiary-fixed-dim": "#adc6ff",
                        "on-tertiary-fixed": "#001a42",
                        "tertiary-fixed": "#d8e2ff",
                        "primary-container": "#5200cc",
                        "on-primary-container": "#bea7ff",
                        "surface-bright": "#f8fafa",
                        "error": "#ba1a1a",
                        "on-primary-fixed": "#21005d",
                        "background": "#f8fafa",
                        "on-tertiary": "#ffffff",
                        "inverse-surface": "#2e3131"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .headline { font-family: 'Manrope', sans-serif; }
        .geometric-bg {
            background-color: #f8fafa;
            background-image: radial-gradient(rgba(57, 0, 146, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    @livewireStyles
</head>
<body class="geometric-bg text-on-surface min-h-screen flex flex-col justify-center items-center p-4">
    {{ $slot }}

    @livewireScripts
</body>
</html>
