<div class="max-w-5xl mx-auto p-6 space-y-6">

    {{-- EMPTY STATE --}}
    @if(!$clinic)
        <div class="p-4 bg-red-100 text-red-700 rounded-lg">
            Clinic not found
        </div>
    @else

    {{-- HEADER --}}
    <div class="bg-white shadow-lg rounded-2xl p-6 border flex gap-5 items-center">

        {{-- LOGO --}}
        <div class="w-20 h-20 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center">
            @if($clinic->logo)
                <img src="{{ asset('storage/'.$clinic->logo) }}" class="w-full h-full object-cover">
            @else
                <span class="text-gray-400 text-xs">No Logo</span>
            @endif
        </div>

        {{-- NAME + DESCRIPTION --}}
        <div>
            <h1 class="text-3xl font-bold text-gray-800">
                {{ $clinic->name }}
            </h1>

            <p class="text-gray-600 mt-1">
                {{ $clinic->description ?? 'No description available' }}
            </p>
        </div>
    </div>

    {{-- GRID --}}
    <div class="grid md:grid-cols-2 gap-6">

        {{-- ADDRESS + CONTACT --}}
        <div class="bg-white shadow rounded-xl p-5 border space-y-2">
            <h2 class="text-lg font-semibold">Contact & Address</h2>

            <p><span class="font-medium">Phone:</span> {{ $clinic->contact_number ?? 'N/A' }}</p>

            <p><span class="font-medium">Address:</span> {{ $clinic->address ?? 'N/A' }}</p>
        </div>

        {{-- LOCATION --}}
        <div class="bg-white shadow rounded-xl p-5 border">
            <h2 class="text-lg font-semibold mb-2">Location</h2>

            @if($clinic->latitude && $clinic->longitude)
                <p class="text-sm text-gray-600 mb-2">
                    {{ $clinic->latitude }}, {{ $clinic->longitude }}
                </p>

                <a target="_blank"
                   class="text-blue-600 underline"
                   href="https://www.google.com/maps?q={{ $clinic->latitude }},{{ $clinic->longitude }}">
                    Open Map
                </a>
            @else
                <p class="text-gray-500">No location set</p>
            @endif
        </div>
    </div>

    {{-- ABOUT --}}
    @if($clinic->about_clinic)
    <div class="bg-white shadow rounded-xl p-5 border">
        <h2 class="text-lg font-semibold mb-2">About Clinic</h2>
        <p class="text-gray-700">{{ $clinic->about_clinic }}</p>
    </div>
    @endif

    {{-- WORKING HOURS --}}
    <div class="bg-white shadow rounded-xl p-5 border">
        <h2 class="text-lg font-semibold mb-4">Working Hours</h2>

        @php
            $hours = is_array($clinic->working_hours)
                ? $clinic->working_hours
                : json_decode($clinic->working_hours, true);
        @endphp

        @if(!empty($hours))
            <div class="space-y-2">
                @foreach($hours as $day => $time)
                    <div class="flex justify-between border-b pb-1">
                        <span class="capitalize">{{ $day }}</span>
                        <span>{{ $time }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No working hours available</p>
        @endif
    </div>

    @endif

</div>