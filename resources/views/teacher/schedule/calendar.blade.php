@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <header class="flex flex-col sm:flex-row justify-between items-center bg-white text-gray-700 px-4 sm:px-6 py-4 rounded-lg shadow-md space-y-2 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <i class="fa-solid fa-calendar-day text-lg"></i>
            <span class="text-sm font-semibold">Today's Schedule</span>
            <span class="text-sm">{{ now()->format('l, F j, Y') }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Current Time: <span id="current-time">{{ $currentTime }}</span></span>
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>
        </div>
    </header>

    <div class="flex justify-between items-center my-6">
        <a href="?month={{ $currentMonth }}&year={{ $currentYear - 1 }}&room_id={{ $currentRoom }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
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

            <select id="room-select" class="p-2 border rounded bg-white">
                <option value="All" {{ $currentRoom == 'All' ? 'selected' : '' }}>All Rooms</option>
                @foreach($rooms as $room)
                <option value="{{ $room->id }}" {{ $room->id == $currentRoom ? 'selected' : '' }}>
                    {{ $room->room_name }}
                </option>
                @endforeach
            </select>
        </div>

        <a href="?month={{ $currentMonth }}&year={{ $currentYear + 1 }}&room_id={{ $currentRoom }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Next Year ▶
        </a>
    </div>
    <div class="flex justify-center space-x-4 mb-2">
        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full bg-green-500 mr-1"></div>
            <span class="text-xs">Done</span>
        </div>
        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full bg-blue-500 mr-1"></div>
            <span class="text-xs">Ongoing</span>
        </div>
        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full bg-yellow-500 mr-1"></div>
            <span class="text-xs">Upcoming</span>
        </div>
        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full bg-purple-500 mr-1"></div>
            <span class="text-xs">Reservation (Pending)</span>
        </div>
    </div>
    <div class="grid grid-cols-7 bg-gray-100 mb-1 rounded-t-lg">
        @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
        <div class="p-2 text-center font-semibold">
            {{ substr($day, 0, 3) }}
        </div>
        @endforeach
    </div>

    @foreach($weeks as $week)
    <div class="grid grid-cols-7 gap-px bg-gray-200 mb-1">
        @foreach($week['days'] as $day)
        <div
            class="bg-white min-h-[10.5rem] p-1 {{ $day['is_today'] ? 'border-2 border-blue-400' : '' }} {{ !$day['in_month'] ? 'bg-gray-50' : '' }} cursor-pointer hover:bg-gray-50"
            onclick="openDayModal('{{ $day['date'] }}', '{{ Carbon::parse($day['date'])->format('l, F j, Y') }}')">
            <div class="text-right font-semibold text-sm mb-1 {{ !$day['in_month'] ? 'text-gray-400' : '' }}">
                {{ $day['day_number'] }}
            </div>

            @if($day['in_month'])
            @php
            $daySchedules = $weeklySchedules[$day['day_name']] ?? [];
            // <--- CHANGE IS HERE: Use $day['date'] to fetch reservations for this specific date
                $dayReservations=$reservations[$day['date']] ?? [];
                $now=now();

                // Combine and sort by start time
                $events=collect($daySchedules)->map(function($event) use ($day) {
                $event->type = 'weekly_schedule';
                $event->start_time_carbon = Carbon::parse($day['date'] . ' ' . $event->start_time);
                $event->end_time_carbon = Carbon::parse($day['date'] . ' ' . $event->end_time);
                return $event;
                })->merge(collect($dayReservations)->map(function($event) use ($day) {
                $event->type = 'reservation';
                $event->start_time_carbon = Carbon::parse($day['date'] . ' ' . $event->start_time);
                $event->end_time_carbon = Carbon::parse($day['date'] . ' ' . $event->end_time);
                return $event;
                }))->sortBy('start_time_carbon');

                @endphp

                @if(count($events) > 0)
                <div class="space-y-1 max-h-full overflow-y-auto text-xs">
                    @foreach($events as $event)
                    @php
                    $statusClass = '';
                    $borderColor = '';
                    $bgColor = '';
                    $textColor = '';
                    $subtitle = '';
                    $displayRoomName = '';

                    if ($event->type === 'weekly_schedule') {
                    $status = $now->gt($event->end_time_carbon) ? 'done' : ($now->between($event->start_time_carbon, $event->end_time_carbon) ? 'ongoing' : 'upcoming');
                    if($status === 'done') { $statusClass = 'bg-green-500'; $borderColor = 'border-green-100'; $bgColor = 'bg-green-50'; $textColor = 'text-green-700'; }
                    elseif($status === 'ongoing') { $statusClass = 'bg-blue-500'; $borderColor = 'border-blue-100'; $bgColor = 'bg-blue-50'; $textColor = 'text-blue-700'; }
                    else { $statusClass = 'bg-yellow-500'; $borderColor = 'border-yellow-100'; $bgColor = 'bg-yellow-50'; $textColor = 'text-yellow-700'; }
                    $subtitle = $event->room->subject ?? 'General';
                    $displayRoomName = $event->room->room_name;
                    } elseif ($event->type === 'reservation') {
                    // For reservations, they are always 'pending' from the teacher's view initially
                    $statusClass = 'bg-purple-500'; // A new color for reservations
                    $borderColor = 'border-purple-100';
                    $bgColor = 'bg-purple-50';
                    $textColor = 'text-purple-700';
                    $subtitle = 'Reservation (Pending)'; // Clearly state it's a reservation and pending
                    $displayRoomName = $event->room->room_name;
                    }
                    @endphp
                    <div class="p-1 rounded {{ $bgColor }} border {{ $borderColor }} mb-1">
                        <div class="flex items-start">
                            <span class="w-2 h-2 rounded-full mt-1 mr-1 flex-shrink-0 {{ $statusClass }}">
                            </span>
                            <div class="flex-grow min-w-0">
                                <div class="font-medium {{ $textColor }} truncate">
                                    {{ $displayRoomName }}
                                </div>
                                <div class="text-gray-600 truncate">
                                    {{ $event->start_time_carbon->format('g:i') }}-{{ $event->end_time_carbon->format('g:i A') }}
                                </div>
                                <div class="text-gray-500 truncate text-xs">
                                    {{ $event->subject ?? $subtitle }}
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateCalendar() {
            const month = document.getElementById('month-select').value;
            const year = document.getElementById('year-select').value;
            const room_id = document.getElementById('room-select').value;
            window.location.href = `?month=${month}&year=${year}&room_id=${room_id}`;
        }

        // Handle month/year/room selection changes
        document.getElementById('month-select').addEventListener('change', updateCalendar);
        document.getElementById('year-select').addEventListener('change', updateCalendar);
        document.getElementById('room-select').addEventListener('change', updateCalendar);


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