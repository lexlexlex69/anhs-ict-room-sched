@extends('layouts.app')

@section('content')
<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <!-- Top Bar -->
    <header class="flex flex-col sm:flex-row justify-between items-center bg-white text-gray px-6 py-4 rounded-lg shadow-md space-y-2 sm:space-y-0">
        <div class="flex items-center space-x-5">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <span class="text-sm font-semibold">All Teachers' Schedules</span>
        </div>
    </header>

    <!-- Schedule Section -->
    <section class="mt-6">
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <!-- Room Filter -->
            <div class="mb-6">
                <form method="GET" action="{{ route('admin.schedule.all') }}" class="flex items-center space-x-4">
                    <select name="room_id" class="p-2 border border-gray-300 rounded-md bg-white" onchange="this.form.submit()">
                        <option value="">All Rooms</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->room_name }}
                        </option>
                        @endforeach
                    </select>
                    <select name="teacher_id" class="p-2 border border-gray-300 rounded-md bg-white" onchange="this.form.submit()">
                        <option value="">All Teachers</option>
                        @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->first_name }}
                        </option>
                        @endforeach
                    </select>
                </form>
            </div>


            <!-- Weekly Schedule Grid -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($days as $day)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-bold text-center mb-3 capitalize">{{ $day }}</h3>
                    <div class="space-y-2">
                        @php
                        // Group schedules by room and time slot
                        $daySchedules = $weeklySchedules->where('day', $day)->groupBy(function($item) {
                        return $item->room_id.'-'.$item->start_time.'-'.$item->end_time;
                        });
                        @endphp

                        @foreach($daySchedules as $timeSlot => $schedules)
                        @php
                        // Get the first schedule to get room info
                        $firstSchedule = $schedules->first();
                        // Generate a unique color based on room ID
                        $colors = ['bg-blue-100', 'bg-green-100', 'bg-yellow-100', 'bg-purple-100', 'bg-pink-100', 'bg-indigo-100'];
                        $colorIndex = $firstSchedule->room_id % count($colors);
                        $bgColor = $colors[$colorIndex];
                        $borderColor = str_replace('bg', 'border', $bgColor);
                        @endphp

                        <div class="{{ $bgColor }} border {{ $borderColor }} rounded p-2 text-sm mb-2">
                            <div class="font-semibold">{{ $firstSchedule->room->room_name }}</div>
                            <div class="text-gray-600 mb-1">
                                {{ date('g:i A', strtotime($firstSchedule->start_time)) }} - {{ date('g:i A', strtotime($firstSchedule->end_time)) }}
                            </div>
                            <div class="border-t border-gray-200 mt-1 pt-1">
                                @foreach($schedules as $schedule)
                                <div class="flex items-center mt-1">
                                    <div class="w-6 h-6 rounded-full overflow-hidden mr-2">
                                        @if(!empty($schedule->teacher->getProfilePictureUrl()))
                                        <img src="{{ $schedule->teacher->getProfilePictureUrl() }}" class="w-full h-full object-cover">
                                        @else
                                        <div class="w-full h-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-xs text-gray-600"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <span>{{ $schedule->teacher->first_name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection