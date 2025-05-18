@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-gray-900">Room Reservation</h2>
        </div>

        <form id="reservationForm" class="mt-8 space-y-6">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="room_id" class="block text-sm font-medium text-gray-700">Room</label>
                    <select id="room_id" name="room_id" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        <option value="">Select a room</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->room_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="date" name="date" min="{{ date('Y-m-d') }}" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                        <select id="start_time" name="start_time" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                            @for($i = 7; $i <= 17; $i++)
                                <option value="{{ sprintf('%02d:00:00', $i) }}">{{ $i }}:00</option>
                                @endfor
                        </select>
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                        <select id="end_time" name="end_time" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                            @for($i = 8; $i <= 18; $i++)
                                <option value="{{ sprintf('%02d:00:00', $i) }}" @if($i==8) selected @endif>{{ $i }}:00</option>
                                @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div id="suggestions" class="hidden p-4 bg-yellow-50 rounded-md">
                <p class="text-sm font-medium text-yellow-800">The selected time is not available. Here are some suggestions:</p>
                <ul id="suggestionList" class="mt-2 space-y-2"></ul>
            </div>

            <div id="userDetails" class="hidden space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                    <input type="text" id="name" name="name" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" id="subject" name="subject" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                    Submit Reservation
                </button>
            </div>

            <button id="checkAvailabilityBtn" type="button" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                Check Availability
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reservationForm');
        const checkBtn = document.getElementById('checkAvailabilityBtn');
        const suggestionsDiv = document.getElementById('suggestions');
        const suggestionList = document.getElementById('suggestionList');
        const userDetails = document.getElementById('userDetails');

        checkBtn.addEventListener('click', function() {
            const formData = new FormData(form);

            fetch("{{ route('reservation.check') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        userDetails.classList.remove('hidden');
                        suggestionsDiv.classList.add('hidden');
                        checkBtn.classList.add('hidden');
                    } else {
                        suggestionList.innerHTML = '';
                        data.suggestions.forEach(suggestion => {
                            const li = document.createElement('li');
                            li.className = 'text-sm';
                            li.innerHTML = `
                        <button type="button" class="text-blue-600 hover:text-blue-800" 
                            onclick="selectSuggestion('${suggestion.date}', '${suggestion.start_time}', '${suggestion.end_time}')">
                            ${suggestion.day_name}, ${suggestion.date} (${suggestion.start_time} - ${suggestion.end_time})
                        </button>
                    `;
                            suggestionList.appendChild(li);
                        });
                        suggestionsDiv.classList.remove('hidden');
                    }
                });
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            fetch("{{ route('reservation.submit') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    window.location.href = "/reserve/confirmation/" + data.reference_number;
                });
        });
    });

    function selectSuggestion(date, startTime, endTime) {
        document.getElementById('date').value = date;
        document.getElementById('start_time').value = startTime;
        document.getElementById('end_time').value = endTime;
        document.getElementById('suggestions').classList.add('hidden');
        document.getElementById('userDetails').classList.remove('hidden');
        document.getElementById('checkAvailabilityBtn').classList.add('hidden');
    }
</script>
@endsection