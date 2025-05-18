@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen" style="background-color: #fbf9f9ff;">
    <div class="relative z-10 w-full max-w-2xl px-6 py-8 bg-white rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-center mb-6">Room Reservation</h1>

        <form id="reservationForm" method="POST" action="{{ route('reservation.submit') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Room Selection -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                    <select name="room_id" class="w-full p-2 border border-gray-300 rounded-md" required>
                        <option value="">Select a room</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->room_name }} (Capacity: {{ $room->capacity }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" id="reservationDate"
                        class="w-full p-2 border border-gray-300 rounded-md"
                        min="{{ date('Y-m-d') }}" required>
                </div>

                <!-- Time Selection -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                        <select name="start_time" class="w-full p-2 border border-gray-300 rounded-md" required>
                            @for($i = 7; $i <= 17; $i++)
                                <option value="{{ sprintf('%02d:00', $i) }}">{{ $i }}:00</option>
                                @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                        <select name="end_time" class="w-full p-2 border border-gray-300 rounded-md" required>
                            @for($i = 8; $i <= 18; $i++)
                                <option value="{{ sprintf('%02d:00', $i) }}">{{ $i }}:00</option>
                                @endfor
                        </select>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="col-span-2">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                        <input type="text" name="name" class="w-full p-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" class="w-full p-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>
            </div>

            <!-- Check Availability Button -->
            <div class="mt-6 flex justify-center">
                <button type="button" id="checkAvailabilityBtn"
                    class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                    Check Availability
                </button>
            </div>

            <!-- Availability Results -->
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

                <!-- Submit Button -->
                <div class="mt-6 flex justify-center">
                    <button type="submit" id="submitBtn"
                        class="px-6 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition hidden">
                        Submit Reservation
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reservationForm');
        const checkBtn = document.getElementById('checkAvailabilityBtn');
        const resultsDiv = document.getElementById('availabilityResults');
        const availableMsg = document.getElementById('availableMessage');
        const conflictMsg = document.getElementById('conflictMessage');
        const conflictList = document.getElementById('conflictList');
        const alternativesList = document.getElementById('alternativesList');
        const submitBtn = document.getElementById('submitBtn');

        checkBtn.addEventListener('click', function() {
            // Show loading state
            checkBtn.disabled = true;
            checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';

            const formData = {
                room_id: document.querySelector('select[name="room_id"]').value,
                date: document.getElementById('reservationDate').value,
                start_time: document.querySelector('select[name="start_time"]').value,
                end_time: document.querySelector('select[name="end_time"]').value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            fetch("{{ route('reservation.check') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData._token
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => {
                    checkBtn.disabled = false;
                    checkBtn.innerHTML = 'Check Availability';

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // ... rest of your success handling code
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while checking availability. Please try again.');
                });
        });
    });
</script>
@endsection