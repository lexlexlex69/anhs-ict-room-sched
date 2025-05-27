@extends('layouts.app')
@php use Carbon\Carbon; @endphp
@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <header class="flex justify-between items-center bg-white text-gray px-6 py-4 rounded-lg shadow-md">
        <div class="flex items-center space-x-5">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <span id="current-date" class="text-sm">Loading date...</span>
            <span id="current-time" class="text-sm font-semibold">Loading time...</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>

            <div class="relative">
                <i class="fa-solid fa-bell text-lg cursor-pointer text-gray" id="notification-icon"></i>

                <span id="notification-badge"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">
                    0
                </span>

                <div id="notification-dropdown"
                    class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg z-50 hidden border border-gray-200">
                    <div class="p-3">
                        <p class="text-sm font-semibold text-gray-700">Notifications</p>
                        <ul id="notification-list" class="mt-2 max-h-60 overflow-y-auto text-gray-800"></ul>

                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="mt-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Room Reservations</h1>
            <div class="flex items-center space-x-4">
                {{-- Month Filter --}}
                <select id="month-select" class="p-2 border rounded bg-white">
                    <option value="All" {{ $currentMonth == 'All' ? 'selected' : '' }}>All Months</option>
                    @foreach(range(1, 12) as $month)
                    <option value="{{ $month }}" {{ $month == Carbon::now()->month && $currentMonth == 'All' ? 'selected' : ($month == $currentMonth ? 'selected' : '') }}>
                        {{ date('F', mktime(0, 0, 0, $month, 1)) }}
                    </option>
                    @endforeach
                </select>

                {{-- Year Filter --}}
                <select id="year-select" class="p-2 border rounded bg-white">
                    @foreach(range(date('Y') - 2, date('Y') + 2) as $year)
                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                    @endforeach
                </select>

                {{-- Room Filter --}}
                <select id="room-select" class="p-2 border rounded bg-white">
                    <option value="All" {{ $currentRoom == 'All' ? 'selected' : '' }}>All Rooms</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ $room->id == $currentRoom ? 'selected' : '' }}>
                        {{ $room->room_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        @include('_message')

        <div class="space-y-8">
            @forelse($reservations as $date => $dateReservations)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 px-4 py-3 text-white">
                    <h2 class="text-lg font-semibold">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </h2>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($dateReservations as $reservation)
                    @php
                    $reservationEndDateTime = Carbon::parse($reservation->date . ' ' . $reservation->end_time);
                    $isPastReservation = $reservationEndDateTime->isPast();
                    @endphp
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div class="mb-3 sm:mb-0">
                                <div class="flex items-center space-x-3">
                                    <span class="font-semibold text-lg">{{ $reservation->room->room_name }}</span>
                                    <span class="px-2 py-1 text-xs rounded-full
                                    @if($reservation->status == 'approved') bg-green-100 text-green-800
                                    @elseif($reservation->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </div>
                                <div class="text-gray-600 mt-1">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                                </div>
                                <div class="mt-1">
                                    <span class="font-medium">Reserved by:</span> {{ $reservation->teacher_name }}
                                </div>
                                <div>
                                    <span class="font-medium">Subject:</span> {{ $reservation->subject }}
                                </div>
                                @if($reservation->reference_number)
                                <div class="mt-1 text-sm">
                                    <span class="font-medium">Ref #:</span> {{ $reservation->reference_number }}
                                </div>
                                @endif
                            </div>

                            @if($reservation->status == 'approved')
                            <div class="flex space-x-2">
                                <button onclick="openCancelModal('{{ $reservation->id }}')"
                                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm {{ $isPastReservation ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ $isPastReservation ? 'disabled' : '' }}>
                                    Cancel
                                </button>
                            </div>
                            @elseif($reservation->remarks)
                            <div class="text-sm text-gray-500 mt-2 sm:mt-0">
                                <span class="font-medium">Remarks:</span> {{ $reservation->remarks }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
                No reservations found for the selected filters.
            </div>
            @endforelse
        </div>

        <div id="cancelModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h2 class="text-xl font-bold">Cancel Reservation</h2>
                    <button type="button" onclick="closeCancelModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">Reason for cancellation</label>
                        <textarea name="remarks" id="remarks" rows="3" class="w-full border rounded-md p-2" required></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCancelModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Back
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
<script>
    function openCancelModal(reservationId) {
        const form = document.getElementById('cancelForm');
        form.action = `/admin/reservations/${reservationId}/cancel`;
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Function to update the current date and time
        function updateDateTime() {
            const now = new Date();

            // Format date (e.g., "Tuesday, May 27, 2025")
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);

            // Format time (e.g., "11:16 PM")
            const timeOptions = {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            };
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update date and time initially and every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Filter functionality
        function applyFilters() {
            const month = document.getElementById('month-select').value;
            const year = document.getElementById('year-select').value;
            const room_id = document.getElementById('room-select').value;

            const url = new URL(window.location.href);
            url.searchParams.set('month', month);
            url.searchParams.set('year', year);
            url.searchParams.set('room_id', room_id);
            window.location.href = url.toString();
        }

        document.getElementById('month-select').addEventListener('change', applyFilters);
        document.getElementById('year-select').addEventListener('change', applyFilters);
        document.getElementById('room-select').addEventListener('change', applyFilters);

        // Notification dropdown (existing functionality)
        const notificationIcon = document.getElementById('notification-icon');
        const notificationDropdown = document.getElementById('notification-dropdown');
        const notificationBadge = document.getElementById('notification-badge');
        const notificationList = document.getElementById('notification-list');

        function fetchNotifications() {
            fetch('/get-notifications') // Adjust this URL to your notification endpoint
                .then(response => response.json())
                .then(data => {
                    notificationList.innerHTML = ''; // Clear existing notifications
                    if (data.length > 0) {
                        notificationBadge.textContent = data.length;
                        notificationBadge.classList.remove('hidden');
                        data.forEach(notification => {
                            const listItem = document.createElement('li');
                            listItem.classList.add('p-2', 'border-b', 'last:border-b-0', 'text-sm');
                            listItem.textContent = notification.message; // Assuming notification has a 'message' field
                            notificationList.appendChild(listItem);
                        });
                    } else {
                        notificationBadge.classList.add('hidden');
                        const emptyItem = document.createElement('li');
                        emptyItem.classList.add('p-2', 'text-sm', 'text-gray-500');
                        emptyItem.textContent = "No new notifications.";
                        notificationList.appendChild(emptyItem);
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                });
        }

        notificationIcon.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent document click from closing it immediately
            notificationDropdown.classList.toggle('hidden');

            if (!notificationDropdown.classList.contains('hidden')) {
                // Mark notifications as read when dropdown is opened
                fetch('/mark-notifications-as-read', { // Adjust this URL to your endpoint
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, // If you're using CSRF protection
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                }).then(() => {
                    notificationBadge.classList.add('hidden');
                }).catch(error => {
                    console.error('Error marking notifications as read:', error);
                });
            }
        });

        // Close notification dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationDropdown.contains(event.target) && !notificationIcon.contains(event.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });

        fetchNotifications(); // Initial fetch
        setInterval(fetchNotifications, 60000); // Fetch every minute
    });
</script>

@endsection