<div class="w-full max-w-md">
    <!-- Branding/Icon Section -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-primary-fixed mb-4">
            <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
        </div>
        <h1 class="font-headline text-3xl font-extrabold text-primary tracking-tight mb-2">Password Recovery</h1>
        <p class="text-on-surface-variant font-medium text-sm px-4">
            Enter your email address and we'll send you a link to reset your password.
        </p>
    </div>

    <!-- Recovery Form -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm shadow-indigo-100/20 p-8 border border-outline-variant/15">
        @if ($status)
            <div class="mb-4 font-medium text-sm text-secondary bg-secondary-container/20 p-4 rounded-lg">
                {{ $status }}
            </div>
        @endif

        <form wire:submit.prevent="sendResetLink" class="space-y-6">
            <div class="space-y-2">
                <label class="block text-[12px] font-semibold text-primary uppercase tracking-widest" for="email">
                    Email Address
                </label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-outline text-xl group-focus-within:text-primary transition-colors">mail</span>
                    </div>
                    <input wire:model="email" class="block w-full pl-10 pr-3 py-3 bg-surface-container-low border-transparent rounded-lg text-sm focus:ring-2 focus:ring-primary focus:bg-white transition-all placeholder:text-outline/50" id="email" name="email" placeholder="name@clinical.com" required="" type="email"/>
                </div>
                @error('email') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
            </div>
            
            <button class="w-full py-3 px-4 bg-primary text-on-primary font-headline font-bold rounded-lg hover:bg-primary-container active:scale-[0.98] transition-all flex items-center justify-center gap-2" type="submit">
                <span wire:loading.remove>Send Reset Link</span>
                <span wire:loading>Processing...</span>
                <span wire:loading.remove class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-outline-variant/20 text-center">
            <a class="inline-flex items-center gap-2 text-secondary font-semibold text-sm hover:text-on-secondary-container transition-colors group" href="/login" wire:navigate>
                <span class="material-symbols-outlined text-lg group-hover:-translate-x-1 transition-transform">arrow_back</span>
                Back to Login
            </a>
        </div>
    </div>

    <!-- Contextual Illustration/Graphic -->
    <div class="mt-12 opacity-40 mix-blend-multiply flex justify-center">
        <img alt="Abstract clinical graphic" class="h-24 w-auto grayscale" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDKo_axws3tIm7aO19lLTkbzqADqNHELnjaYiNtp0LTzibG4zwRASiUkWfJlCyHM2xTapFfsp92zmlkhnY6niarz0-gbrUOnBerVdyLfk6LceZ6WBQfhNUH_WTW7xBv_su3ehnP6UUVykfMis0S0-TECFXzUqnWfuelW48ksU9LiLBSwwwo_fgL6HZq7eGcSfh62C68e2MKSq8V9EYrlxOFAm5XhUC8Pfa_3f-ITwBOh_ZgQSG5ZLmGnQJ3Q34ofkTs3vHcWMy63_Ve"/>
    </div>
</div>
