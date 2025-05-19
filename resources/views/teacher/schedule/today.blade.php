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

        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <h1 class="text-lg font-karla font-semibold text-gray-900 mb-5">Today's Schedule</h1>
            @if($schedules->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-calendar-check text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700">No schedules for today</h3>
                <p class="text-gray-500 mt-2">You don't have any classes scheduled for today.</p>
            </div>
            @else
            <div class="space-y-4">
                @foreach($schedules as $schedule)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg">{{ $schedule->room->room_name }}</h3>
                            <p class="text-gray-600">
                                {{ date('g:i A', strtotime($schedule->start_time)) }} -
                                {{ date('g:i A', strtotime($schedule->end_time)) }}
                            </p>
                            @if(isset($schedule->subject))
                            <p class="text-sm text-gray-500 mt-1">Subject: {{ $schedule->subject }}</p>
                            @endif
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $schedule->status_badge }}">
                            {{ ucfirst($schedule->status) }}
                        </span>
                    </div>

                    @if($schedule->status == 'ongoing')
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center text-blue-600">
                            <i class="fas fa-clock mr-2"></i>
                            <span>Class is currently in session</span>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </section>
</main>

<!-- Auto-refresh script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh every minute to update statuses
        setInterval(() => {
            window.location.reload();
        }, 60000);
    });
</script>
@endsection