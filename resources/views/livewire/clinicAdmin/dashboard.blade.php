<div class="flex h-screen bg-[#F8FAFC] dark:bg-gray-950 font-sans text-gray-900">
    <!-- Sidebar -->
    <livewire:common.sidebar />

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Header -->
        <livewire:common.header />

        <!-- Dashboard Content -->
        <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
            <div class="max-w-7xl mx-auto">
                <!-- Welcome Section -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Clinic Overview</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Welcome back, {{ auth()->user()->name }}. Here's what's happening at your facility today.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Patients -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center">
                                    <x-icon name="users" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                </div>
                                <span class="text-sm font-medium text-green-500 flex items-center gap-1">+2.4% <x-icon name="trending-up" class="w-3 h-3" /></span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Patients</p>
                            <h3 class="text-2xl font-bold dark:text-white">{{ number_format($stats['total_patients']) }}</h3>
                        </div>
                    </div>

                    <!-- Today's Appointments -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                                <x-icon name="calendar" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <span class="text-xs text-gray-400">Daily Target: 100</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Today's Appointments</p>
                        <h3 class="text-2xl font-bold dark:text-white">{{ $stats['today_appointments'] }}</h3>
                        <div class="mt-4 h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-600" style="width: {{ min(($stats['today_appointments'] / 100) * 100, 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Revenue Today -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                                <x-icon name="banknote" class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <span class="text-sm font-medium text-green-500 flex items-center gap-1">+12% <x-icon name="trending-up" class="w-3 h-3" /></span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Revenue Today</p>
                        <h3 class="text-2xl font-bold dark:text-white">${{ number_format($stats['revenue_today']) }}</h3>
                    </div>

                    <!-- Active Doctors -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                                <x-icon name="stethoscope" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                            </div>
                            <span class="text-xs text-gray-400">On Shift</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Active Doctors</p>
                        <h3 class="text-2xl font-bold dark:text-white">{{ $stats['active_doctors'] }}/{{ $stats['doctor_total'] }}</h3>
                    </div>
                </div>

                <!-- Main Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Appointment Trends Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-xl font-bold dark:text-white">Appointment Trends</h2>
                            <div class="flex gap-2 p-1 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <button class="px-4 py-1.5 text-xs font-semibold rounded-md bg-white dark:bg-gray-700 shadow-sm text-blue-600 dark:text-blue-400">Weekly</button>
                                <button class="px-4 py-1.5 text-xs font-semibold rounded-md text-gray-500">Monthly</button>
                            </div>
                        </div>
                        <div id="appointmentTrendsChart" class="w-full h-80"></div>
                    </div>

                    <!-- Live Clinic Queue -->
                    <div class="bg-indigo-950 dark:bg-indigo-950 p-8 rounded-3xl shadow-xl text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        </div>
                        <h2 class="text-xl font-bold mb-8 flex items-center gap-2">
                            Live Clinic Queue
                        </h2>

                        <div class="bg-indigo-900/50 rounded-2xl p-6 mb-8 border border-white/10">
                            <p class="text-xs text-indigo-300 font-medium uppercase tracking-wider mb-2">Now Serving</p>
                            <h3 class="text-3xl font-black mb-2">
                                @if($liveQueue['serving'])
                                    Token #{{ $liveQueue['serving']->token_number }}
                                @else
                                    No tokens
                                @endif
                            </h3>
                            <p class="text-sm text-indigo-200">
                                @if($liveQueue['serving'])
                                    Room {{ rand(1, 20) }} • {{ $liveQueue['serving']->appointment->doctor->user->name }}
                                @else
                                    Queue is empty
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-4 mb-8">
                            <div>
                                <p class="text-xs text-indigo-300 font-medium uppercase tracking-wider mb-1">Next in line</p>
                                <h4 class="text-xl font-bold">
                                    @if($liveQueue['next'])
                                        #{{ $liveQueue['next']->token_number }}
                                    @else
                                        -
                                    @endif
                                </h4>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-indigo-800 flex items-center justify-center">
                                <x-icon name="arrow-right" class="w-4 h-4 text-white" />
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-indigo-300 font-medium uppercase tracking-wider mb-1">Waiting</p>
                                <h4 class="text-xl font-bold">{{ $liveQueue['waiting_count'] }} Patients</h4>
                            </div>
                        </div>

                        <button class="w-full py-4 bg-teal-400 hover:bg-teal-300 transition-colors text-indigo-950 font-bold rounded-2xl">
                            Manage Queue
                        </button>
                    </div>

                    <!-- Recent Appointments -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between mb-8">
                            <h2 class="text-xl font-bold dark:text-white">Recent Appointments</h2>
                            <button class="text-sm font-semibold text-blue-600 dark:text-blue-400">View All</button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left border-b border-gray-50 dark:border-gray-800">
                                        <th class="pb-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Patient Name</th>
                                        <th class="pb-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Doctor</th>
                                        <th class="pb-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Time</th>
                                        <th class="pb-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach($recentAppointments as $appointment)
                                        <tr>
                                            <td class="py-5">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold">
                                                        {{ strtoupper(substr($appointment->patient->name, 0, 2)) }}
                                                    </div>
                                                    <span class="font-semibold dark:text-white">{{ $appointment->patient->name }}</span>
                                                </div>
                                            </td>
                                            <td class="py-5 text-gray-600 dark:text-gray-400 font-medium">
                                                {{ $appointment->doctor->user->name }}
                                            </td>
                                            <td class="py-5">
                                                <div class="text-sm font-semibold dark:text-white">{{ \Carbon\Carbon::parse($appointment->start_time)->format('h:i A') }}</div>
                                            </td>
                                            <td class="py-5">
                                                @php
                                                    $statusClasses = [
                                                        'completed' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                                                        'confirmed' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                                                        'pending' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400',
                                                        'cancelled' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
                                                    ];
                                                    $class = $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-600';
                                                @endphp
                                                <span class="px-4 py-1.5 rounded-full text-xs font-bold capitalize {{ $class }}">
                                                    {{ $appointment->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Revenue Mix & Efficiency -->
                    <div class="bg-white dark:bg-gray-900 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800">
                        <h2 class="text-xl font-bold mb-8 dark:text-white">Revenue Mix</h2>
                        <div class="space-y-8 mb-12">
                            @foreach($revenueMix as $item)
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $item['label'] }}</span>
                                        <span class="text-sm font-bold dark:text-white">${{ number_format($item['amount']) }}</span>
                                    </div>
                                    <div class="h-2 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                        <div class="h-full {{ $loop->first ? 'bg-indigo-600' : ($loop->index == 1 ? 'bg-teal-500' : 'bg-purple-500') }}" style="width: {{ $item['percentage'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-8 border-t border-gray-50 dark:border-gray-800 flex flex-col items-center">
                            <div class="relative w-40 h-40 flex items-center justify-center">
                                <svg class="w-full h-full transform -rotate-90">
                                    <circle cx="80" cy="80" r="70" fill="none" stroke="currentColor" stroke-width="12" class="text-gray-100 dark:text-gray-800" />
                                    <circle cx="80" cy="80" r="70" fill="none" stroke="currentColor" stroke-width="12" stroke-dasharray="440" stroke-dashoffset="{{ 440 - (440 * $efficiencyScore / 100) }}" class="text-indigo-600" />
                                </svg>
                                <div class="absolute flex flex-col items-center">
                                    <span class="text-sm text-gray-400 font-medium">Efficiency Score</span>
                                    <span class="text-3xl font-black dark:text-white">{{ $efficiencyScore }}%</span>
                                    <span class="text-[10px] text-green-500 font-bold">vs. 88.5% last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts for Charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
            const options = {
                series: [{
                    name: 'Appointments',
                    data: @json($chartData['data'])
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    zoom: { enabled: false },
                    fontFamily: 'Inter, sans-serif'
                },
                colors: ['#4F46E5'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100, 100, 100]
                    }
                },
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                xaxis: {
                    categories: @json($chartData['labels']),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#94A3B8',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    show: false
                },
                grid: {
                    borderColor: '#F1F5F9',
                    strokeDashArray: 4,
                    padding: { left: 0, right: 0 }
                },
                markers: {
                    size: 0,
                    hover: { size: 6 }
                },
                tooltip: {
                    x: { show: false },
                    theme: 'light',
                }
            };

            const chart = new ApexCharts(document.querySelector("#appointmentTrendsChart"), options);
            chart.render();
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #E2E8F0;
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #1E293B;
        }
    </style>
</div>
