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

        <!-- Upcoming Schedules -->
        <section class="mt-6">

        @include(' _message')

            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray">Manage Teacher Schedules</h2>
                
                <!-- Filter Controls -->
                <div class="flex items-center gap-4">
                    <!-- Time Period Filter -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Filter:</span>
                        <div class="inline-flex rounded-md shadow-sm" role="group">
                            <a href="{{ url('admin/schedules/AllList?filter=all' . (request('per_page') ? '&per_page='.request('per_page') : '')) }}" 
                               class="px-4 py-2 text-sm font-medium rounded-l-lg {{ request('filter', 'all') === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                All
                            </a>
                            <a href="{{ url('admin/schedules/AllList?filter=week' . (request('per_page') ? '&per_page='.request('per_page') : '')) }}"
                               class="px-4 py-2 text-sm font-medium {{ request('filter') === 'week' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                This Week
                            </a>
                            <a href="{{ url('admin/schedules/AllList?filter=month' . (request('per_page') ? '&per_page='.request('per_page') : '')) }}"
                               class="px-4 py-2 text-sm font-medium rounded-r-lg {{ request('filter') === 'month' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                This Month
                            </a>
                        </div>
                    </div>
                    
                    <!-- Records Per Page -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Show:</span>
                        <select id="per-page-selector" class="py-1.5 px-3 text-sm border rounded-md">
                            <option value="5" {{ request('per_page', '10') == '5' ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', '10') == '15' ? 'selected' : '' }}>15</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Display filter details -->
            @if(request('filter') === 'week')
                <div class="mt-2 text-sm text-gray-600">
                    <span>Showing: This Week ({{ \Carbon\Carbon::now()->startOfWeek()->format('M d') }} - {{ \Carbon\Carbon::now()->endOfWeek()->format('M d, Y') }})</span>
                </div>
            @elseif(request('filter') === 'month')
                <div class="mt-2 text-sm text-gray-600">
                    <span>Showing: This Month ({{ \Carbon\Carbon::now()->startOfMonth()->format('M d') }} - {{ \Carbon\Carbon::now()->endOfMonth()->format('M d, Y') }})</span>
                </div>
            @endif

            <!-- Schedule Table -->
            <div class="mt-4 overflow-x-auto">
<table class="w-full bg-white bg-opacity-80 backdrop-blur-lg border border-white border-opacity-20 rounded-lg shadow-lg" id="myTable">
    <thead class="bg-blue-700 text-white ">
        <tr>
        <th class="py-3 px-4">Profile</th>
            <th class="py-3 px-4">Teacher</th>
            <th class="py-3 px-4">Subject</th>
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
    <tr class="border-b border-gray-300 text-center">
    <td class="py-3 px-4 flex justify-center items-center">
    @if(!empty($schedule->teacher->getProfilePictureUrl()))
        <img src="{{ $schedule->teacher->getProfilePictureUrl() }}" class="h-12 w-12 rounded-full">
    @else
        <div class="h-12 w-12 rounded-full flex items-center justify-center bg-gray-200 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
        </div>
    @endif
</td>

    <td class="py-3 px-4">{{ optional($schedule->teacher)->first_name }}</td> 
    <td class="py-3 px-4">{{ optional($schedule->teacher)->subject }}</td> 
    <td class="py-3 px-4">{{ optional($schedule->room)->room_name }}</td> 
    <td class="py-3 px-4">{{ \Carbon\Carbon::parse($schedule->date)->format('F j, Y') }}</td>

        <td class="py-3 px-4">
    {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - 
    {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
</td>
        
<td class="py-3 px-4">
    <span class="px-3 py-1 rounded text-white
        {{ $schedule->status == 'upcoming' ? 'bg-green-500' :  
           ($schedule->status == 'declined' ? 'bg-pink-500' :  
           ($schedule->status == 'completed' ? 'bg-red-500' : 'bg-yellow-500')) }}">
        {{ ucfirst($schedule->status) }}
    </span>
</td>

<td class="py-3 px-4">{{ $schedule->remarks ? $schedule->remarks : "-- -- --" }}</td> 


<td class="py-3 px-4 flex items-center gap-2">
    <!-- Modified Decline Button with conditional disabling -->
    @if($schedule->remarks && $schedule->remarks != "-- -- --")
        <button type="button" 
            class="flex items-center gap-1 px-3 py-1 rounded bg-gray-400 text-white cursor-not-allowed"
            disabled>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Decline
        </button>
    @else
        <button type="button" 
            class="flex items-center gap-1 px-3 py-1 rounded bg-pink-500 text-white hover:bg-pink-600 transition decline-btn"
            data-url="{{ url('admin/schedule/decline/'.$schedule->id) }}"
            onclick="showRemarksModal(this)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Decline
        </button>
    @endif

    <!-- Delete Button (unchanged) -->
    <a href="{{ url('admin/schedule/delete/'.$schedule->id) }}" 
        class="flex items-center gap-1 px-3 py-1 rounded bg-red-500 text-white hover:bg-red-600 transition"
        onclick="return confirm('Are you sure you want to delete this schedule?')">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2 0a2 2 0 00-2-2H9a2 2 0 00-2 2h10z" />
        </svg>
        Delete
    </a>
</td>


    </tr>
@endforeach

    </tbody>
</table>

            </div>

            <div style="padding: 10px; float:right;" class="mt-4">
                {{ $schedules->appends(['filter' => request('filter'), 'per_page' => request('per_page')])->links() }}
            </div>

            <div id="remarksModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Decline Remarks</h3>
                <form id="declineForm" method="POST">
                    @csrf
                    <div class="mt-2 px-7 py-3">
                        <textarea name="remarks" required 
                            class="w-full px-3 py-2 border rounded-md" 
                            placeholder="Enter decline reason..." 
                            rows="3"></textarea>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700">
                            Submit
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="ml-3 px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md shadow-sm hover:bg-gray-300">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        </section>

        <script>
        function showRemarksModal(button) {
            const url = button.getAttribute('data-url');
            const form = document.getElementById('declineForm');
            form.action = url;
            document.getElementById('remarksModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('remarksModal').classList.add('hidden');
        }

        // Handle pagination size changes
        document.getElementById('per-page-selector').addEventListener('change', function() {
            const perPage = this.value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('per_page', perPage);
            window.location.href = currentUrl.toString();
        });
    </script>
    </main>

    @endsection
