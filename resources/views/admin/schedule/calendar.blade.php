@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold flex flex-col" style="height: calc(100vh - 8rem);">
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
    <div class="flex justify-between items-center my-4">
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

    <!-- Legend - Moved Above Calendar -->
    <div class="flex justify-center space-x-4 mb-2">
        <div class="flex items-center">
            <div class="w-4 h-4 bg-green-100 border border-green-200 mr-2"></div>
            <span class="text-xs">Teacher Schedule</span>
        </div>
        <div class="flex items-center">
            <div class="w-4 h-4 bg-blue-100 border border-blue-200 mr-2"></div>
            <span class="text-xs">Reservation</span>
        </div>
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
    </div>

    <!-- Calendar Container with Scroll -->
    <div class="flex-grow overflow-auto">
        <!-- Day Headers -->
        <div class="grid grid-cols-7 bg-gray-100 sticky top-0 z-10">
            @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
            <div class="p-2 text-center font-semibold">
                {{ substr($day, 0, 3) }}
            </div>
            @endforeach
        </div>

        <!-- Calendar Grid -->
        <div class="grid auto-rows-min gap-px bg-gray-200">
            @foreach($weeks as $week)
            <div class="grid grid-cols-7">
                @foreach($week['days'] as $day)
                <div
                    class="bg-white min-h-[10.5rem] p-1 {{ $day['is_today'] ? 'border-2 border-blue-400' : '' }} {{ !$day['in_month'] ? 'bg-gray-50' : '' }} cursor-pointer hover:bg-gray-50"
                    onclick="openDayModal('{{ $day['date'] }}', '{{ Carbon::parse($day['date'])->format('l, F j, Y') }}')">
                    <!-- Date Number -->
                    <div class="text-right font-semibold text-sm mb-1 {{ !$day['in_month'] ? 'text-gray-400' : '' }}">
                        {{ Carbon::parse($day['date'])->format('j') }}
                    </div>

                    @if($day['in_month'])
                    <!-- Compact Schedule Display -->
                    @php
                    $daySchedules = $weeklySchedules[$day['day_name']] ?? [];
                    $dayReservations = $reservations[$day['date']] ?? [];
                    $totalItems = count($daySchedules) + count($dayReservations);
                    $now = now();
                    @endphp

                    @if($totalItems > 0)
                    <div class="space-y-1 max-h-full overflow-y-auto">
                        @foreach($daySchedules as $schedule)
                        @php
                        $start = Carbon::parse($day['date'] . ' ' . $schedule->start_time);
                        $end = Carbon::parse($day['date'] . ' ' . $schedule->end_time);
                        $status = $now->gt($end) ? 'done' : ($now->between($start, $end) ? 'ongoing' : 'upcoming');
                        @endphp
                        <div class="text-xs p-1 rounded bg-green-50 border border-green-100 mb-1 truncate flex items-center">
                            <span class="w-2 h-2 rounded-full mr-1 
                    @if($status === 'done') bg-green-500 @elseif($status === 'ongoing') bg-blue-500 @else bg-yellow-500 @endif">
                            </span>
                            <span class="text-green-700 font-medium">
                                {{ $schedule->teacher->first_name }} ({{ $schedule->room->room_name }})
                            </span>
                        </div>
                        @endforeach

                        @foreach($dayReservations as $reservation)
                        @php
                        $start = Carbon::parse($reservation->date . ' ' . $reservation->start_time);
                        $end = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
                        $status = $now->gt($end) ? 'done' : ($now->between($start, $end) ? 'ongoing' : 'upcoming');
                        @endphp
                        <div class="text-xs p-1 rounded bg-blue-50 border border-blue-100 mb-1 truncate flex items-center">
                            <span class="w-2 h-2 rounded-full mr-1 
                    @if($status === 'done') bg-green-500 @elseif($status === 'ongoing') bg-blue-500 @else bg-yellow-500 @endif">
                            </span>
                            <span class="text-blue-700 font-medium">
                                {{ $reservation->teacher_name }} ({{ $reservation->room->room_name }})
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</main>

<!-- Day Detail Modal -->
<div id="dayModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center border-b px-6 py-4">
            <h3 id="modalTitle" class="text-lg font-bold"></h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalContent" class="p-6 overflow-y-auto flex-grow">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    function refreshStatusIndicators() {
        document.querySelectorAll('[data-schedule-start]').forEach(el => {
            const start = new Date(el.dataset.scheduleStart);
            const end = new Date(el.dataset.scheduleEnd);
            const now = new Date();
            const statusDot = el.querySelector('.status-dot');

            if (now > end) {
                statusDot.classList.remove('bg-yellow-500', 'bg-blue-500');
                statusDot.classList.add('bg-green-500');
            } else if (now >= start && now <= end) {
                statusDot.classList.remove('bg-yellow-500', 'bg-green-500');
                statusDot.classList.add('bg-blue-500');
            } else {
                statusDot.classList.remove('bg-green-500', 'bg-blue-500');
                statusDot.classList.add('bg-yellow-500');
            }
        });
    }

    // Call initially and set interval
    refreshStatusIndicators();
    setInterval(refreshStatusIndicators, 60000);
    // Modal functions
    // Update modal status indicators when opened
    function openDayModal(date, title) {
        fetch(`/admin/calendar/day-details?date=${date}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalTitle').textContent = title;
                document.getElementById('modalContent').innerHTML = html;
                document.getElementById('dayModal').classList.remove('hidden');

                // Set up auto-refresh for modal status indicators
                const modalRefreshInterval = setInterval(() => {
                    fetch(`/admin/calendar/day-details?date=${date}`)
                        .then(response => response.text())
                        .then(html => {
                            if (!document.getElementById('dayModal').classList.contains('hidden')) {
                                document.getElementById('modalContent').innerHTML = html;
                            } else {
                                clearInterval(modalRefreshInterval);
                            }
                        });
                }, 60000);
            });
    }

    function closeModal() {
        document.getElementById('dayModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('dayModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Calendar navigation
    document.addEventListener('DOMContentLoaded', function() {
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