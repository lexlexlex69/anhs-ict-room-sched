@extends('layouts.app')

@section('content')

<!-- Main Content -->
<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
    <header class="flex justify-between items-center bg-white text-gray px-6 py-4 rounded-lg shadow-md">
        <div class="flex items-center space-x-5">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <span id="current-date" class="text-sm">Loading date...</span>
            <span id="current-time" class="text-sm font-semibold">Loading time...</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>

            <!-- Notification Icon with Badge -->
            <div class="relative">
                <!-- Notification Bell Icon -->
                <i class="fa-solid fa-bell text-lg cursor-pointer text-gray" id="notification-icon"></i>

                <!-- Notification Badge -->
                <span id="notification-badge"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">
                    0
                </span>

                <!-- Notification Dropdown -->
                <div id="notification-dropdown"
                    class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg z-50 hidden border border-gray-200">
                    <div class="p-3">
                        <p class="text-sm font-semibold text-gray-700">Notifications</p>
                        <ul id="notification-list" class="mt-2 max-h-60 overflow-y-auto text-gray-800"></ul>

                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="mt-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Room Reservations</h1>
        </div>

        @include('_message')

        <div class="space-y-8">
            @foreach($reservations as $date => $dateReservations)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-blue-600 px-4 py-3 text-white">
                    <h2 class="text-lg font-semibold">
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    </h2>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($dateReservations as $reservation)
                    <div class="p-4 hover:bg-gray-50 transition">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div class="mb-3 sm:mb-0">
                                <div class="flex items-center space-x-3">
                                    <span class="font-semibold text-lg">{{ $reservation->room->room_name }}</span>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                    @if($reservation->status == 'approved') bg-green-100 text-green-800
                                    @elseif($reservation->status == 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($reservation->status) }}
                                    </span>
                                </div>
                                <div class="text-gray-600 mt-1">
                                    <i class="far fa-clock mr-1"></i>
                                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} -
                                    {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                                </div>
                                <div class="mt-1">
                                    <span class="font-medium">Reserved by:</span> {{ $reservation->teacher_name }}
                                </div>
                                <div>
                                    <span class="font-medium">Subject:</span> {{ $reservation->subject }}
                                </div>
                                @if($reservation->reference_number)
                                <div class="mt-1 text-sm">
                                    <span class="font-medium">Ref #:</span> {{ $reservation->reference_number }}
                                </div>
                                @endif
                            </div>

                            @if($reservation->status == 'approved')
                            <div class="flex space-x-2">
                                <button onclick="openCancelModal('{{ $reservation->id }}')"
                                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm">
                                    Cancel
                                </button>
                            </div>
                            @elseif($reservation->remarks)
                            <div class="text-sm text-gray-500 mt-2 sm:mt-0">
                                <span class="font-medium">Remarks:</span> {{ $reservation->remarks }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- Cancel Reservation Modal -->
        <div id="cancelModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h2 class="text-xl font-bold">Cancel Reservation</h2>
                    <button onclick="closeCancelModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
                </div>

                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason for cancellation</label>
                        <textarea name="remarks" rows="3" class="w-full border rounded-md p-2" required></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCancelModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Back
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                            Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>


</main>
<script>
    function openCancelModal(reservationId) {
        const form = document.getElementById('cancelForm');
        form.action = `/admin/reservations/${reservationId}/cancel`;
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }
</script>

@endsection