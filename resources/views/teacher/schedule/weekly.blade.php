@extends('layouts.app')

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

    <!-- Schedule Section -->
    <section class="mt-6">
        @include('_message')
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-lg font-karla font-semibold text-gray-900">Weekly Schedule</h1>
                <button id="openModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Add Schedule +
                </button>
            </div>

            <!-- Weekly Schedule Grid -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($days as $day)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-bold text-center mb-3 capitalize">{{ $day }}</h3>
                    <div class="space-y-2">
                        @foreach($weeklySchedules->where('day', $day) as $schedule)
                        <div class="bg-blue-100 border border-blue-200 rounded p-2 text-sm relative group">
                            <div class="font-semibold">{{ $schedule->room->room_name }}</div>
                            <div>{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</div>

                            <!-- Delete Button -->
                            <form action="{{ route('teacher.schedule.weekly.delete', $schedule->id) }}" method="POST" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this schedule?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Create Schedule Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-full max-w-lg p-6 bg-white border-2 border-blue-600 rounded-lg shadow-lg">
            <div class="flex justify-between items-center border-b border-blue-300 pb-2 mb-4">
                <h2 class="text-xl font-bold text-blue-700">ADD WEEKLY SCHEDULE</h2>
                <button id="closeModalBtn" class="text-blue-700 hover:text-red-500 text-lg">&times;</button>
            </div>

            <form action="{{ route('teacher.schedule.weekly.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Room</label>
                        <select name="room_id" class="p-2 border border-gray-300 rounded-md bg-blue-600 text-white" required>
                            <option value="">Pick a room</option>
                            @foreach ($getRoom as $room)
                            <option value="{{ $room->id }}">{{ $room->room_name }} ({{ $room->capacity }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Day</label>
                        <select name="day" class="p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
                            <option value="">Select day</option>
                            <option value="monday">Monday</option>
                            <option value="tuesday">Tuesday</option>
                            <option value="wednesday">Wednesday</option>
                            <option value="thursday">Thursday</option>
                            <option value="friday">Friday</option>
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Time</label>
                        <div class="flex space-x-2">
                            <select name="start_time" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
                                @foreach(range(7, 17) as $hour)
                                <option value="{{ sprintf('%02d:00:00', $hour) }}">{{ $hour }}:00</option>
                                @endforeach
                            </select>
                            <select name="end_time" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
                                @foreach(range(8, 18) as $hour)
                                <option value="{{ sprintf('%02d:00:00', $hour) }}">{{ $hour }}:00</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="closeModalBtn2" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Modal handling
        const openModalBtn = document.getElementById("openModalBtn");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const closeModalBtn2 = document.getElementById("closeModalBtn2");
        const scheduleModal = document.getElementById("scheduleModal");

        openModalBtn.addEventListener("click", function() {
            scheduleModal.classList.remove("hidden");
        });

        [closeModalBtn, closeModalBtn2].forEach(btn => {
            btn.addEventListener("click", function() {
                scheduleModal.classList.add("hidden");
            });
        });

        // Time validation
        const startTime = document.querySelector("select[name='start_time']");
        const endTime = document.querySelector("select[name='end_time']");

        // startTime.addEventListener("change", function() {
        //     const startHour = parseInt(this.value.split(':')[0]);
        //     Array.from(endTime.options).forEach(option => {
        //         const endHour = parseInt(option.value.split(':')[0]);
        //         option.disabled = endHour <= startHour;
        //     });
        //     endTime.value = startHour + 1 + ":00:00";
        // });
        startTime.addEventListener("change", function() {
            const startHour = parseInt(this.value.split(':')[0]);
            Array.from(endTime.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                option.disabled = endHour <= startHour;
                // Ensure at least 1 hour difference
                option.disabled = endHour <= startHour;
            });
            // Auto-select next hour if current selection is invalid
            if (parseInt(endTime.value.split(':')[0]) <= startHour) {
                endTime.value = (startHour + 1) + ":00:00";
            }
        });
    });
</script>
@endsection