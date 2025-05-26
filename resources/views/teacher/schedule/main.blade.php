@extends('layouts.app')

@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <header class="flex flex-col sm:flex-row justify-between items-center bg-white text-gray-700 px-4 sm:px-6 py-4 rounded-lg shadow-md space-y-2 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <i class="fa-solid fa-calendar-day text-lg"></i>
            <span class="text-sm font-semibold">Today's Date: {{ now()->format('l, F j, Y') }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Current Time: {{ $currentTime }}</span>
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>
        </div>
    </header>

    <section class="mt-6">
        @include('_message') {{-- For displaying success/error messages (assuming you have this partial) --}}
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-lg font-karla font-semibold text-gray-900">My Main Schedules</h1>
                <button id="openMainScheduleModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Add New Schedule +
                </button>
            </div>

            <div class="space-y-4">
                @forelse($mainSchedules as $mainSchedule)
                <div class="bg-white rounded-lg shadow p-4 flex items-center justify-between group">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">{{ $mainSchedule->schedule_name }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ Carbon\Carbon::parse($mainSchedule->start_date)->format('F Y') }} -
                            {{ Carbon\Carbon::parse($mainSchedule->end_date)->format('F Y') }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        {{-- Link to view weekly schedules for this main schedule --}}
                        <a href="{{ route('teacher.schedules.weekly.show', $mainSchedule->id) }}" class="bg-indigo-500 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-600 transition">
                            View Weekly Schedules
                        </a>
                        <form action="{{ route('teacher.schedules.main.delete', $mainSchedule->id) }}" method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" onclick="return confirm('Are you sure you want to delete this main schedule and all its associated weekly schedules?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="text-center text-gray-500 p-8">
                    No main schedules added yet. Click "Add New Schedule +" to create one.
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <div id="mainScheduleModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-full max-w-lg p-6 bg-white border-2 border-blue-600 rounded-lg shadow-lg">
            <div class="flex justify-between items-center border-b border-blue-300 pb-2 mb-4">
                <h2 class="text-xl font-bold text-blue-700">ADD NEW MAIN SCHEDULE</h2>
                <button id="closeMainScheduleModalBtn" class="text-blue-700 hover:text-red-500 text-3xl">&times;</button>
            </div>

            <form action="{{ route('teacher.schedules.main.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="flex flex-col">
                        <label for="schedule_name" class="text-sm font-bold text-gray-700">Schedule Name</label>
                        <input type="text" name="schedule_name" id="schedule_name" class="p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
                    </div>

                    <div class="flex flex-col">
                        <label for="start_month" class="text-sm font-bold text-gray-700">Start Month</label>
                        {{-- Input type="month" provides YYYY-MM format, we use YYYY-MM-01 in backend --}}
                        <input type="month" name="start_month" id="start_month" class="p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
                    </div>

                    <div class="flex flex-col">
                        <label for="end_month" class="text-sm font-bold text-gray-700">End Month</label>
                        <input type="month" name="end_month" id="end_month" class="p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" id="closeMainScheduleModalBtn2" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Main Schedule Modal handling
        const openMainScheduleModalBtn = document.getElementById("openMainScheduleModalBtn");
        const closeMainScheduleModalBtn = document.getElementById("closeMainScheduleModalBtn");
        const closeMainScheduleModalBtn2 = document.getElementById("closeMainScheduleModalBtn2");
        const mainScheduleModal = document.getElementById("mainScheduleModal");

        openMainScheduleModalBtn.addEventListener("click", function() {
            mainScheduleModal.classList.remove("hidden");
        });

        [closeMainScheduleModalBtn, closeMainScheduleModalBtn2].forEach(btn => {
            btn.addEventListener("click", function() {
                mainScheduleModal.classList.add("hidden");
            });
        });

        // Current time update for header (if you want to keep this on this page)
        function updateTime() {
            const now = new Date();
            // Assuming the time span is the second .text-sm in the header div
            const timeSpan = document.querySelector("header .flex.items-center.space-x-4 span:nth-of-type(1)");
            if (timeSpan) {
                timeSpan.textContent = `Current Time: ${now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
            }
        }
        setInterval(updateTime, 60000); // Update every minute
        updateTime(); // Initial call
    });
</script>

@endsection