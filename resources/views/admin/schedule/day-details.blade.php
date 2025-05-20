@php use Carbon\Carbon; @endphp

<div class="space-y-4">
    @if($schedules->count() > 0)
    <div>
        <h4 class="font-bold text-green-700 mb-2">Weekly Schedules</h4>
        <div class="space-y-2">
            @foreach($schedules as $schedule)
            <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                <div class="font-bold text-green-700">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    {{ $schedule->teacher->first_name }} {{ $schedule->teacher->last_name }}
                </div>
                <div class="text-sm mt-1">
                    <span class="font-semibold">Time:</span>
                    {{ Carbon::parse($schedule->start_time)->format('g:i A') }} -
                    {{ Carbon::parse($schedule->end_time)->format('g:i A') }}
                </div>
                <div class="text-sm">
                    <span class="font-semibold">Room:</span> {{ $schedule->room->room_name }}
                </div>
                <div class="text-sm">
                    <span class="font-semibold">Subject:</span> {{ $schedule->room->subject ?? 'N/A' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($reservations->count() > 0)
    <div>
        <h4 class="font-bold text-blue-700 mb-2">Reservations</h4>
        <div class="space-y-2">
            @foreach($reservations as $reservation)
            <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                <div class="font-bold text-blue-700">
                    <i class="fas fa-user mr-2"></i>
                    {{ $reservation->teacher_name }}
                </div>
                <div class="text-sm mt-1">
                    <span class="font-semibold">Time:</span>
                    {{ Carbon::parse($reservation->start_time)->format('g:i A') }} -
                    {{ Carbon::parse($reservation->end_time)->format('g:i A') }}
                </div>
                <div class="text-sm">
                    <span class="font-semibold">Room:</span> {{ $reservation->room->room_name }}
                </div>
                <div class="text-sm">
                    <span class="font-semibold">Subject:</span> {{ $reservation->subject }}
                </div>
                @if($reservation->reference_number)
                <div class="text-sm">
                    <span class="font-semibold">Reference:</span> {{ $reservation->reference_number }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($schedules->count() == 0 && $reservations->count() == 0)
    <div class="text-center text-gray-500 py-4">
        No schedules or reservations for this day
    </div>
    @endif
</div>