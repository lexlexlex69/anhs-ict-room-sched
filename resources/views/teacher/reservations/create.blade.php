<?php
// File: resources/views/teacher/reservations/create.blade.php
// New file for teacher room reservation page
?>
@extends('layouts.app')

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
                <span id="notification-badge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">0</span>
                <div id="notification-dropdown" class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg z-50 hidden border border-gray-200">
                    <div class="p-3">
                        <p class="text-sm font-semibold text-gray-700">Notifications</p>
                        <ul id="notification-list" class="mt-2 max-h-60 overflow-y-auto text-gray-800"></ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="mt-6">
        @include('_message')
        <div class="bg-white bg-opacity-80 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Reserve a Room</h2>

            <form id="checkAvailabilityForm">
                @csrf
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Room</label>
                        <select name="room_id" class="p-2 border rounded-md" required>
                            <option value="">Select Room</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->room_name }} (Capacity: {{ $room->capacity }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Date</label>
                        <input type="date" name="date" min="{{ date('Y-m-d') }}" class="p-2 border rounded-md" required>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Time</label>
                        <div class="flex space-x-2">
                            <select name="start_time" class="w-full p-2 border rounded-md" required>
                                @for($hour = 7; $hour <= 17; $hour++)
                                    <option value="{{ sprintf('%02d:00:00', $hour) }}">{{ $hour }}:00</option>
                                    @endfor
                            </select>
                            <select name="end_time" class="w-full p-2 border rounded-md" required>
                                @for($hour = 8; $hour <= 18; $hour++)
                                    <option value="{{ sprintf('%02d:00:00', $hour) }}">{{ $hour }}:00</option>
                                    @endfor
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="teacher_name" value="{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}">
                    <input type="hidden" name="subject" value="{{ Auth::user()->subject }}">
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="submit" id="checkAvailabilityBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Check Availability</button>
                </div>
            </form>

            <div id="availabilityResults" class="mt-6 hidden">
                <div id="availableMessage" class="p-4 bg-green-100 text-green-800 rounded-md mb-4 hidden">
                    The room is available at your selected time!
                </div>
                <div id="conflictMessage" class="p-4 bg-red-100 text-red-800 rounded-md mb-4 hidden">
                    <p class="font-semibold">The room is not available at your selected time due to:</p>
                    <ul id="conflictList" class="list-disc pl-5 mt-2"></ul>
                    <div id="alternativesSection" class="mt-4">
                        <p class="font-semibold">Suggested alternative times:</p>
                        <ul id="alternativesList" class="list-disc pl-5 mt-2"></ul>
                    </div>
                </div>
                <div class="mt-6 flex justify-center">
                    <button type="button" id="submitReservationBtn" class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition hidden">
                        Submit Reservation
                    </button>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkBtn = document.getElementById('checkAvailabilityBtn');
        const resultsDiv = document.getElementById('availabilityResults');
        const availableMsg = document.getElementById('availableMessage');
        const conflictMsg = document.getElementById('conflictMessage');
        const conflictList = document.getElementById('conflictList');
        const alternativesList = document.getElementById('alternativesList');
        const submitBtn = document.getElementById('submitReservationBtn');
        const checkAvailabilityForm = document.getElementById('checkAvailabilityForm');

        // Function to clear previous messages
        function clearMessages() {
            availableMsg.classList.add('hidden');
            conflictMsg.classList.add('hidden');
            resultsDiv.classList.add('hidden');
            submitBtn.classList.add('hidden');
            conflictList.innerHTML = '';
            alternativesList.innerHTML = '';
            const existingError = document.querySelector('.error-message');
            if (existingError) existingError.remove();
            const existingSuggestions = document.querySelector('.bg-blue-50.border.border-blue-200');
            if (existingSuggestions) existingSuggestions.remove();
        }

        checkAvailabilityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearMessages(); // Clear previous messages

            checkBtn.disabled = true;
            checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';

            const formData = new FormData(this);
            const duration = parseInt(formData.get('end_time').split(':')[0]) -
                parseInt(formData.get('start_time').split(':')[0]);

            fetch('{{ route("reservation.check") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = 'Check Availability';
                    resultsDiv.classList.remove('hidden');

                    if (data.available) {
                        availableMsg.classList.remove('hidden');
                        submitBtn.classList.remove('hidden');
                    } else {
                        conflictMsg.classList.remove('hidden');
                        conflictList.innerHTML = `<li>${data.message}</li>`;
                        // Suggest optimal slots only if there's a conflict
                        suggestOptimalSlots(
                            formData.get('room_id'),
                            formData.get('date'),
                            duration
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Instead of alert, display a message in the UI
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'bg-red-100 border border-red-200 text-red-700 p-3 rounded mb-4 error-message';
                    errorDiv.textContent = 'An error occurred while checking availability. Please try again.';
                    checkAvailabilityForm.insertBefore(errorDiv, checkAvailabilityForm.firstChild);
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = 'Check Availability';
                });
        });

        submitBtn.addEventListener('click', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            const formData = new FormData(checkAvailabilityForm); // Use data from the check form

            fetch('{{ route("reservation.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Reservation';
                    if (data.success) {
                        clearMessages(); // Clear previous messages
                        const successDiv = document.createElement('div');
                        successDiv.className = 'bg-blue-100 border border-blue-200 p-4 rounded-lg mb-4 text-center';
                        successDiv.innerHTML = `
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                            <h3 class="text-lg font-bold">Reservation Submitted!</h3>
                            <p class="mt-2">Your reference number is:</p>
                            <p class="text-xl font-mono font-bold my-2">${data.reference_number}</p>
                            <p>Status: ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}</p>
                            <p>Please keep this for your records. The admin will review your request.</p>
                        `;
                        checkAvailabilityForm.parentNode.insertBefore(successDiv, checkAvailabilityForm.nextSibling);
                        checkAvailabilityForm.reset(); // Clear the form
                        checkAvailabilityForm.classList.add('hidden'); // Hide the form after submission
                    } else {
                        // Handle server-side validation errors or other issues
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'bg-red-100 border border-red-200 text-red-700 p-3 rounded mb-4 error-message';
                        errorDiv.textContent = data.message || 'An error occurred during reservation. Please try again.';
                        checkAvailabilityForm.insertBefore(errorDiv, checkAvailabilityForm.firstChild);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'bg-red-100 border border-red-200 text-red-700 p-3 rounded mb-4 error-message';
                    errorDiv.textContent = 'An error occurred during reservation. Please try again.';
                    checkAvailabilityForm.insertBefore(errorDiv, checkAvailabilityForm.firstChild);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Submit Reservation';
                });
        });

        function suggestOptimalSlots(roomId, date, duration) {
            fetch('{{ route("reservation.suggest-slots") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        room_id: roomId,
                        date: date,
                        duration: duration
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alternativesList.innerHTML = ''; // Clear previous suggestions
                    if (data.available_slots && data.available_slots.length > 0) {
                        const suggestionsDiv = document.createElement('div');
                        suggestionsDiv.className = 'bg-blue-50 border border-blue-200 p-3 rounded mt-4 slot-suggestion';

                        let suggestionsHTML = '<p class="font-semibold text-blue-800">Suggested available time slots:</p><ul class="mt-2 space-y-2">';

                        data.available_slots.forEach(slot => {
                            const startTime = new Date('2000-01-01T' + slot.start).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            const endTime = new Date('2000-01-01T' + slot.end).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            suggestionsHTML += `
                                <li class="flex justify-between items-center">
                                    <span>${startTime} - ${endTime} (${slot.duration} hours)</span>
                                    <button type="button" onclick="useSuggestedSlot('${slot.start}', '${slot.end}')" 
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium use-slot-btn">
                                        Use this slot
                                    </button>
                                </li>
                            `;
                        });

                        suggestionsHTML += '</ul>';
                        suggestionsDiv.innerHTML = suggestionsHTML;

                        conflictMsg.appendChild(suggestionsDiv); // Append to conflict message section
                    } else {
                        alternativesList.innerHTML = '<li>No alternative slots found for the requested duration.</li>';
                    }
                });
        }

        // Global function to be called from dynamically created buttons
        window.useSuggestedSlot = function(startTime, endTime) {
            document.querySelector('select[name="start_time"]').value = startTime;
            document.querySelector('select[name="end_time"]').value = endTime;
            // Re-submit the check availability form to re-validate and show the "Submit Reservation" button
            checkAvailabilityForm.dispatchEvent(new Event('submit'));
        };

        // Time validation for start and end times
        const startTimeSelect = document.querySelector('select[name="start_time"]');
        const endTimeSelect = document.querySelector('select[name="end_time"]');

        startTimeSelect.addEventListener('change', function() {
            const startHour = parseInt(this.value.split(':')[0]);
            Array.from(endTimeSelect.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                option.disabled = endHour <= startHour;
            });
            // If the currently selected end time is no longer valid, adjust it
            if (parseInt(endTimeSelect.value.split(':')[0]) <= startHour) {
                endTimeSelect.value = (startHour + 1).toString().padStart(2, '0') + ':00:00';
            }
        });
    });
</script>
@endsection