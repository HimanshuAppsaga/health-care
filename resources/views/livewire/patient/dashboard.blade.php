<div class="p-4 sm:p-8 max-w-4xl mx-auto font-manrope">
    <!-- Main Queue Card -->
    <div class="bg-surface rounded-[2rem] shadow-clinical border border-outline-variant overflow-hidden">
        <!-- Card Header -->
        <div class="p-6 sm:p-8 border-b border-outline-variant flex items-center bg-surface-container-low">
            <h2 class="text-xl font-black flex items-center gap-3 text-on-surface">
                <span class="w-3 h-3 rounded-full bg-[#f87171] animate-pulse"></span>
                Live Queue Manager
            </h2>
        </div>

        <!-- Card Body -->
        <div class="p-6 sm:p-12 md:p-16 flex flex-col items-center justify-center text-center relative overflow-hidden">
            <!-- Glow background effect -->
            <div class="absolute -inset-10 bg-primary/5 blur-3xl rounded-full pointer-events-none"></div>

            <p class="text-sm font-black text-outline uppercase tracking-widest mb-4 relative z-10">Now Serving</p>
            
            <div class="relative mb-6 z-10">
                <div class="text-8xl sm:text-9xl font-black text-[#005bb0] tracking-tighter leading-none select-none">
                    {{ $nowServing ? $nowServing->token_number : '— —' }}
                </div>
            </div>

            <h4 class="text-xl sm:text-2xl font-bold text-on-background mb-10 relative z-10">
                {{ $nowServing ? ($nowServing->appointment->name ?? 'Unknown Patient') : 'No Patient Assigned' }}
            </h4>

            <div class="flex flex-wrap items-center justify-center gap-3 relative z-10">
                <p class="text-xs sm:text-sm font-bold text-outline uppercase tracking-widest w-full sm:w-auto mb-2 sm:mb-0">Next Tokens:</p>
                @forelse($nextTokens as $token)
                    <span class="px-5 py-2 bg-surface-container text-[#005bb0] font-black rounded-full text-sm sm:text-base border border-outline-variant/30 transition-all hover:scale-105">
                        {{ $token->token_number }}
                    </span>
                @empty
                    <span class="px-5 py-2 bg-surface-container text-outline font-black rounded-full text-sm sm:text-base border border-outline-variant/30">
                        None
                    </span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Patient's Personal Token Helper Card -->
    @if($appointment)
        <div class="mt-8 bg-primary-container/10 rounded-[2rem] border border-primary/20 p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
            <div class="flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
                <div class="w-16 h-16 bg-[#005bb0] text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-lg shadow-[#005bb0]/25 shrink-0">
                    {{ $appointment->token ?? '—' }}
                </div>
                <div>
                    <h3 class="text-lg font-black text-on-background">Your Token Number</h3>
                    <p class="text-sm text-outline font-medium">Consulting Doctor: <strong class="text-on-background">Dr. {{ $appointment->doctor->user->name ?? 'Staff' }}</strong></p>
                </div>
            </div>
            <div>
                @php
                    $statusClasses = [
                        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    ];
                    $statusVal = $appointment->status->value;
                    $statusClass = $statusClasses[$statusVal] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                @endphp
                <span class="px-5 py-2.5 border rounded-full text-xs font-black uppercase tracking-widest {{ $statusClass }}">
                    Status: {{ ucfirst($statusVal) }}
                </span>
            </div>
        </div>
    @endif
</div>
