<div class="px-8 py-8">
    <div class="max-w-4xl mx-auto">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-primary mb-2">Edit Clinic</h1>
            <p class="text-outline font-medium">Update clinic profile, location, and working configuration.</p>
        </div>

        <!-- Success Message -->
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Main Card -->
        <div class="bg-surface rounded-[2rem] border overflow-hidden">

            <div class="p-8 space-y-8">

                {{-- LOGO SECTION --}}
                <div>
                    <label class="text-sm font-bold mb-3 block">Clinic Logo</label>

                    {{-- CURRENT LOGO --}}
                    @if(!$logo && $clinic->logo && !$removeLogo)
                        <div class="mb-4 flex items-center gap-4">
                            <div>
                                <p class="text-xs mb-1">Current Logo</p>
                                <img src="{{ asset('storage/'.$clinic->logo) }}"
                                     class="w-20 h-20 rounded-xl border">
                            </div>

                            <button wire:click="removeExistingLogo"
                                    type="button"
                                    class="px-3 py-2 text-sm bg-red-100 text-red-600 rounded-lg">
                                Remove
                            </button>
                        </div>
                    @endif

                    {{-- NEW PREVIEW --}}
                    @if ($logo)
                        <div class="mb-4 flex items-center gap-4">
                            <div>
                                <p class="text-xs mb-1">New Logo Preview</p>
                                <img src="{{ $logo->temporaryUrl() }}"
                                     class="w-20 h-20 rounded-xl border border-green-500">

                                <p class="text-xs mt-1">
                                    {{ $logo->getClientOriginalName() }}
                                </p>
                            </div>

                            <button wire:click="cancelNewLogo"
                                    type="button"
                                    class="px-3 py-2 text-sm bg-gray-200 rounded-lg">
                                Cancel
                            </button>
                        </div>
                    @endif

                    {{-- INPUT --}}
                    <input type="file" wire:model="logo" class="block w-full text-sm">

                    {{-- LOADING --}}
                    <div wire:loading wire:target="logo" class="text-sm text-blue-500 mt-2">
                        Uploading...
                    </div>
                </div>

                {{-- NAME --}}
                <div>
                    <label class="text-sm font-bold mb-2 block">Clinic Name</label>
                    <input type="text" wire:model="name"
                           class="w-full border-2 rounded-2xl px-6 py-4">
                </div>

                {{-- DESCRIPTION --}}
                <div>
                    <label class="text-sm font-bold mb-2 block">Description</label>
                    <textarea wire:model="description"
                              class="w-full border-2 rounded-2xl px-6 py-4"></textarea>
                </div>

                {{-- ADDRESS --}}
                <div>
                    <label class="text-sm font-bold mb-2 block">Address</label>
                    <input type="text" wire:model="address"
                           class="w-full border-2 rounded-2xl px-6 py-4">
                </div>

                {{-- CONTACT --}}
                <div>
                    <label class="text-sm font-bold mb-2 block">Contact Number</label>
                    <input type="text" wire:model="contact_number"
                           class="w-full border-2 rounded-2xl px-6 py-4">
                </div>

                {{-- ABOUT --}}
                <div>
                    <label class="text-sm font-bold mb-2 block">About Clinic</label>
                    <textarea wire:model="about_clinic"
                              class="w-full border-2 rounded-2xl px-6 py-4"></textarea>
                </div>

                {{-- LOCATION --}}
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" wire:model="latitude" placeholder="Latitude"
                           class="border-2 rounded-2xl px-6 py-4">

                    <input type="text" wire:model="longitude" placeholder="Longitude"
                           class="border-2 rounded-2xl px-6 py-4">
                </div>

                {{-- WORKING HOURS --}}
                <div>
                    <label class="text-sm font-bold mb-4 block">Working Hours</label>

                    @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                        <div class="flex items-center gap-4 mb-2">
                            <span class="w-28 capitalize text-sm font-bold">{{ $day }}</span>

                            <input type="text"
                                   wire:model="working_hours.{{ $day }}"
                                   class="flex-1 border-2 rounded-2xl px-4 py-3"
                                   placeholder="9 AM - 5 PM">
                        </div>
                    @endforeach
                </div>

            </div>

            <!-- Footer -->
            <div class="p-8 border-t flex justify-end">
                <button wire:click="update"
                        class="px-8 py-4 bg-blue-600 text-white rounded-2xl">
                    Update Clinic
                </button>
            </div>

        </div>
    </div>
</div>