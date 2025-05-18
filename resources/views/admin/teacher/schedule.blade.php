@extends('layouts.app')

@section('content')
<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <!-- Top Bar -->
    <header class="flex justify-between items-center bg-white text-gray px-6 py-4 rounded-lg shadow-md">
        <div class="flex items-center space-x-5">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <span class="text-sm font-semibold">Schedule for {{ $teacher->first_name }}</span>
        </div>
        <div class="flex items-center space-x-4">
            <a href="{{ url('admin/teacher/list') }}"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Back to Teachers
            </a>
        </div>
    </header>

    <!-- Schedule Section -->
    <section class="mt-6">
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <!-- Weekly Schedule Grid -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($days as $day)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-bold text-center mb-3 capitalize">{{ $day }}</h3>
                    <div class="space-y-2">
                        @foreach($weeklySchedules->where('day', $day) as $schedule)
                        <div class="bg-blue-100 border border-blue-200 rounded p-2 text-sm">
                            <div class="font-semibold">{{ $schedule->room->room_name }}</div>
                            <div>{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</div>
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