<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agusan National High School</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="flex flex-col items-center justify-center min-h-screen" style="background-color: #fbf9f9ff;">

    <div class="absolute inset-0" style="background-color: #fbf9f9ff;"></div>



    <!-- Header -->
    <header class="absolute top-4 left-4 right-4 flex justify-between items-center p-3 bg-white text-gray rounded-lg shadow-lg w-[95%] md:w-[80%] mx-auto">
        <div class="flex items-center space-x-5">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <span id="current-date" class="text-sm">Loading date...</span>
            <span id="current-time" class="text-sm font-semibold">Loading time...</span>
        </div>
    </header>

    <!-- Main Content -->
    <div class="relative z-10 flex flex-col items-center w-full max-w-lg px-6 py-12 
            bg-white bg-opacity-30 backdrop-blur-lg border border-white border-opacity-20 
            rounded-lg shadow-lg mt-20">
        <!-- School Logo -->
        <img src="{{ asset('asset/img/logo.png') }}" alt="School Logo" class="w-24 mb-4">

        <h1 class="text-2xl font-semibold text-gray-800 text-center">Agusan National High School</h1>
        <p class="text-sm text-gray-600 text-center mb-6">Butuan City, Agusan Del Norte, Philippines</p>

        <h2 class="text-md font-semibold text-gray-700 uppercase mb-4">Log in as</h2>

        <!-- Login Buttons -->
        <a href="{{ url('loginfront') }}" class="w-full flex items-center justify-between px-6 py-3 text-white bg-blue-500 rounded-lg hover:bg-blue-700 transition mb-4">
            <span class="font-medium">Teacher</span>
            <i class="fa-solid fa-right-to-bracket"></i>
        </a>

        <a href="{{ url('loginfront') }}" class="w-full flex items-center justify-between px-6 py-3 text-white bg-blue-500 rounded-lg hover:bg-blue-700 transition">
            <span class="font-medium">Admin</span>
            <i class="fa-solid fa-right-to-bracket"></i>
        </a>
        <button id="openReservationModal" class="w-full flex items-center justify-between px-6 py-3 text-white bg-green-500 rounded-lg hover:bg-green-700 transition mt-4">
            <span class="font-medium">Reserve a Room</span>
            <i class="fa-solid fa-calendar-plus"></i>
        </button>
        <button id="openStatusModal" class="w-full flex items-center justify-between px-6 py-3 text-white bg-purple-500 rounded-lg hover:bg-purple-700 transition mt-4">
            <span class="font-medium">Check Ticket Status</span>
            <i class="fa-solid fa-ticket"></i>
        </button>
    </div>
    <div id="statusModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Check Ticket Status</h2>
                <button id="closeStatusModal" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>

            <form id="checkStatusForm">
                @csrf
                <div class="flex flex-col">
                    <label class="text-sm font-bold mb-2">Reference Number</label>
                    <input type="text" name="reference_number" class="p-2 border rounded-md"
                        placeholder="Enter your reference number" required>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="closeStatusModal2" class="px-4 py-2 bg-gray-300 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600">
                        Check Status
                    </button>
                </div>
            </form>

            <div id="statusResult" class="mt-4 hidden">
                <div class="border-t pt-4">
                    <h3 class="font-bold text-lg mb-2">Reservation Details</h3>
                    <div id="statusContent"></div>
                </div>
            </div>
        </div>
    </div>
    <div id="reservationModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-full max-w-lg p-6 bg-white rounded-lg shadow-lg">
            <div class="flex justify-between items-center border-b pb-2 mb-4">
                <h2 class="text-xl font-bold">Room Reservation</h2>
                <button id="closeReservationModal" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>

            <!-- Step 1: Check Availability -->
            <div id="step1">
                <form id="checkAvailabilityForm">
                    @csrf
                    <div class="space-y-4">
                        <div class="flex flex-col">
                            <label class="text-sm font-bold">Room</label>
                            <select name="room_id" class="p-2 border rounded-md" required>
                                <option value="">Select Room</option>
                                @foreach(App\Models\Room::all() as $room)
                                <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col">
                            <label class="text-sm font-bold">Date</label>
                            <input type="date" name="date" min="{{ date('Y-m-d') }}" class="p-2 border rounded-md" required>
                        </div>

                        <div class="flex flex-col">
                            <label class="text-sm font-bold">Time</label>
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
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" id="closeReservationModal2" class="px-4 py-2 bg-gray-300 rounded-md">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Check Availability</button>
                    </div>
                </form>
            </div>

            <!-- Step 2: Reservation Form (shown after availability check) -->
            <div id="step2" class="hidden">
                <form id="reservationForm" action="{{ route('reservation.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="room_id" id="reservation_room_id">
                    <input type="hidden" name="date" id="reservation_date">
                    <input type="hidden" name="start_time" id="reservation_start_time">
                    <input type="hidden" name="end_time" id="reservation_end_time">

                    <div class="space-y-4">
                        <div class="bg-green-100 border border-green-200 p-3 rounded mb-4">
                            <p id="availabilityMessage" class="text-green-700"></p>
                        </div>

                        <div class="flex flex-col">
                            <label class="text-sm font-bold">Your Name</label>
                            <input type="text" name="teacher_name" class="p-2 border rounded-md" required>
                        </div>

                        <div class="flex flex-col">
                            <label class="text-sm font-bold">Subject</label>
                            <input type="text" name="subject" class="p-2 border rounded-md" required>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" id="backToStep1" class="px-4 py-2 bg-gray-300 rounded-md">Back</button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Submit Reservation</button>
                    </div>
                </form>
            </div>

            <!-- Step 3: Confirmation (shown after successful reservation) -->
            <div id="step3" class="hidden text-center">
                <div class="bg-blue-100 border border-blue-200 p-4 rounded-lg mb-4">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                    <h3 class="text-lg font-bold">Reservation Successful!</h3>
                    <p class="mt-2">Your reference number is:</p>
                    <p id="referenceNumber" class="text-xl font-mono font-bold my-2"></p>
                    <p>Please keep this for your records.</p>
                </div>
                <button id="closeAfterReservation" class="px-4 py-2 bg-blue-500 text-white rounded-md">Close</button>
            </div>
        </div>
    </div>
    <!-- JavaScript for Real-Time Date and Time -->
    <script>
        function updateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            document.getElementById('current-date').innerText = now.toLocaleDateString('en-US', options);
            document.getElementById('current-time').innerText = now.toLocaleTimeString();
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
    <script>
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
                    if (data.available_slots.length > 0) {
                        const suggestionsDiv = document.createElement('div');
                        suggestionsDiv.className = 'bg-blue-50 border border-blue-200 p-3 rounded mb-4';

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
                        <span>${startTime} - ${endTime}</span>
                        <button onclick="useSuggestedSlot('${slot.start}', '${slot.end}')" 
                            class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Use this slot
                        </button>
                    </li>
                `;
                        });

                        suggestionsHTML += '</ul>';
                        suggestionsDiv.innerHTML = suggestionsHTML;

                        // Insert suggestions after error message
                        const errorDiv = document.querySelector('#step1 .error-message');
                        if (errorDiv) {
                            errorDiv.insertAdjacentElement('afterend', suggestionsDiv);
                        } else {
                            const form = document.getElementById('checkAvailabilityForm');
                            form.insertBefore(suggestionsDiv, form.firstChild);
                        }
                    }
                });
        }

        // Add this function to use suggested slots
        function useSuggestedSlot(startTime, endTime) {
            document.querySelector('select[name="start_time"]').value = startTime;
            document.querySelector('select[name="end_time"]').value = endTime;

            // Remove suggestions
            const suggestionsDiv = document.querySelector('.bg-blue-50');
            if (suggestionsDiv) suggestionsDiv.remove();

            // Re-check availability
            document.getElementById('checkAvailabilityForm').dispatchEvent(new Event('submit'));
        }
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('reservationModal');
            const openBtn = document.getElementById('openReservationModal');
            const closeBtns = [
                document.getElementById('closeReservationModal'),
                document.getElementById('closeReservationModal2'),
                document.getElementById('closeAfterReservation')
            ];

            // Modal toggle
            openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
            closeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    document.getElementById('step1').classList.remove('hidden');
                    document.getElementById('step2').classList.add('hidden');
                    document.getElementById('step3').classList.add('hidden');
                });
            });

            // Back button
            document.getElementById('backToStep1').addEventListener('click', function() {
                document.getElementById('step1').classList.remove('hidden');
                document.getElementById('step2').classList.add('hidden');
            });

            // Check availability
            document.getElementById('checkAvailabilityForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const duration = parseInt(formData.get('end_time').split(':')[0]) -
                    parseInt(formData.get('start_time').split(':')[0]);

                // Clear any previous error messages
                const existingError = document.querySelector('#step1 .error-message');
                if (existingError) existingError.remove();

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
                        if (data.available) {
                            // Set hidden fields for step 2
                            document.getElementById('reservation_room_id').value = formData.get('room_id');
                            document.getElementById('reservation_date').value = formData.get('date');
                            document.getElementById('reservation_start_time').value = formData.get('start_time');
                            document.getElementById('reservation_end_time').value = formData.get('end_time');

                            // Show availability message
                            const date = new Date(formData.get('date'));
                            const day = date.toLocaleDateString('en-US', {
                                weekday: 'long'
                            });
                            const startTime = new Date('2000-01-01T' + formData.get('start_time')).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            const endTime = new Date('2000-01-01T' + formData.get('end_time')).toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            document.getElementById('availabilityMessage').innerHTML = `
                <strong>${data.room.room_name}</strong> is available on<br>
                ${day}, ${formData.get('date')} from ${startTime} to ${endTime}
            `;

                            // Show step 2
                            document.getElementById('step1').classList.add('hidden');
                            document.getElementById('step2').classList.remove('hidden');
                        } else {
                            // Create error message element
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'bg-red-100 border border-red-200 text-red-700 p-3 rounded mb-4 error-message';
                            errorDiv.innerHTML = data.message || 'Room is not available at the selected time. Please choose another time or room.';

                            // Insert error message after the form header
                            const form = document.getElementById('checkAvailabilityForm');
                            form.insertBefore(errorDiv, form.firstChild);

                            // Add animation to highlight the error
                            errorDiv.style.animation = 'fadeIn 0.3s ease-in-out';
                            suggestOptimalSlots(
                                formData.get('room_id'),
                                formData.get('date'),
                                duration
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            // Handle reservation submission
            document.getElementById('reservationForm').addEventListener('submit', function(e) {
                e.preventDefault();

                fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('step2').classList.add('hidden');
                            document.getElementById('referenceNumber').textContent = data.reference_number;
                            document.getElementById('step3').classList.remove('hidden');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            });
        });
        startTime.addEventListener("change", function() {
            const startHour = parseInt(this.value.split(':')[0]);
            const endSelect = document.querySelector("select[name='end_time']");

            Array.from(endSelect.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                // Disable times that are before or equal to start time
                option.disabled = endHour <= startHour;
            });

            // Auto-select the next hour if current selection is invalid
            if (parseInt(endSelect.value.split(':')[0]) <= startHour) {
                endSelect.value = (startHour + 1) + ":00:00";
            }
        });
    </script>
    <script>
        // Status Modal Handling
        const statusModal = document.getElementById('statusModal');
        const openStatusBtn = document.getElementById('openStatusModal');
        const closeStatusBtns = [
            document.getElementById('closeStatusModal'),
            document.getElementById('closeStatusModal2')
        ];

        openStatusBtn.addEventListener('click', () => statusModal.classList.remove('hidden'));
        closeStatusBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                statusModal.classList.add('hidden');
                document.getElementById('statusResult').classList.add('hidden');
            });
        });

        // Check Status Form Submission
        document.getElementById('checkStatusForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const statusResult = document.getElementById('statusResult');
            const statusContent = document.getElementById('statusContent');

            fetch('{{ route("reservation.check-status") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'not_found') {
                        statusContent.innerHTML = `
                <div class="bg-red-100 border border-red-200 text-red-700 p-3 rounded">
                    ${data.message}
                </div>
            `;
                    } else {
                        const reservation = data.reservation;
                        const date = new Date(reservation.date);
                        const formattedDate = date.toLocaleDateString('en-US', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });

                        const startTime = new Date('2000-01-01T' + reservation.start_time).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        const endTime = new Date('2000-01-01T' + reservation.end_time).toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        let statusBadge = '';
                        if (data.detailed_status === 'cancelled') {
                            statusBadge = `<span class="bg-red-500 text-white px-2 py-1 rounded text-xs">Cancelled</span>`;
                        } else if (data.detailed_status === 'pending') {
                            statusBadge = `<span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">Pending</span>`;
                        } else if (data.detailed_status === 'ongoing') {
                            statusBadge = `<span class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Upcoming</span>`;
                        } else if (data.detailed_status === 'completed') {
                            statusBadge = `<span class="bg-green-500 text-white px-2 py-1 rounded text-xs">Completed</span>`;
                        }

                        let remarksSection = '';
                        if (reservation.status === 'cancelled' && reservation.remarks) {
                            remarksSection = `
                    <div class="mt-3">
                        <h4 class="font-semibold">Remarks:</h4>
                        <p class="text-gray-700">${reservation.remarks}</p>
                    </div>
                `;
                        }

                        statusContent.innerHTML = `
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="font-semibold">Status: ${statusBadge}</span>
                       
                    </div>
              
                    <div><span class="font-semibold">Room:</span> ${data.room_name}</div>
                    <div><span class="font-semibold">Date:</span> ${formattedDate}</div>
                    <div><span class="font-semibold">Time:</span> ${startTime} - ${endTime}</div>
                    <div><span class="font-semibold">Teacher:</span> ${reservation.teacher_name}</div>
                    <div><span class="font-semibold">Subject:</span> ${reservation.subject}</div>
                    ${remarksSection}
                    <div class="mt-3 text-sm text-gray-500">
                        Current server time: ${data.current_time}
                    </div>
                </div>
            `;
                    }

                    statusResult.classList.remove('hidden');
                });
        });
    </script>


</body>

</html>