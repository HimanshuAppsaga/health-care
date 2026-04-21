<div class="relative z-10 w-full max-w-md px-6 py-12">
    <div class="bg-surface-container-lowest shadow-xl shadow-indigo-900/5 rounded-xl overflow-hidden">
        <!-- Header Section -->
        <div class="p-8 text-center bg-white">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-lg bg-primary-fixed mb-6">
                <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
            </div>
            <h1 class="text-2xl font-extrabold text-indigo-900 tracking-tight mb-2">Reset Password</h1>
            <p class="text-sm font-medium text-on-surface-variant font-inter">Create a new password for your account.</p>
        </div>

        <!-- Form Section -->
        <form wire:submit.prevent="resetPassword" class="p-8 space-y-6 pt-0">
            <!-- Email Field (Hidden or Readonly usually, but showing for confirmation) -->
            <div class="space-y-2">
                <label class="block text-[12px] uppercase tracking-widest font-semibold text-on-surface-variant" for="email">Email Address</label>
                <div class="relative group">
                    <input wire:model="email" class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/20 rounded-lg py-3 px-4 text-on-surface placeholder:text-outline-variant transition-all duration-200" id="email" type="email" readonly/>
                </div>
                @error('email') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
            </div>

            <!-- New Password Field -->
            <div class="space-y-2">
                <label class="block text-[12px] uppercase tracking-widest font-semibold text-on-surface-variant" for="new_password">New Password</label>
                <div class="relative group">
                    <input wire:model="password" class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/20 rounded-lg py-3 px-4 text-on-surface placeholder:text-outline-variant transition-all duration-200" id="new_password" placeholder="••••••••••••" type="password"/>
                </div>
            </div>

            <!-- Confirm Password Field -->
            <div class="space-y-2">
                <label class="block text-[12px] uppercase tracking-widest font-semibold text-on-surface-variant" for="confirm_password">Confirm New Password</label>
                <div class="relative group">
                    <input wire:model="password_confirmation" class="w-full bg-surface-container-low border-none focus:ring-2 focus:ring-primary/20 rounded-lg py-3 px-4 text-on-surface placeholder:text-outline-variant transition-all duration-200" id="confirm_password" placeholder="••••••••••••" type="password"/>
                </div>
                @error('password') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
            </div>

            <!-- Action Button -->
            <button class="w-full bg-primary text-on-primary py-4 rounded-lg font-bold tracking-tight hover:bg-primary-container hover:text-on-primary-container transition-all duration-300 active:scale-[0.98] shadow-md shadow-primary/10" type="submit">
                <span wire:loading.remove>Reset Password</span>
                <span wire:loading>Updating...</span>
            </button>

            <!-- Back to Login Link -->
            <div class="text-center pt-2">
                <a class="text-sm font-semibold text-secondary hover:text-on-secondary-container transition-colors duration-200 flex items-center justify-center gap-2" href="/login" wire:navigate>
                    <span class="material-symbols-outlined text-lg">arrow_back</span>
                    Back to sign in
                </a>
            </div>
        </form>
    </div>

    <!-- Trust Indicators -->
    <div class="mt-8 flex items-center justify-center gap-8 opacity-50 grayscale transition-all hover:opacity-100 hover:grayscale-0">
        <img alt="HIPAA Compliant" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuABOHZoO8lyOkaljYUACBIWuSEICsloroJmvQm2Q1L74eKwpBWx-24Xqf39_UcfJs8JGLLKuZ7Tv7BvacGoNws4ZBERawbWjcALGs-6GXba4SZtcxDH4PIEbiU5CRKd-YsyFdmTzpaoEwpaxTu6M_nF7pdSog2FXUEAN5QAIbGAtvYuqHwCbb5GgQmqtpe4sMKljUaXjnUmPh1asFl6EZjpc8SRKR1oLkzgbKDIaG1jzM0a_rl2DI_vpoeG0_KP6oLnQmcGuHAvpPw2"/>
        <img alt="SOC2 Certified" class="h-5" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAdyndfxQhvML4n4akm-m1hFG926AB4CcWrf_yG_3zYYOtQrr39Yt6NaZFvBVsm6mDc-0CWsnqtsu_ZK2YRplqqb76LFyEel6T3le5FCHD7PLC7_Opm8pZZTRjtI3m55TwgG7xVlWevOr6CFlLOnc8n3nIMWKylzxxjBIkAMJ5K-yOOfRF9ztuVFzVMGCxGzYGowBwtCuq3Vg18xX_RL4PYsRSESBYMkMm6qPvcAoivNBsbjT8KjDaFKGdWRUp53K6IPJOduWaZ_jOp"/>
    </div>
</div>
