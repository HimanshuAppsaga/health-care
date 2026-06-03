<div class="w-full max-w-md space-y-8">
    <!-- Top Anchor -->
    <div class="flex flex-col items-center space-y-2 mb-12">
        <div class="flex items-center space-x-2">
            <span class="material-symbols-outlined text-primary text-4xl" style="font-variation-settings: 'FILL' 1;">medical_services</span>
            <span class="text-2xl font-black text-primary tracking-tighter font-manrope">Clinic Saga</span>
        </div>
        <p class="text-on-surface-variant font-bold text-[10px] uppercase tracking-[0.2em]">Precision Sanctuary Enrollment</p>
    </div>

    <div class="bg-surface clinical-shadow border border-outline-variant/30 p-10 rounded-[2rem] space-y-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full -mr-16 -mt-16 blur-3xl"></div>
        <div class="relative space-y-2">
            <h1 class="text-3xl font-black text-on-background tracking-tight font-manrope">Create Account</h1>
            <p class="text-on-surface-variant text-sm font-medium">Join the ecosystem of elite healthcare precision.</p>
        </div>

        <form wire:submit.prevent="register" class="space-y-6">
            <div class="space-y-5">
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-outline ml-1" for="full_name">Full Name</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant text-lg transition-colors group-focus-within:text-primary">person</span>
                        <input wire:model="full_name" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none" id="full_name" name="full_name" placeholder="Dr. Julian Sterling" required="" type="text"/>
                    </div>
                    @error('full_name') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-outline ml-1" for="email">Email Address</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant text-lg transition-colors group-focus-within:text-primary">alternate_email</span>
                        <input wire:model="email" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none" id="email" name="email" placeholder="precision@clinicsaga.com" required="" type="email"/>
                    </div>
                    @error('email') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-outline ml-1" for="password">Password</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant text-lg transition-colors group-focus-within:text-primary">lock</span>
                            <input wire:model="password" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none" id="password" name="password" placeholder="••••••••" required="" type="password"/>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-outline ml-1" for="confirm_password">Confirm</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant text-lg transition-colors group-focus-within:text-primary">verified_user</span>
                            <input wire:model="password_confirmation" class="w-full bg-surface-container-low border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50 outline-none" id="confirm_password" name="confirm_password" placeholder="••••••••" required="" type="password"/>
                        </div>
                    </div>
                </div>
                @error('password') <span class="text-error text-xs font-bold pl-1 col-span-2">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center space-x-3 group cursor-pointer pl-1">
                <div class="relative flex items-center">
                    <input wire:model="terms" class="w-5 h-5 rounded-lg border-outline-variant/50 bg-surface-container-low text-primary focus:ring-primary/20 cursor-pointer transition-all duration-200" id="terms" name="terms" required="" type="checkbox"/>
                </div>
                <label class="text-sm font-bold text-on-surface-variant cursor-pointer select-none" for="terms">
                    I agree to the <a class="text-primary font-black hover:underline underline-offset-4" href="#">Terms</a> and <a class="text-primary font-black hover:underline underline-offset-4" href="#">Privacy Protocol</a>.
                </label>
            </div>
            @error('terms') <span class="text-error text-xs font-bold block pl-1">{{ $message }}</span> @enderror

            <button class="w-full bg-primary text-on-primary font-black text-sm py-4 rounded-2xl clinical-shadow shadow-primary/20 hover:bg-primary/90 active:scale-[0.98] transition-all duration-200 flex items-center justify-center space-x-2" type="submit">
                <span wire:loading.remove>Create Account</span>
                <span wire:loading>Processing...</span>
                <span wire:loading.remove class="material-symbols-outlined text-lg">arrow_forward</span>
            </button>
        </form>

        <div class="flex flex-col items-center pt-2 space-y-4">
            <div class="w-full flex items-center space-x-4">
                <div class="h-[1px] flex-1 bg-outline-variant/10"></div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-outline-variant">or</span>
                <div class="h-[1px] flex-1 bg-outline-variant/10"></div>
            </div>
            <p class="text-sm font-bold text-on-surface-variant">
                Already have an account? 
                <a class="text-primary font-black hover:underline underline-offset-8 transition-all ml-1" href="{{ route('login') }}" wire:navigate>Log in</a>
            </p>
        </div>
    </div>

    <div class="flex flex-col md:flex-row items-center justify-between text-outline-variant text-[10px] font-black uppercase tracking-[0.2em] opacity-60">
        <p>© {{ date('Y') }} Clinic Saga</p>
        <div class="flex space-x-6 mt-4 md:mt-0">
            <a class="hover:text-primary transition-colors" href="#">Help Center</a>
            <a class="hover:text-primary transition-colors" href="#">System Status</a>
        </div>
    </div>
</div>
