@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <!-- Top Bar -->
    <header class="flex justify-between items-center bg-white text-gray px-6 py-4 rounded-lg shadow-md">
        <div class="flex items-center space-x-5">
            <i class="fa-solid fa-calendar-alt text-lg"></i>
            <span class="text-sm">{{ Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}</span>
            <span id="current-time" class="text-sm font-semibold">{{ now()->format('g:i A') }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Admin View</span>
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
        <div class="bg-white min-h-40 p-1 {{ $day['is_today'] ? 'border-2 border-blue-400' : '' }} {{ !$day['in_month'] ? 'bg-gray-50' : '' }}">
            <!-- Date Number -->
            <div class="text-right font-semibold text-sm mb-1 {{ !$day['in_month'] ? 'text-gray-400' : '' }}">
                {{ Carbon::parse($day['date'])->format('j') }}
            </div>

            @if($day['in_month'])
            <!-- Weekly Schedules (Teachers - Green) -->
            @php
            $daySchedules = $weeklySchedules[$day['day_name']] ?? [];
            @endphp

            @foreach($daySchedules as $schedule)
            <div class="text-xs p-1 rounded bg-green-50 border border-green-100 mb-1">
                <div class="font-medium truncate text-green-700">
                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                    {{ $schedule->teacher->first_name }} {{ $schedule->teacher->last_name }}
                </div>
                <div class="text-xs">
                    {{ Carbon::parse($schedule->start_time)->format('g:i A') }} -
                    {{ Carbon::parse($schedule->end_time)->format('g:i A') }}
                </div>
                <div class="text-xs truncate">
                    {{ $schedule->room->room_name }}
                </div>
            </div>
            @endforeach

            <!-- Reservations (Reservees - Blue) -->
            @if(isset($reservations[$day['date']]))
            @foreach($reservations[$day['date']] as $reservation)
            <div class="text-xs p-1 rounded bg-blue-50 border border-blue-100 mb-1">
                <div class="font-medium truncate text-blue-700">
                    <i class="fas fa-user mr-1"></i>
                    {{ $reservation->teacher_name }}
                </div>
                <div class="text-xs">
                    {{ Carbon::parse($reservation->start_time)->format('g:i A') }} -
                    {{ Carbon::parse($reservation->end_time)->format('g:i A') }}
                </div>
                <div class="text-xs truncate">
                    {{ $reservation->room->room_name }}
                </div>
                @if($reservation->reference_number)
                <div class="text-xs text-gray-500 truncate">
                    Ref: {{ $reservation->reference_number }}
                </div>
                @endif
            </div>
            @endforeach
            @endif
            @endif
        </div>
        @endforeach
    </div>
    @endforeach

    <!-- Legend -->
    <div class="mt-4 flex justify-center space-x-4">
        <div class="flex items-center">
            <div class="w-4 h-4 bg-green-100 border border-green-200 mr-2"></div>
            <span class="text-xs">Teacher Schedule</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-blue-100 border border-blue-200 mr-2"></div>
            <span class="text-xs">Reservation</span>
        </div>
    </div>
</main>

<!-- JavaScript -->
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

        setInterval(updateTime, 60000);
        updateTime();
    });
</script>

@endsection