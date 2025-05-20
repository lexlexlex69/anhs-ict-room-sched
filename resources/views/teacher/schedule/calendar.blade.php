@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <!-- Top Bar -->
    <header class="flex flex-col sm:flex-row justify-between items-center bg-white text-gray-700 px-4 sm:px-6 py-4 rounded-lg shadow-md space-y-2 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <i class="fa-solid fa-calendar-day text-lg"></i>
            <span class="text-sm font-semibold">Today's Schedule</span>
            <span class="text-sm">{{ now()->format('l, F j, Y') }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Current Time: {{ $currentTime }}</span>
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>
        </div>
    </header>

    <!-- Month Navigation -->
    <div class="flex justify-between items-center my-6">
        <a href="?month={{ $currentMonth }}&year={{ $currentYear - 1 }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            ◀ Previous Year
        </a>

        <div class="flex items-center space-x-4">
            <select id="month-select" class="p-2 border rounded bg-white">
                @foreach(range(1, 12) as $month)
                <option value="{{ $month }}" {{ $month == $currentMonth ? 'selected' : '' }}>
                    {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                </option>
                @endforeach
            </select>

            <select id="year-select" class="p-2 border rounded bg-white">
                @foreach(range(date('Y') - 2, date('Y') + 2) as $year)
                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                    {{ $year }}
                </option>
                @endforeach
            </select>
        </div>

        <a href="?month={{ $currentMonth }}&year={{ $currentYear + 1 }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Next Year ▶
        </a>
    </div>

    <!-- Day Headers -->
    <div class="grid grid-cols-7 bg-gray-100 mb-1 rounded-t-lg">
        @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
        <div class="p-2 text-center font-semibold">
            {{ substr($day, 0, 3) }}
        </div>
        @endforeach
    </div>

    <!-- Calendar Grid -->
    @foreach($weeks as $week)
    <div class="grid grid-cols-7 gap-px bg-gray-200 mb-1">
        @foreach($week['days'] as $day)
        <div
            class="bg-white min-h-[10.5rem] p-1 {{ $day['is_today'] ? 'border-2 border-blue-400' : '' }} {{ !$day['in_month'] ? 'bg-gray-50' : '' }} cursor-pointer hover:bg-gray-50"
            onclick="openDayModal('{{ $day['date'] }}', '{{ Carbon::parse($day['date'])->format('l, F j, Y') }}')">
            <!-- Date Number -->
            <div class="text-right font-semibold text-sm mb-1 {{ !$day['in_month'] ? 'text-gray-400' : '' }}">
                {{ $day['day_number'] }}
            </div>

            @if($day['in_month'])
            @php
            $daySchedules = $weeklySchedules[$day['day_name']] ?? [];
            $now = now();
            @endphp

            @if(count($daySchedules) > 0)
            <div class="space-y-1  max-h-full overflow-y-auto text-xs">
                @foreach($daySchedules as $schedule)
                @php
                $start = Carbon::parse($day['date'] . ' ' . $schedule->start_time);
                $end = Carbon::parse($day['date'] . ' ' . $schedule->end_time);
                $status = $now->gt($end) ? 'done' : ($now->between($start, $end) ? 'ongoing' : 'upcoming');
                @endphp
                <div class="p-1 rounded bg-green-50 border border-green-100 mb-1">
                    <div class="flex items-start">
                        <span class="w-2 h-2 rounded-full mt-1 mr-1 flex-shrink-0
                    @if($status === 'done') bg-green-500 @elseif($status === 'ongoing') bg-blue-500 @else bg-yellow-500 @endif">
                        </span>
                        <div class="flex-grow min-w-0">
                            <div class="font-medium text-green-700 truncate">
                                {{ $schedule->room->room_name }}
                            </div>
                            <div class="text-gray-600 truncate">
                                {{ Carbon::parse($schedule->start_time)->format('g:i') }}-{{ Carbon::parse($schedule->end_time)->format('g:i A') }}
                            </div>
                            <div class="text-gray-500 truncate text-xs">
                                {{ $schedule->room->subject ?? 'General' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            @endif
        </div>
        @endforeach
    </div>
    @endforeach
</main>

<!-- JavaScript for Calendar -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle month/year selection changes
        document.getElementById('month-select').addEventListener('change', function() {
            const month = this.value;
            const year = document.getElementById('year-select').value;
            window.location.href = `?month=${month}&year=${year}`;
        });

        document.getElementById('year-select').addEventListener('change', function() {
            const year = this.value;
            const month = document.getElementById('month-select').value;
            window.location.href = `?month=${month}&year=${year}`;
        });

        // Auto-update time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent =
                now.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
        }

        setInterval(updateTime, 60000); // Update every minute
        updateTime(); // Initial call
    });
</script>

@endsection