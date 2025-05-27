@extends('layouts.app')

@section('content')
<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
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

    <section class="mt-6">
        <div class="flex justify-end mb-4">
            <select id="main-schedule-filter" class="p-2 border rounded bg-white">
                <option value="all">Select a schedule name</option>
                @foreach($mainSchedules as $mainSchedule)
                <option value="{{ $mainSchedule->id }}" {{ $selectedMainScheduleId == $mainSchedule->id ? 'selected' : '' }}>
                    {{ $mainSchedule->schedule_name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($days as $day)
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="font-bold text-center mb-3 capitalize">{{ $day }}</h3>
                    <div class="space-y-2">
                        @forelse($weeklySchedules->where('day', $day) as $schedule)
                        <div class="bg-blue-100 border border-blue-200 rounded p-2 text-sm">
                            <div class="font-semibold">{{ $schedule->room->room_name }}</div>
                            <div>{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</div>
                        </div>
                        @empty
                        <div class="text-center text-gray-500 text-xs">No schedule for this day.</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
</main>

<script>
    document.getElementById('main-schedule-filter').addEventListener('change', function() {
        const selectedScheduleId = this.value;
        const currentUrl = new URL(window.location.href);
        if (selectedScheduleId === 'all') {
            currentUrl.searchParams.delete('main_schedule_id');
        } else {
            currentUrl.searchParams.set('main_schedule_id', selectedScheduleId);
        }
        window.location.href = currentUrl.toString();
    });
</script>
@endsection