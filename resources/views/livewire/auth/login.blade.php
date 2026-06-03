<div>
    <!-- Top Anchor -->
    <div class="mb-12 flex flex-col items-center">
        <span class="text-2xl font-black text-primary tracking-tighter font-manrope">ClinicOS</span>
        <div class="h-1.5 w-12 bg-secondary mt-2 rounded-full"></div>
    </div>
    
    <!-- Login Card -->
    <main class="w-full max-w-[440px] bg-surface rounded-[2rem] clinical-shadow border border-outline-variant/30 p-8 md:p-12 relative overflow-hidden">
        <!-- Subtle Accent Line -->
        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
        <div class="mb-10">
            <h1 class="text-3xl font-black tracking-tight text-on-background mb-3 font-manrope">Welcome back</h1>
            <p class="text-sm font-medium text-outline leading-relaxed">Enter your credentials to access the clinical precision sanctuary.</p>
        </div>
        
        <form wire:submit.prevent="authenticate" class="space-y-6">
            <!-- Email Field -->
            <div class="space-y-2.5">
                <label class="text-[10px] font-black uppercase tracking-widest text-outline pl-1" for="email">Email Address</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline-variant group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-xl">mail</span>
                    </div>
                    <input wire:model="email" class="block w-full pl-12 pr-4 py-4 bg-surface-container-low border border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50" id="email" name="email" placeholder="practitioner@clinicos.com" required="" type="email"/>
                </div>
                @error('email') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
            </div>
            
            <!-- Password Field -->
            <div class="space-y-2.5">
                <div class="flex justify-between items-center pl-1">
                    <label class="text-[10px] font-black uppercase tracking-widest text-outline" for="password">Password</label>
                    <a class="text-[10px] font-black uppercase tracking-widest text-primary hover:text-primary/70 transition-colors" href="{{ route('password.request') }}" wire:navigate>Forgot password?</a>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-outline-variant group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-xl">lock</span>
                    </div>
                    <input wire:model="password" class="block w-full pl-12 pr-4 py-4 bg-surface-container-low border border-transparent focus:border-primary/30 focus:bg-surface focus:ring-4 focus:ring-primary/5 rounded-2xl text-sm font-bold text-on-background transition-all placeholder:text-outline-variant/50" id="password" name="password" placeholder="••••••••••••" required="" type="password"/>
                </div>
                @error('password') <span class="text-error text-xs font-bold pl-1">{{ $message }}</span> @enderror
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center pl-1">
                <input wire:model="remember" value="1" class="h-4 w-4 text-primary focus:ring-primary/20 border-outline-variant/50 rounded-lg cursor-pointer transition-all" id="remember_me" name="remember_me" type="checkbox"/>
                <label class="ml-3 block text-sm font-bold text-on-surface-variant cursor-pointer" for="remember_me">
                    Keep me logged in
                </label>
            </div>
            
            <!-- Login Button -->
            <button class="w-full bg-primary text-on-primary py-4 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-primary/90 transition-all active:scale-[0.98] clinical-shadow shadow-primary/20 flex items-center justify-center gap-2" type="submit">
                <span wire:loading.remove>Login to Dashboard</span>
                <span wire:loading>Authenticating...</span>
                <span class="material-symbols-outlined text-lg" wire:loading.remove>arrow_forward</span>
            </button>
        </form>
        
        <div class="mt-10 pt-10 border-t border-outline-variant/20 border-dashed text-center">
            <p class="text-sm font-bold text-on-surface-variant">
                New to the platform? 
                <a class="text-primary font-black hover:underline underline-offset-8 transition-all" href="{{ route('register') }}" wire:navigate>Create Account</a>
            </p>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="mt-12 text-center">
        <p class="font-manrope text-[10px] uppercase tracking-[0.2em] font-black text-outline-variant">
            © {{ date('Y') }} ClinicOS • Clinical Precision Platform
        </p>
    </footer>
</div>
