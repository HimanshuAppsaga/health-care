<div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 mt-6 px-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:border-[#5200cc]/30 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Today's Appointments</p>
                <h3 class="text-3xl font-black text-[#5200cc]">{{ $totalAppointments }}</h3>
            </div>
            <div class="w-14 h-14 bg-[#ede7ff] rounded-2xl flex items-center justify-center text-[#5200cc]">
                <span class="material-symbols-outlined text-3xl">calendar_today</span>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-[#0fbda6]/30 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Pending Patients</p>
                <h3 class="text-3xl font-black text-[#0fbda6]">{{ $pendingPatients }}</h3>
            </div>
            <div class="w-14 h-14 bg-[#e6fffb] rounded-2xl flex items-center justify-center text-[#0fbda6]">
                <span class="material-symbols-outlined text-3xl">pending_actions</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between hover:border-orange-200 transition-all">
            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Completed</p>
                <h3 class="text-3xl font-black text-orange-500">{{ $completedToday }}</h3>
            </div>
            <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500">
                <span class="material-symbols-outlined text-3xl">task_alt</span>
            </div>
        </div>
    </div>

    <div class="px-8 pb-12">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-[#1c1b1b]">Patient Schedule</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Patient Name</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Token</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($todaysAppointments as $appointment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#5200cc]">{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</td>
                            <td class="px-6 py-4 font-medium text-[#1c1b1b]">{{ $appointment->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 text-gray-600 font-bold">{{ $appointment->token ?? '--' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-bold rounded-full 
                                    @if($appointment->status === 'completed') bg-gray-100 text-gray-700 
                                    @elseif($appointment->status === 'cancelled') bg-red-100 text-red-700
                                    @else bg-green-100 text-green-700 @endif">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-sm font-bold text-[#5200cc] hover:underline">View Details</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 font-medium">No appointments for today.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
