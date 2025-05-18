@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-8 rounded-lg shadow-md text-center">
        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>

        <h2 class="mt-3 text-xl font-bold text-gray-900">Reservation Confirmed!</h2>

        <div class="mt-4 bg-gray-50 p-4 rounded-md">
            <h3 class="text-lg font-medium">Your Reservation Details</h3>
            <div class="mt-2 space-y-1 text-sm">
                <p><strong>Reference Number:</strong> {{ $reservation->reference_number }}</p>
                <p><strong>Room:</strong> {{ $reservation->room->room_name }}</p>
                <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($reservation->date)->format('F j, Y') }}</p>
                <p><strong>Time:</strong> {{ date('g:i A', strtotime($reservation->start_time)) }} - {{ date('g:i A', strtotime($reservation->end_time)) }}</p>
                <p><strong>Name:</strong> {{ $reservation->name }}</p>
                <p><strong>Subject:</strong> {{ $reservation->subject }}</p>
            </div>
        </div>

        <div class="mt-6">
            <a href="/" class="text-blue-600 hover:text-blue-800">Return to homepage</a>
        </div>
    </div>
</div>
@endsection