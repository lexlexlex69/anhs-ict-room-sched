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
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <h1 class="text-lg font-karla font-semibold text-gray-900 mb-5">
                @if($isNonIctTeacher)
                My Today's Approved Room Reservations
                @else
                My Today's Schedules
                @endif
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 my-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total</p>
                            <p class="text-2xl font-bold">{{ $statusCounts['total'] }}</p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-full">
                            <i class="fas fa-calendar text-gray-500"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Upcoming</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $statusCounts['upcoming'] }}</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-clock text-yellow-500"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Ongoing</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $statusCounts['ongoing'] }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-chalkboard-teacher text-blue-500"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Completed</p>
                            <p class="text-2xl font-bold text-green-600">{{ $statusCounts['completed'] }}</p>
                        </div>
                        <div class="bg-green-200 p-3 rounded-full">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if($schedules->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-calendar-check text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700">
                    @if($isNonIctTeacher)
                    No approved reservations for today.
                    @else
                    No schedules for today.
                    @endif
                </h3>
                <p class="text-gray-500 mt-2">
                    @if($isNonIctTeacher)
                    You haven't reserved any rooms for today, or your reservations are not yet approved.
                    @else
                    You don't have any classes scheduled for today.
                    @endif
                </p>
            </div>
            @else
            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            @if($isNonIctTeacher)
                            <th scope="col" class="py-3 px-6">Reference No.</th>
                            @endif
                            <th scope="col" class="py-3 px-6">Room</th>
                            <th scope="col" class="py-3 px-6">Time</th>
                            <th scope="col" class="py-3 px-6">Subject</th>
                            @if(!$isNonIctTeacher)
                            <th scope="col" class="py-3 px-6">Schedule Type</th>
                            @else
                            <th scope="col" class="py-3 px-6">Reservation Status</th>
                            @endif
                            <th scope="col" class="py-3 px-6">Time Status</th> {{-- This column is for Upcoming/Ongoing/Completed --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $item) {{-- $schedules now holds both schedules/reservations --}}
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            @if($isNonIctTeacher)
                            <td class="py-4 px-6">{{ $item->reference_number }}</td>
                            @endif
                            <td class="py-4 px-6">{{ $item->room->room_name ?? 'N/A' }}</td>
                            <td class="py-4 px-6">
                                {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }} -
                                {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
                            </td>
                            <td class="py-4 px-6">{{ $item->subject ?? 'N/A' }}</td>
                            @if(!$isNonIctTeacher)
                            <td class="py-4 px-6">
                                @if(isset($item->day)) Fixed
                                @else One-Time @endif
                            </td>
                            @else
                            <td class="py-4 px-6">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($item->status == 'approved') bg-green-100 text-green-800
                                        @elseif($item->status == 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            @endif
                            <td class="py-4 px-6">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $item->status_badge }}">
                                    {{ ucfirst($item->status_indicator) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh every minute to update statuses
        setInterval(() => {
            window.location.reload();
        }, 60000);
    });
</script>
@endsection