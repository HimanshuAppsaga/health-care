<div class="w-full max-w-md">
    <!-- Branding/Icon Section -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-primary-container/20 mb-6 clinical-shadow shadow-primary/5">
            <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
        </div>
        <h1 class="font-manrope text-3xl font-black text-primary tracking-tight mb-3">Password Recovery</h1>
        <p class="text-on-surface-variant font-medium text-sm px-6 leading-relaxed">
            Enter your email address and we'll send you a secure link to reset your clinical access.
        </p>
    </div>

    <!-- Recovery Form -->
    <div class="bg-surface rounded-[2rem] clinical-shadow border border-outline-variant/30 p-10 relative overflow-hidden">
        @if ($status)
            <div class="mb-6 font-bold text-sm text-primary bg-primary-container/30 p-4 rounded-2xl border border-primary/10">
                {{ $status }}
            </div>
        @endif

        <form wire:submit.prevent="sendResetLink" class="space-y-6">
            <div class="space-y-2.5">
                <label class="block text-[10px] font-black text-outline pl-1 uppercase tracking-widest" for="email">
                    Email Address
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-outline-variant text-xl group-focus-within:text-primary transition-colors">mail</span>
                    </div>
                    <input wire:model="email" class="block w-full pl-12 pr-4 py-4 bg-surface-container-low border-transparent rounded-2xl text-sm font-bold text-on-background focus:ring-4 focus:ring-primary/5 focus:border-primary/30 focus:bg-surface transition-all placeholder:text-outline-variant/50 outline-none" id="email" name="email" placeholder="practitioner@clinicos.com" required="" type="email"/>
                </div>
                @error('email') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
            </div>
            
            <button class="w-full py-4 px-4 bg-primary text-on-primary font-black rounded-2xl clinical-shadow shadow-primary/20 hover:bg-primary/90 active:scale-[0.98] transition-all flex items-center justify-center gap-2 text-sm uppercase tracking-widest" type="submit">
                <span wire:loading.remove>Send Link</span>
                <span wire:loading>Processing...</span>
                <span wire:loading.remove class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </form>

        <div class="mt-10 pt-8 border-t border-outline-variant/20 border-dashed text-center">
            <a class="inline-flex items-center gap-2 text-primary font-black text-sm hover:text-primary/70 transition-all group" href="{{ route('login') }}" wire:navigate>
                <span class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Back to Login
            </a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-12 text-center">
        <p class="font-manrope text-[10px] uppercase tracking-[0.2em] font-black text-outline-variant opacity-60">
            © {{ date('Y') }} ClinicOS • Clinical Precision Platform
        </p>
    </footer>
</div>
