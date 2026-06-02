<div class="px-4 sm:px-8 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
            <h1 class="text-3xl font-black text-primary mb-2">Clinic Settings</h1>
            <p class="text-outline font-medium">Manage your clinic's technical configurations and API integrations.</p>
        </div>

        @if (session()->has('message'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <span class="material-symbols-outlined">check_circle</span>
                <span class="font-bold">{{ session('message') }}</span>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3 animate-fade-in">
                <span class="material-symbols-outlined">error</span>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- API Key Card -->
        <div class="bg-surface rounded-[2rem] clinical-shadow border border-outline-variant overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-outline-variant bg-surface-container-low">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-container/20 rounded-2xl flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">key</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-black">API Integration</h2>
                        <p class="text-sm text-outline font-medium">Use this key to authenticate external services with your clinic.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                @if($apiKey)
                    <div class="space-y-6">
                        <div class="p-6 bg-surface-container-highest rounded-2xl border border-outline-variant group relative">
                            <label class="text-xs font-bold text-outline uppercase tracking-widest block mb-3">Your API Key</label>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
                                <code class="flex-1 text-sm font-mono font-bold text-primary break-all bg-white/50 p-3 rounded-xl border border-outline-variant/30 text-center sm:text-left">
                                    {{ $apiKey }}
                                </code>
                                <button 
                                    onclick="navigator.clipboard.writeText('{{ $apiKey }}'); alert('Copied to clipboard!');"
                                    class="p-3 bg-primary text-white rounded-xl hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 flex items-center justify-center flex-shrink-0"
                                    title="Copy to clipboard"
                                >
                                    <span class="material-symbols-outlined">content_copy</span>
                                </button>
                            </div>
                            <p class="mt-4 text-xs text-error font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">warning</span>
                                Keep this key secure. Do not share it in public repositories or with unauthorized personnel.
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-2xl border border-blue-100 text-blue-700">
                            <span class="material-symbols-outlined">info</span>
                            <p class="text-sm font-medium">This API key has been generated and saved. You can use it to access our REST APIs.</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-primary-container/10 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="material-symbols-outlined text-5xl text-primary/40">api</span>
                        </div>
                        <h3 class="text-lg font-bold mb-2">No API Key Generated</h3>
                        <p class="text-outline text-sm max-w-md mx-auto mb-8 font-medium">
                            Generate an API key to start integrating your clinic with third-party applications and services.
                        </p>
                        
                        <button 
                            wire:click="generateApiKey"
                            wire:loading.attr="disabled"
                            class="px-8 py-4 bg-primary text-white rounded-2xl font-black text-lg clinical-shadow shadow-primary/30 hover:bg-primary-container hover:text-on-primary-container transition-all active:scale-95 flex items-center gap-3 mx-auto"
                        >
                            <span wire:loading.remove class="material-symbols-outlined">add_circle</span>
                            <span wire:loading class="material-symbols-outlined animate-spin">refresh</span>
                            <span>Create API Key</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Queue Settings Card -->
        <div class="mt-8 bg-surface rounded-[2rem] clinical-shadow border border-outline-variant overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-outline-variant bg-surface-container-low">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-secondary-container/20 rounded-2xl flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined text-2xl" style="font-variation-settings: 'FILL' 1;">queue</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-black">Queue Settings</h2>
                        <p class="text-sm text-outline font-medium">Configure how the patient queue behaves.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="max-w-md">
                    <label for="transferDepth" class="text-sm font-bold text-on-surface mb-2 block">Transfer Token Depth (1-9)</label>
                    <p class="text-xs text-outline mb-4">Number of positions a patient moves back when "Transfer Token" is clicked.</p>
                    
                    <div class="relative">
                        <input 
                            type="number" 
                            id="transferDepth"
                            wire:model.live="transferDepth"
                            min="1" 
                            max="9" 
                            oninput="if(this.value.length > 1) this.value = this.value.slice(0, 1);"
                            class="w-32 bg-surface border-2 border-outline-variant rounded-2xl px-6 py-4 text-2xl font-black text-primary focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all outline-none"
                        >
                        @error('transferDepth') 
                            <span class="text-error text-xs font-bold mt-2 block">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings Placeholder -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            @if($clinic)
                <a href="{{ route('doctor.clinic.detail', $clinic->id) }}" wire:navigate class="block bg-surface p-6 rounded-2xl border border-outline-variant hover:border-primary hover:bg-surface-container-low transition-all group">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="material-symbols-outlined text-outline group-hover:text-primary transition-colors">domain</span>
                        <h3 class="font-bold text-on-background group-hover:text-primary transition-colors">Clinic Profile</h3>
                    </div>
                    <p class="text-sm text-outline">Update clinic name, address, and contact details.</p>
                </a>
            @else
                <div class="bg-surface p-6 rounded-2xl border border-outline-variant opacity-50 cursor-not-allowed">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="material-symbols-outlined text-outline">domain</span>
                        <h3 class="font-bold">Clinic Profile</h3>
                    </div>
                    <p class="text-sm text-outline">Update clinic name, address, and contact details.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
    .clinical-shadow {
        box-shadow: 0 10px 40px -10px rgba(0, 91, 176, 0.1);
    }
</style>
