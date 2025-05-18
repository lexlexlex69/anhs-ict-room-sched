@extends('layouts.app')

@section('content')

    <!-- Main Content -->
    <main class="relative z-10 flex-1 px-8 font-karla font-semibold">
        <!-- Top Bar -->
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

        <!-- List of Rooms -->
        <section class="mt-6">
            @include('_message')

            <div class="flex flex-col sm:flex-row justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900">List of Rooms</h2>

                <!-- Add Room Button -->
                <a href="{{ url('admin/room/add') }}" 
                    class="bg-blue-500 text-white px-4 py-2 mt-2 sm:mt-0 rounded-lg shadow-md hover:bg-blue-700 transition">
                    + Add Room
                </a>
            </div>

            <!-- Responsive Table Wrapper -->
            <div class="mt-4 overflow-x-auto bg-white bg-opacity-80 backdrop-blur-lg rounded-lg shadow-lg">
                <table class="w-full min-w-max table-auto border border-white border-opacity-20">
                <thead class="bg-white text-gray-700 shadow border-b border-gray-300">

                        <tr>
                            <th class="py-3 px-4 whitespace-nowrap">Room Name</th>
                            <th class="py-3 px-4 whitespace-nowrap">Capacity</th>
                            <th class="py-3 px-4 whitespace-nowrap">Availability</th>
                            <th class="py-3 px-4 whitespace-nowrap">Subject</th>
                            <th class="py-3 px-4 whitespace-nowrap">Created By</th>
                            <th class="py-3 px-4 whitespace-nowrap">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-300 text-center">
                        @if($getRecord->count() > 0)
                            @foreach($getRecord as $room)
                                <tr>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $room->room_name }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $room->capacity }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-3 py-1 rounded-md text-white 
                                            {{ $room->availability == 'Available' ? 'bg-green-500' : 
                                               ($room->availability == 'Occupied' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                            {{ $room->availability }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $room->subject }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $room->created_by_name }}</td>
                                    <td class="py-3 px-4 flex space-x-2">
                                        <a href="{{ url('admin/room/edit/'.$room->id) }}" 
                                            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs sm:text-sm">
                                            Edit
                                        </a>
                                        <a href="{{ url('admin/room/delete/'.$room->id) }}" 
                                            class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-xs sm:text-sm"
                                            onclick="return confirm('Are you sure you want to delete this room?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">No rooms found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                
            </div>

            <!-- Pagination -->
            <div style="padding: 10px; float:right;" class="mt-4">
                {{ $getRecord->links() }}
            </div>

        </section>
    </main>

@endsection
