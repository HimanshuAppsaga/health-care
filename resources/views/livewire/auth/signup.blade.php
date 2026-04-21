<div class="w-full max-w-md space-y-8">
    <div class="flex flex-col items-center space-y-2 mb-12">
        <div class="flex items-center space-x-2">
            <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">medical_services</span>
            <span class="text-2xl font-headline font-black text-primary tracking-tighter uppercase">Indigo Clinical</span>
        </div>
        <p class="text-on-surface-variant font-body text-sm font-medium tracking-wide">Precision Sanctuary Enrollment</p>
    </div>

    <div class="bg-surface-container-lowest shadow-sm shadow-indigo-100/20 p-10 rounded-lg space-y-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
        <div class="relative space-y-2">
            <h1 class="text-3xl font-headline font-extrabold text-on-surface tracking-tight">Create Account</h1>
            <p class="text-on-surface-variant font-body text-sm">Join the ecosystem of elite healthcare precision.</p>
        </div>

        <form wire:submit.prevent="register" class="space-y-6">
            <div class="space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-headline font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="full_name">Full Name</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-lg transition-colors group-focus-within:text-primary">person</span>
                        <input wire:model="full_name" class="w-full bg-surface-container-low border-none rounded-lg py-3.5 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all duration-200 outline-none placeholder:text-outline-variant" id="full_name" name="full_name" placeholder="Dr. Julian Sterling" required="" type="text"/>
                    </div>
                    @error('full_name') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[11px] font-headline font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="email">Email Address</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-lg transition-colors group-focus-within:text-primary">alternate_email</span>
                        <input wire:model="email" class="w-full bg-surface-container-low border-none rounded-lg py-3.5 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all duration-200 outline-none placeholder:text-outline-variant" id="email" name="email" placeholder="precision@indigoclinical.com" required="" type="email"/>
                    </div>
                    @error('email') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-headline font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="password">Password</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-lg transition-colors group-focus-within:text-primary">lock</span>
                            <input wire:model="password" class="w-full bg-surface-container-low border-none rounded-lg py-3.5 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all duration-200 outline-none placeholder:text-outline-variant" id="password" name="password" placeholder="••••••••" required="" type="password"/>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-headline font-bold uppercase tracking-widest text-on-surface-variant ml-1" for="confirm_password">Confirm</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-lg transition-colors group-focus-within:text-primary">verified_user</span>
                            <input wire:model="password_confirmation" class="w-full bg-surface-container-low border-none rounded-lg py-3.5 pl-12 pr-4 text-sm font-medium focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all duration-200 outline-none placeholder:text-outline-variant" id="confirm_password" name="confirm_password" placeholder="••••••••" required="" type="password"/>
                        </div>
                    </div>
                </div>
                @error('password') <span class="text-error text-xs font-medium col-span-2">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center space-x-3 group cursor-pointer">
                <div class="relative flex items-center">
                    <input wire:model="terms" class="w-5 h-5 rounded border-outline-variant bg-surface-container-low text-primary focus:ring-primary/20 cursor-pointer transition-all duration-200" id="terms" name="terms" required="" type="checkbox"/>
                </div>
                <label class="text-[12px] font-medium text-on-surface-variant cursor-pointer select-none" for="terms">
                    I agree to the <a class="text-primary font-bold hover:underline underline-offset-2" href="#">Terms of Service</a> and <a class="text-primary font-bold hover:underline underline-offset-2" href="#">Privacy Protocol</a>.
                </label>
            </div>
            @error('terms') <span class="text-error text-xs font-medium block">{{ $message }}</span> @enderror

            <button class="w-full bg-primary text-on-primary font-headline font-extrabold text-sm py-4 rounded-lg shadow-lg shadow-indigo-200/50 hover:bg-primary-container active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2" type="submit">
                <span wire:loading.remove>Create Account</span>
                <span wire:loading>Processing...</span>
                <span wire:loading.remove class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </form>

        <div class="flex flex-col items-center pt-2 space-y-4">
            <div class="w-full flex items-center space-x-4">
                <div class="h-[1px] flex-1 bg-surface-container-high"></div>
                <span class="text-[10px] font-headline font-bold uppercase tracking-[0.2em] text-outline">or</span>
                <div class="h-[1px] flex-1 bg-surface-container-high"></div>
            </div>
            <p class="text-sm font-medium text-on-surface-variant">
                Already have an account? 
                <a class="text-secondary font-bold hover:text-on-secondary-container transition-colors ml-1" href="/login" wire:navigate>Log in</a>
            </p>
        </div>
    </div>

    <div class="flex flex-col md:flex-row items-center justify-between text-on-surface-variant text-[11px] font-headline font-bold uppercase tracking-widest opacity-60">
        <p>© 2024 Indigo Clinical</p>
        <div class="flex space-x-6 mt-4 md:mt-0">
            <a class="hover:text-primary transition-colors" href="#">Help Center</a>
            <a class="hover:text-primary transition-colors" href="#">System Status</a>
        </div>
    </div>
</div>
