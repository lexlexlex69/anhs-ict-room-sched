@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-screen" style="background-color: #fbf9f9ff;">
    <div class="relative z-10 w-full max-w-2xl px-6 py-8 bg-white rounded-lg shadow-lg">
        <div class="text-center mb-6">
            <i class="fas fa-check-circle text-5xl text-green-500 mb-4"></i>
            <h1 class="text-2xl font-bold">Reservation Confirmed!</h1>
            <p class="text-gray-600 mt-2">Your room reservation has been submitted successfully.</p>
        </div>

        <div class="border border-gray-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Reservation Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Reference Number</p>
                    <p class="font-medium">{{ $reservation->reference_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Room</p>
                    <p class="font-medium">{{ $reservation->room->room_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date</p>
                    <p class="font-medium">{{ $reservation->date->format('F j, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Time</p>
                    <p class="font-medium">
                        {{ \Carbon\Carbon::parse($reservation->start_time)->format('g:i A') }} -
                        {{ \Carbon\Carbon::parse($reservation->end_time)->format('g:i A') }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $reservation->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Subject</p>
                    <p class="font-medium">{{ $reservation->subject }}</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Please keep this reference number for your records.
                        You'll need it to check your reservation status.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="/" class="inline-block px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                Return to Home
            </a>
        </div>
    </div>
</div>
@endsection