<div>
    <!-- Top Anchor (Hidden Shell Rule: We only use the Brand Identity from TopNavBar but suppress the shell itself) -->
    <div class="mb-12 flex flex-col items-center">
        <span class="text-xl font-black text-indigo-900 dark:text-white tracking-tighter headline">Indigo Clinical</span>
        <div class="h-1 w-12 bg-secondary mt-2 rounded-full"></div>
    </div>
    
    <!-- Login Card -->
    <main class="w-full max-w-[440px] bg-surface-container-lowest rounded-lg shadow-sm shadow-indigo-100/20 p-8 md:p-12 relative overflow-hidden">
        <!-- Subtle Accent Line (Asymmetric brand touch) -->
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary"></div>
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold tracking-tight text-on-background mb-2">Welcome back</h1>
            <p class="text-sm font-medium text-outline">Enter your credentials to access the clinical precision sanctuary.</p>
        </div>
        
        <form wire:submit.prevent="authenticate" class="space-y-6">
            <!-- Email Field -->
            <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-outline" for="email">Email Address</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">mail</span>
                    </div>
                    <input wire:model="email" class="block w-full pl-10 pr-4 py-3 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded text-sm font-medium transition-all" id="email" name="email" placeholder="practitioner@indigoclinical.com" required="" type="email"/>
                </div>
                @error('email') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
            </div>
            
            <!-- Password Field -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label class="text-xs font-semibold uppercase tracking-widest text-outline" for="password">Password</label>
                    <a class="text-xs font-bold text-secondary hover:text-on-secondary-container transition-colors" href="/forgot-password" wire:navigate>Forgot password?</a>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-outline group-focus-within:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <input wire:model="password" class="block w-full pl-10 pr-4 py-3 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded text-sm font-medium transition-all" id="password" name="password" placeholder="••••••••••••" required="" type="password"/>
                </div>
                @error('password') <span class="text-error text-xs font-medium">{{ $message }}</span> @enderror
            </div>
            
            <!-- Remember Me -->
            <div class="flex items-center">
                <input wire:model="remember" class="h-4 w-4 text-primary focus:ring-primary-container border-outline-variant rounded" id="remember_me" name="remember_me" type="checkbox"/>
                <label class="ml-2 block text-sm font-medium text-on-surface-variant" for="remember_me">
                    Remember me
                </label>
            </div>
            
            <!-- Login Button -->
            <button class="w-full bg-primary text-on-primary py-4 rounded font-bold text-sm uppercase tracking-widest hover:bg-primary-container transition-all active:scale-[0.98] shadow-sm shadow-indigo-200" type="submit">
                <span wire:loading.remove>Login</span>
                <span wire:loading>Authenticating...</span>
            </button>
        </form>
        
        <div class="mt-8 pt-8 border-t border-surface-container border-dashed text-center">
            <p class="text-sm font-medium text-on-surface-variant">
                Don't have an account? 
                <a class="text-secondary font-bold hover:underline underline-offset-4 transition-all" href="/register" wire:navigate>Sign up</a>
            </p>
        </div>
    </main>
    
    <!-- Contextual Aesthetic Element -->
    <div class="mt-12 opacity-40 pointer-events-none">
        <img class="w-24 h-1 grayscale mix-blend-multiply rounded-full" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCIbHBPQeE4rGv6iSaFqr1UKO2ViibUsHyG9SRG54rcxecedQIU8hoFGWgQKqQv7guKOdSTEVGR-WF8AS13CdA8cjCNtRVZD1d_PNXo72Pm4R7EQ6tPrXy0OzVFa2jG6P2hWg1kXv49GdWQ19Pc3L6CQCZwoqgfkxbu3ypeUblFEJNbhbILRdf9BOttA-u4yTjyx9KzfjeM9zV_LupI-Ga6ZUz5EHK6aYhODH1yhqUgtjrgN4TLG1I5ruhnXQPp1m3avLypiq9JI1CF"/>
    </div>
    
    <!-- Hidden Copyright for Context (Shell Rule) -->
    <footer class="mt-8">
        <p class="font-inter text-[10px] uppercase tracking-widest font-semibold text-slate-400">
            © 2024 Indigo Clinical. Precision Sanctuary.
        </p>
    </footer>
</div>
