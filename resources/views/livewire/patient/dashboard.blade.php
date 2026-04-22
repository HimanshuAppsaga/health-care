<div class="p-6 md:p-8 space-y-8">
    <!-- Greeting & Quick Action Hero -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 relative overflow-hidden bg-primary-container rounded-3xl p-8 text-white flex flex-col justify-between min-h-[220px]">
            <div class="relative z-10">
                <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                    @php
                        $hour = date('H');
                        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
                    @endphp
                    {{ $greeting }}, {{ auth()->user()->name }}
                </h1>
                <p class="mt-2 text-primary-fixed/80 max-w-md font-medium">
                    @if($nextAppointment)
                        You have an appointment coming up soon. Your blood pressure readings are improving. Keep up the great work!
                    @else
                        You have no appointments scheduled for today. Take some time to review your health progress!
                    @endif
                </p>
            </div>
            <div class="relative z-10 flex gap-4 mt-6">
                @if($nextAppointment)
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-2 rounded-full border border-white/20">
                        <span class="material-symbols-outlined text-secondary-container">calendar_today</span>
                        <span class="text-xs font-semibold">Next: Dr. {{ $nextAppointment->doctor->user->name }} ({{ \Carbon\Carbon::parse($nextAppointment->start_time)->format('g:i A') }})</span>
                    </div>
                @endif
            </div>
            <!-- Abstract Decorative Elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-violet-400/20 rounded-full blur-3xl -mr-20 -mt-20"></div>
            <div class="absolute bottom-0 right-0 w-48 h-48 bg-teal-400/10 rounded-full blur-2xl mr-10 mb-10"></div>
        </div>
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

        <!-- Recent Prescriptions -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-outline-variant/30">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary">pill</span>
                    Recent Prescriptions
                </h2>
                <span class="material-symbols-outlined text-gray-400 cursor-pointer">more_horiz</span>
            </div>
            <div class="space-y-3">
                @forelse($recentPrescriptions as $prescription)
                    @foreach($prescription->items as $item)
                        <div class="bg-surface-container-low p-4 rounded-2xl border-l-4 {{ $loop->even ? 'border-secondary' : 'border-primary-container' }}">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-sm font-extrabold">{{ $item->medicine_name }}</h4>
                                    <p class="text-[11px] text-gray-500">{{ $item->dosage }} • {{ $item->duration }}</p>
                                </div>
                                <span class="bg-primary-fixed text-primary text-[10px] px-2 py-0.5 rounded-full font-bold">Active</span>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">No recent prescriptions</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Alerts Panel -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-outline-variant/30 lg:row-span-2">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-error">notifications_active</span>
                    Recent Alerts
                </h2>
            </div>
            <div class="space-y-4">
                @forelse($notifications as $notification)
                    <div class="flex gap-4 p-4 rounded-2xl {{ $notification->read_at ? 'bg-surface-container' : 'bg-primary-fixed/30 border border-primary-container/10' }}">
                        <div class="w-10 h-10 rounded-full {{ $notification->read_at ? 'bg-gray-200' : 'bg-primary-container/10' }} flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined {{ $notification->read_at ? 'text-gray-500' : 'text-primary-container' }}">{{ $notification->type == 'lab' ? 'biotech' : 'assignment_turned_in' }}</span>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">{{ $notification->title }}</h4>
                            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $notification->message }}</p>
                            @if(!$notification->read_at)
                                <span class="text-[10px] font-bold text-primary-container mt-2 block uppercase tracking-tighter">New</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">No new alerts</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Your Progress Section -->
        <div class="md:col-span-2 bg-white rounded-3xl p-8 shadow-sm border border-outline-variant/30">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-xl font-bold">Your Progress Summary</h2>
                    <p class="text-sm text-gray-500">Tracking your vitals over the last 30 days</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded-xl bg-surface-container text-xs font-bold hover:bg-surface-container-high transition-colors">Daily</button>
                    <button class="px-4 py-2 rounded-xl bg-primary-container text-white text-xs font-bold">Monthly</button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                <div class="flex flex-col items-center p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/20">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Heart Rate</span>
                    <div class="flex items-end gap-1 mb-4">
                        <span class="text-3xl font-black text-on-surface">{{ $vitals['heart_rate'] }}</span>
                        <span class="text-xs text-gray-500 font-bold mb-1">BPM</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-error" style="width: {{ $vitals['heart_rate'] }}%"></div>
                    </div>
                </div>
                <div class="flex flex-col items-center p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/20">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Weight</span>
                    <div class="flex items-end gap-1 mb-4">
                        <span class="text-3xl font-black text-on-surface">{{ $vitals['weight'] }}</span>
                        <span class="text-xs text-gray-500 font-bold mb-1">LBS</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-secondary" style="width: 85%"></div>
                    </div>
                </div>
                <div class="flex flex-col items-center p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/20">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Sleep Avg</span>
                    <div class="flex items-end gap-1 mb-4">
                        <span class="text-3xl font-black text-on-surface">{{ $vitals['sleep_avg'] }}</span>
                        <span class="text-xs text-gray-500 font-bold mb-1">HRS</span>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-container" style="width: 65%"></div>
                    </div>
                </div>
            </div>
            <div class="mt-8 p-4 bg-secondary-container/10 border border-secondary/10 rounded-2xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-secondary-container flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-on-secondary-container">trending_up</span>
                </div>
                <p class="text-sm text-on-secondary-container font-medium">
                    <span class="font-bold">Insight:</span> Your resting heart rate has decreased by <span class="font-bold">{{ abs($vitals['heart_rate_change']) }}%</span> since last month. This correlates with your increased morning walks.
                </p>
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
