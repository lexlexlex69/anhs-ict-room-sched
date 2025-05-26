@extends('layouts.app')

@section('content')
<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <header class="flex flex-col sm:flex-row justify-between items-center bg-white text-gray-700 px-4 sm:px-6 py-4 rounded-lg shadow-md space-y-2 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <i class="fa-solid fa-calendar-alt text-lg"></i>
            <span class="text-sm font-semibold">My Reservations</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Current Time: {{ now()->format('h:i A') }}</span>
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>
        </div>
    </header>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <section class="mt-6">
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <h1 class="text-lg font-karla font-semibold text-gray-900 mb-5">My Room Reservations</h1>

            <div class="flex flex-wrap items-center gap-4 mb-6">
                <form action="{{ route('teacher.reservations.index') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status:</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All</option>
                            <option value="approved" {{ $statusFilter == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="cancelled" {{ $statusFilter == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700">Month:</label>
                        <select id="month" name="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700">Year:</label>
                        <select id="year" name="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}" {{ $selectedYear == $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-700 hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-6">
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            @if($reservations->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700">No reservations found</h3>
                <p class="text-gray-500 mt-2">No reservations match your current filters.</p>
            </div>
            @else
            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="py-3 px-6">Reference No.</th>
                            <th scope="col" class="py-3 px-6">Room</th>
                            <th scope="col" class="py-3 px-6">Date</th>
                            <th scope="col" class="py-3 px-6">Time</th>
                            <th scope="col" class="py-3 px-6">Subject</th>
                            <th scope="col" class="py-3 px-6">Status</th>
                            @if($statusFilter == 'cancelled' || $statusFilter == 'all')
                            <th scope="col" class="py-3 px-6">Remarks</th>
                            @endif
                            <th scope="col" class="py-3 px-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservations as $reservation)
                        @php
                        $reservationDateTime = \Carbon\Carbon::parse($reservation->date . ' ' . $reservation->start_time);
                        $canCancel = $reservation->status === 'approved' && \Carbon\Carbon::now()->lessThan($reservationDateTime);
                        @endphp
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $reservation->reference_number }}
                            </th>
                            <td class="py-4 px-6">
                                {{ $reservation->room->room_name }}
                            </td>
                            <td class="py-4 px-6">
                                {{ \Carbon\Carbon::parse($reservation->date)->format('M d, Y') }}
                            </td>
                            <td class="py-4 px-6">
                                {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} -
                                {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}
                            </td>
                            <td class="py-4 px-6">
                                {{ $reservation->subject ?? 'N/A' }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        @if($reservation->status == 'approved') bg-green-100 text-green-800
                                        @elseif($reservation->status == 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($reservation->status) }}
                                </span>
                            </td>
                            @if($statusFilter == 'cancelled' || $statusFilter == 'all')
                            <td class="py-4 px-6">
                                {{ $reservation->remarks ?? 'N/A' }}
                            </td>
                            @endif
                            <td class="py-4 px-6">
                                @if($canCancel)
                                <form action="{{ route('teacher.reservations.cancel', $reservation->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                    @csrf
                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                        Cancel
                                    </button>
                                </form>
                                @else
                                <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $reservations->links() }}
            </div>
            @endif
        </div>
    </section>
</main>
@endsection