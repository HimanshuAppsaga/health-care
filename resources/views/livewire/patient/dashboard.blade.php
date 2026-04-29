<div class="p-6 md:p-8 space-y-8">
    <!-- Greeting & Quick Action Hero -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-outline-variant/30 flex flex-col items-center justify-center text-center group cursor-pointer hover:shadow-xl transition-all duration-300">
            <div class="w-16 h-16 bg-violet-50 rounded-2xl flex items-center justify-center text-primary-container mb-4 group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-3xl">add_circle</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Book Appointment</h3>
            <p class="text-sm text-gray-500 mt-2">Schedule your next check-up with your preferred specialist.</p>
            <button class="mt-6 px-6 py-2.5 bg-primary-container text-white font-bold rounded-xl hover:shadow-lg hover:shadow-primary-container/30 transition-all">Start Booking</button>
        </div>
    </section>

    <!-- Dashboard Grid -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Upcoming Appointments -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-outline-variant/30 flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">event</span>
                    Upcoming Appointments
                </h2>
                <span class="text-xs font-bold text-primary-container cursor-pointer hover:underline">View All</span>
            </div>
            <div class="space-y-4">
                @forelse($upcomingAppointments as $appointment)
                    <div class="flex items-center gap-4 p-3 rounded-2xl hover:bg-gray-50 transition-colors border border-transparent hover:border-outline-variant/20">
                        <img class="w-12 h-12 rounded-xl object-cover" src="{{ $appointment->doctor->user->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode($appointment->doctor->user->name).'&background=E8DDFF&color=21005D' }}"/>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold">Dr. {{ $appointment->doctor->user->name }}</h4>
                            <p class="text-[11px] text-gray-500">{{ $appointment->doctor->specialization }} • {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M, H:i') }}</p>
                        </div>
                        <span class="material-symbols-outlined text-gray-400 text-sm">chevron_right</span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">No upcoming appointments</p>
                    </div>
                @endforelse
            </div>
        </div>

    </section>

    <!-- Mobile Navigation (visible on mobile only) -->
    <nav class="md:hidden flex justify-around items-center py-3 px-6 bg-white border-t border-gray-200 fixed bottom-0 w-full z-50">
        <button class="flex flex-col items-center gap-1 text-violet-700">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">dashboard</span>
            <span class="text-[10px] font-bold">Home</span>
        </button>
        <button class="flex flex-col items-center gap-1 text-gray-400">
            <span class="material-symbols-outlined">calendar_month</span>
            <span class="text-[10px] font-bold">Book</span>
        </button>
        <button class="flex flex-col items-center gap-1 text-gray-400">
            <span class="material-symbols-outlined">chat_bubble</span>
            <span class="text-[10px] font-bold">Messages</span>
        </button>
        <button class="flex flex-col items-center gap-1 text-gray-400">
            <span class="material-symbols-outlined">account_circle</span>
            <span class="text-[10px] font-bold">Profile</span>
        </button>
    </nav>
</div>
