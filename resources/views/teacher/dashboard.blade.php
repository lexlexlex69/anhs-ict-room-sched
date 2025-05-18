@extends('layouts.app')

@section('content')

<!-- Main Content -->
<main class="relative z-10 flex-1 px-4 sm:px-6 md:px-8 font-karla font-semibold">

    <!-- Top Bar -->
    <header class="flex flex-col sm:flex-row justify-between items-center bg-white text-gray-700  px-4 sm:px-6 py-4 rounded-lg shadow-md space-y-2 sm:space-y-0">
        <div class="flex items-center space-x-4">
            <i class="fa-solid fa-calendar-days text-lg"></i>
            <span id="current-date" class="text-sm">Loading date...</span>
            <span id="current-time" class="text-sm font-semibold">Loading time...</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm font-semibold">Welcome, {{ Auth::user()->first_name }}!</span>

            <!-- Notification Icon with Badge -->
            <div class="relative">
                <i class="fa-solid fa-bell text-lg cursor-pointer text-gray-700" id="notification-icon"></i>
                <span id="notification-badge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full hidden">0</span>

                <!-- Notification Dropdown -->
                <div id="notification-dropdown" class="absolute right-0 mt-2 w-64 bg-white shadow-lg rounded-lg z-50 hidden border border-gray-200">
                    <div class="p-3">
                        <p class="text-sm font-semibold text-gray-700">Notifications</p>
                        <ul id="notification-list" class="mt-2 max-h-60 overflow-y-auto text-gray-800"></ul>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Upcoming Schedules -->
    <section class="mt-6">

        @include('_message')

        <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-3 md:space-y-0">
            <h2 class="text-xl font-semibold text-gray-700">Manage Teacher Schedules</h2>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Status Filter Dropdown -->
                <div class="relative">
                    <button id="statusDropdownButton" class="flex items-center gap-2 bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span>Status: {{ ucfirst(request('status', 'All')) }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="statusDropdown" class="hidden absolute right-0 mt-1 w-44 bg-white border border-gray-300 rounded-md shadow-lg z-10">
                        <a href="{{ route('teacher.dashboard', ['status' => 'all', 'filter' => request('filter', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ !request('status') || request('status') == 'all' ? 'bg-blue-50 text-blue-600' : '' }}">
                           All
                        </a>
                        <a href="{{ route('teacher.dashboard', ['status' => 'upcoming', 'filter' => request('filter', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('status') == 'upcoming' ? 'bg-blue-50 text-blue-600' : '' }}">
                           Upcoming
                        </a>
                        <a href="{{ route('teacher.dashboard', ['status' => 'ongoing', 'filter' => request('filter', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('status') == 'ongoing' ? 'bg-blue-50 text-blue-600' : '' }}">
                           Ongoing
                        </a>
                        <a href="{{ route('teacher.dashboard', ['status' => 'completed', 'filter' => request('filter', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('status') == 'completed' ? 'bg-blue-50 text-blue-600' : '' }}">
                           Completed
                        </a>
                    </div>
                </div>

                <!-- Time Filter Dropdown -->
                <div class="relative">
                    <button id="timeDropdownButton" class="flex items-center gap-2 bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span>Time: {{ ucfirst(request('filter', 'All')) }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="timeDropdown" class="hidden absolute right-0 mt-1 w-44 bg-white border border-gray-300 rounded-md shadow-lg z-10">
                        <a href="{{ route('teacher.dashboard', ['filter' => 'all', 'status' => request('status', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ !request('filter') || request('filter') == 'all' ? 'bg-blue-50 text-blue-600' : '' }}">
                           All
                        </a>
                        <a href="{{ route('teacher.dashboard', ['filter' => 'week', 'status' => request('status', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('filter') == 'week' ? 'bg-blue-50 text-blue-600' : '' }}">
                           This Week
                        </a>
                        <a href="{{ route('teacher.dashboard', ['filter' => 'month', 'status' => request('status', 'all'), 'per_page' => request('per_page', 10)]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('filter') == 'month' ? 'bg-blue-50 text-blue-600' : '' }}">
                           This Month
                        </a>
                    </div>
                </div>

                <!-- Per Page Dropdown -->
                <div class="relative">
                    <button id="perPageDropdownButton" class="flex items-center gap-2 bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span>Show: {{ request('per_page', 10) }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="perPageDropdown" class="hidden absolute right-0 mt-1 w-28 bg-white border border-gray-300 rounded-md shadow-lg z-10">
                        <a href="{{ route('teacher.dashboard', ['per_page' => 5, 'status' => request('status', 'all'), 'filter' => request('filter', 'all')]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('per_page') == 5 ? 'bg-blue-50 text-blue-600' : '' }}">
                           5
                        </a>
                        <a href="{{ route('teacher.dashboard', ['per_page' => 10, 'status' => request('status', 'all'), 'filter' => request('filter', 'all')]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('per_page') == 10 || !request('per_page') ? 'bg-blue-50 text-blue-600' : '' }}">
                           10
                        </a>
                        <a href="{{ route('teacher.dashboard', ['per_page' => 15, 'status' => request('status', 'all'), 'filter' => request('filter', 'all')]) }}" 
                           class="block px-4 py-2 text-sm hover:bg-gray-100 {{ request('per_page') == 15 ? 'bg-blue-50 text-blue-600' : '' }}">
                           15
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Table -->
        <div class="mt-4">
            <!-- Scrollable Table for small screens -->
            <div class="overflow-x-auto">
                <table class="w-full bg-white bg-opacity-80 backdrop-blur-lg border border-white border-opacity-20 rounded-lg shadow-lg text-sm sm:text-base">
                    <thead class="bg-white text-gray-700 shadow border-b border-gray-300">
                        <tr>
                            <th class="py-3 px-4">Room</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Time</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Remarks</th>
                            <th class="py-3 px-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr class="border-b border-gray-300 text-center hidden sm:table-row"> 
                                <td class="py-3 px-4">{{ optional($schedule->room)->room_name }}</td> 
                                <td class="py-3 px-4">{{ \Carbon\Carbon::parse($schedule->date)->format('F j, Y') }}</td>

                                <td class="py-3 px-4">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-3 py-1 rounded text-white text-xs sm:text-sm 
                                        {{ $schedule->status == 'upcoming' ? 'bg-green-500' : 
                                          ($schedule->status == 'ongoing' ? 'bg-yellow-500' : 
                                           ($schedule->status == 'declined' ? 'bg-pink-500' : 'bg-red-500')) }}">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ $schedule->remarks ? $schedule->remarks : "-- -- --" }}</td> 
                                <td class="py-3 px-4">
                                    <a href="{{ url('teacher/schedule/delete/'.$schedule->id) }}" 
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-xs sm:text-sm"
                                        onclick="return confirm('Are you sure you want to delete this schedule?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>

                            <!-- Mobile View: Card Layout -->
                            <tr class="sm:hidden">
                                <td colspan="6" class="p-4">
                                    <div class="bg-white p-3 rounded-lg shadow">
                                        <p><strong>Room:</strong> {{ optional($schedule->room)->room_name }}</p>
                                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($schedule->date)->format('F j, Y') }}</p>
                                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</p>
                                        <p class="mt-2">
                                            <span class="px-3 py-1 rounded text-white text-xs
                                                {{ $schedule->status == 'upcoming' ? 'bg-green-500' : 
                                                  ($schedule->status == 'ongoing' ? 'bg-yellow-500' : 
                                                   ($schedule->status == 'declined' ? 'bg-pink-500' : 'bg-red-500')) }}">
                                                {{ ucfirst($schedule->status) }}
                                            </span>
                                        </p>
                                        <p><strong>Remarks:</strong> {{ $schedule->remarks ? $schedule->remarks : "-- -- --" }}</p>
                                        <div class="mt-3">
                                            <a href="{{ url('teacher/schedule/delete/'.$schedule->id) }}" 
                                               class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-xs"
                                               onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        
                        @if(count($schedules) == 0)
                            <tr class="border-b border-gray-300">
                                <td colspan="6" class="py-6 text-center text-gray-500">No schedules found</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Enhanced Pagination -->
            <div class="mt-5 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Showing {{ $schedules->firstItem() ?? 0 }} to {{ $schedules->lastItem() ?? 0 }} of {{ $schedules->total() }} schedules
                </div>
                <div>
                    {{ $schedules->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </section>
</main>

<!-- JavaScript for Per Page Selector -->
<script>
    document.getElementById('perPageSelector').addEventListener('change', function() {
        const perPage = this.value;
        const currentUrl = new URL(window.location.href);
        
        // Update the per_page parameter
        currentUrl.searchParams.set('per_page', perPage);
        
        // Navigate to the updated URL
        window.location.href = currentUrl.toString();
    });
</script>

<!-- JavaScript for Dropdowns -->
<script>
    // Function to toggle dropdown visibility
    function toggleDropdown(buttonId, dropdownId) {
        const button = document.getElementById(buttonId);
        const dropdown = document.getElementById(dropdownId);
        
        // Close all dropdowns first
        document.querySelectorAll('[id$="Dropdown"]').forEach(el => {
            if (el.id !== dropdownId) el.classList.add('hidden');
        });
        
        button.addEventListener('click', function() {
            dropdown.classList.toggle('hidden');
        });
    }
    
    // Initialize all dropdowns
    toggleDropdown('statusDropdownButton', 'statusDropdown');
    toggleDropdown('timeDropdownButton', 'timeDropdown');
    toggleDropdown('perPageDropdownButton', 'perPageDropdown');
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('[id$="Dropdown"]');
        const buttons = document.querySelectorAll('[id$="DropdownButton"]');
        
        let clickedOnDropdown = false;
        buttons.forEach(button => {
            if (button.contains(event.target)) {
                clickedOnDropdown = true;
            }
        });
        
        if (!clickedOnDropdown) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });
</script>

@endsection
