<div class="relative z-10 w-full max-w-md px-6 py-12">
    <div class="bg-surface clinical-shadow border border-outline-variant/30 rounded-[2rem] overflow-hidden">
        <!-- Header Section -->
        <div class="p-10 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-primary-container/20 mb-6 clinical-shadow shadow-primary/5">
                <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
            </div>
            <h1 class="text-3xl font-black text-primary tracking-tight mb-3 font-manrope">Reset Password</h1>
            <p class="text-sm font-medium text-on-surface-variant leading-relaxed px-4">Create a new, highly secure password for your clinical portal.</p>
        </div>

        <!-- Form Section -->
        <form wire:submit.prevent="resetPassword" class="p-10 space-y-6 pt-0">
            <!-- Email Field -->
            <div class="space-y-2.5">
                <label class="block text-[10px] uppercase tracking-widest font-black text-outline pl-1" for="email">Email Address</label>
                <div class="relative group">
                    <input wire:model="email" class="w-full bg-surface-container-low border-transparent rounded-2xl py-4 px-4 text-sm font-bold text-on-surface transition-all duration-200 outline-none" id="email" type="email" readonly/>
                </div>
                @error('email') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
            </div>

            <!-- OTP Field -->
            <div class="space-y-2.5">
                <label class="block text-[10px] uppercase tracking-widest font-black text-outline pl-1" for="otp">4-Digit Security Code</label>
                <div class="relative group">
                    <input wire:model="otp" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 px-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none tracking-[0.5em] text-center" id="otp" placeholder="••••" type="text" maxlength="4" autocomplete="off"/>
                </div>
                @error('otp') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
            </div>

            <!-- New Password Field -->
            <div class="space-y-2.5">
                <label class="block text-[10px] uppercase tracking-widest font-black text-outline pl-1" for="new_password">New Password</label>
                <div class="relative group">
                    <input wire:model="password" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 px-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none" id="new_password" placeholder="••••••••••••" type="password"/>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="space-y-2.5">
                <label class="block text-[10px] uppercase tracking-widest font-black text-outline pl-1" for="confirm_password">Confirm New Password</label>
                <div class="relative group">
                    <input wire:model="password_confirmation" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 px-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none" id="confirm_password" placeholder="••••••••••••" type="password"/>
                </div>
                @error('password') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
            </div>

            <!-- Action Button -->
            <button class="w-full bg-primary text-on-primary py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-primary/90 transition-all duration-300 active:scale-[0.98] clinical-shadow shadow-primary/20 flex items-center justify-center gap-2" type="submit">
                <span wire:loading.remove>Update Access</span>
                <span wire:loading>Updating...</span>
                <span class="material-symbols-outlined text-lg" wire:loading.remove>check_circle</span>
            </button>

            <!-- Back to Login Link -->
            <div class="text-center pt-4">
                <a class="text-sm font-black text-primary hover:underline underline-offset-8 transition-all flex items-center justify-center gap-2" href="{{ route('login') }}" wire:navigate>
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back to sign in
                </a>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer class="mt-12 text-center">
        <p class="font-manrope text-[10px] uppercase tracking-[0.2em] font-black text-outline-variant opacity-60">
            © {{ date('Y') }} Clinic Saga • Precision Security Layer
        </p>
    </footer>
</div>
