@extends('layouts.app')

@section('content')

<main class="relative z-10 flex-1 px-8 font-karla font-semibold">
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

    <!-- Schedule Section -->
    <section class="mt-6">

        @include(' _message')
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            <h1 class="text-lg font-karla font-semibold text-gray-900 mb-4">All Schedule</h2>


                <!-- Status Indicators -->
                <div class="flex items-center space-x-4 text-sm font-medium my-7">
                    <div class="flex items-center space-x-1">
                        <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                        <span>Upcoming</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
                        <span>Ongoing</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                        <span>Completed</span>
                    </div>
                </div>

                <!-- Teacher Info -->
                <div class="flex items-center space-x-4 mb-4">
                    @if(!empty(Auth::user()->getProfilePictureUrl()) && !Str::contains(Auth::user()->getProfilePictureUrl(), 'default-profile.png'))
                    <img src="{{ Auth::user()->getProfilePictureUrl() }}"
                        class="w-32 h-32 rounded-full object-cover border shadow-md" alt="Profile Picture">
                    @else
                    <div class="w-32 h-32 rounded-full bg-gray-200 border shadow-md flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    @endif
                    <div>
                        <h3 class="font-karla font-semibold">{{ Auth::user()->first_name }}</h3>
                        <p class="text-sm text-gray-600">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="flex justify-between items-center mb-4">
                    <div class="flex space-x-2">
                        <select id="month-selector" class="p-2 border rounded-md bg-white">
                            @foreach (["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"] as $index => $month)
                            <option value="{{ $index }}">{{ $month }}</option>
                            @endforeach
                        </select>
                        <select id="year-selector" class="p-2 border rounded-md bg-white">
                            @for ($year = date('Y') - 5; $year <= date('Y') + 5; $year++)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                        </select>
                    </div>
                    <button id="openModalBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Create Schedule +
                    </button>
                </div>

                <!-- Calendar -->
                <input type="hidden" id="schedule-data" value="{{ json_encode($schedules) }}">
                <div class="grid grid-cols-7 gap-4 mt-4 text-center">
                    <div class="font-bold">Sun</div>
                    <div class="font-bold">Mon</div>
                    <div class="font-bold">Tue</div>
                    <div class="font-bold">Wed</div>
                    <div class="font-bold">Thu</div>
                    <div class="font-bold">Fri</div>
                    <div class="font-bold">Sat</div>
                    <div id="calendar-grid" class="grid grid-cols-7 gap-4 col-span-7"></div>
                </div>
        </div>
    </section>

    <!-- Create Schedule Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
        <div class="w-full max-w-lg p-6 bg-white border-2 border-blue-600 rounded-lg shadow-lg">
            <!-- Modal Header -->
            <div class="flex justify-between items-center border-b border-blue-300 pb-2 mb-4">
                <h2 class="text-xl font-bold text-blue-700">CREATE SCHEDULE</h2>
                <button id="closeModalBtn" class="text-blue-700 hover:text-red-500 text-lg">&times;</button>
            </div>

            <!-- Modal Body -->
            <!-- Schedule Form -->
            <form action="{{ route('teacher.schedule.store') }}" method="POST">
                @csrf
                <div class="space-y-4">


                    <!-- Room Selection -->
                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Room</label>
                        <select name="room_id" id="roomSelect" class="p-2 border border-gray-300 rounded-md bg-blue-600 text-white" required>
                            <option value="">Pick a room</option>
                            @foreach ($getRoom as $room)
                            <option value="{{ $room->id }}">{{ $room->room_name }} ({{ $room->capacity }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Section -->
                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Date</label>
                        <input type="date" name="date" id="scheduleDate" class="p-2 border border-gray-300 rounded-md bg-white text-gray-700 w-full" required>

                    </div>

                    <!-- Time Section -->
                    <div class="flex flex-col">
                        <label class="text-sm font-bold text-gray-700">Time</label>
                        <div class="flex space-x-2">
                            <select name="start_time" id="startTime" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>

                                <option value="07:00">7:00 AM</option>
                                <option value="08:00">8:00 AM</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <!-- <option value="12:00">12:00 PM</option> //lunchbreak
                                <option value="13:00">1:00 PM</option> -->
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="17:00">5:00 PM</option>
                            </select>

                            <select name="end_time" id="endTime" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>

                                <option value="07:00">7:00 AM</option>
                                <option value="08:00">8:00 AM</option>
                                <option value="09:00">9:00 AM</option>
                                <option value="10:00">10:00 AM</option>
                                <option value="11:00">11:00 AM</option>
                                <!-- <option value="12:00">12:00 PM</option> //lunchbreak
                                <option value="13:00">1:00 PM</option> -->
                                <option value="14:00">2:00 PM</option>
                                <option value="15:00">3:00 PM</option>
                                <option value="16:00">4:00 PM</option>
                                <option value="17:00">5:00 PM</option>
                            </select>
                        </div>
                    </div>



                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end space-x-4 mt-6">
                    <button id="closeModalBtn2" type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>

</main>

<!-- JavaScript to Handle Datepicker & Modal -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const openModalBtn = document.getElementById("openModalBtn");
        const closeModalBtn = document.getElementById("closeModalBtn");
        const closeModalBtn2 = document.getElementById("closeModalBtn2");
        const scheduleModal = document.getElementById("scheduleModal");

        openModalBtn.addEventListener("click", function() {
            scheduleModal.classList.remove("hidden");
        });

        closeModalBtn.addEventListener("click", function() {
            scheduleModal.classList.add("hidden");
        });

        closeModalBtn2.addEventListener("click", function() {
            scheduleModal.classList.add("hidden");
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const today = new Date().toISOString().split("T")[0];
        const scheduleDate = document.getElementById("scheduleDate");
        const startTime = document.getElementById("startTime");
        const endTime = document.getElementById("endTime");

        // Set the minimum date to today
        scheduleDate.setAttribute("min", today);

        // When the date changes
        scheduleDate.addEventListener("change", function() {
            const selectedDate = this.value;
            const now = new Date();
            const currentHour = now.getHours();

            // Enable all time options initially
            [...startTime.options, ...endTime.options].forEach(opt => opt.disabled = false);

            if (selectedDate === today) {
                [...startTime.options].forEach(option => {
                    const optionHour = parseInt(option.value.split(':')[0]);
                    if (optionHour < currentHour) {
                        option.disabled = true;
                    }
                });

                [...endTime.options].forEach(option => {
                    const optionHour = parseInt(option.value.split(':')[0]);
                    if (optionHour <= currentHour) {
                        option.disabled = true;
                    }
                });
            }
        });
    });
</script>


<!-- JavaScript for Calendar -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const calendarGrid = document.getElementById("calendar-grid");
        const scheduleData = JSON.parse(document.getElementById("schedule-data").value || "[]");
        const monthSelector = document.getElementById("month-selector");
        const yearSelector = document.getElementById("year-selector");

        function updateCalendar() {
            const month = parseInt(monthSelector.value);
            const year = parseInt(yearSelector.value);
            calendarGrid.innerHTML = "";

            const firstDay = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                calendarGrid.innerHTML += `<div></div>`;
            }

            for (let day = 1; day <= totalDays; day++) {
                let scheduleHTML = "";
                scheduleData.forEach(schedule => {
                    const scheduleDate = new Date(schedule.date);
                    if (scheduleDate.getFullYear() === year && scheduleDate.getMonth() === month && scheduleDate.getDate() === day) {
                        const startTime = formatTime(schedule.start_time);
                        const endTime = formatTime(schedule.end_time);
                        scheduleHTML += `
                       <div class="
                            text-white text-xs mt-2 p-1 rounded 
                            ${schedule.status === 'upcoming' ? 'bg-green-500' : 
                            schedule.status === 'ongoing' ? 'bg-yellow-500' : 
                            schedule.status === 'completed' ? 'bg-red-500' : 'bg-red-500'}">
                            ${startTime} - ${endTime} (${schedule.room_name}) (${schedule.subject})
                        </div>

                    `;
                    }
                });
                calendarGrid.innerHTML += `<div class="bg-white text-gray p-4 rounded-lg">${day}${scheduleHTML}</div>`;
            }
        }

        function formatTime(timeString) {
            const [hours, minutes] = timeString.split(':');
            let hoursInt = parseInt(hours);
            const ampm = hoursInt >= 12 ? 'PM' : 'AM';
            hoursInt = hoursInt % 12 || 12;
            return `${hoursInt}:${minutes} ${ampm}`;
        }

        monthSelector.addEventListener("change", updateCalendar);
        yearSelector.addEventListener("change", updateCalendar);

        monthSelector.value = new Date().getMonth();
        yearSelector.value = new Date().getFullYear();
        updateCalendar();
    });
</script>



<!-- Add this script section after your form in the Blade view -->
<script>
    var existingSchedules = @json($allRoomSchedules);

    // Initialize original text for options on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeOriginalText('startTime');
        initializeOriginalText('endTime');
    });

    document.getElementById('roomSelect').addEventListener('change', updateTimeOptions);
    document.getElementById('scheduleDate').addEventListener('change', updateTimeOptions);
    document.getElementById('startTime').addEventListener('change', updateTimeOptions);

    function updateTimeOptions() {
        var roomId = document.getElementById('roomSelect').value;
        var date = document.getElementById('scheduleDate').value;
        var startTime = document.getElementById('startTime').value;

        resetTimeOptions('startTime');
        resetTimeOptions('endTime');

        if (!roomId || !date) return;

        // Disable times based on existing schedules
        var filteredSchedules = existingSchedules.filter(schedule =>
            schedule.room_id == roomId && schedule.date === date
        );
        disableTimeOptions(filteredSchedules);

        // Disable invalid end times based on selected start time
        if (startTime) {
            const startHour = convertTimeToNumber(startTime);
            const endSelect = document.getElementById('endTime');

            Array.from(endSelect.options).forEach(option => {
                const endHour = convertTimeToNumber(option.value);
                if (endHour <= startHour && !(endHour === 24 && startHour < 24)) {
                    option.disabled = true;
                    option.textContent = 'Not Available - ' + option.dataset.originalText;
                    option.style.backgroundColor = '#f0f0f0';
                }
            });
        }
    }

    function resetTimeOptions(selectId) {
        var select = document.getElementById(selectId);
        Array.from(select.options).forEach(option => {
            option.disabled = false;
            option.style.backgroundColor = '';
            option.textContent = option.dataset.originalText; // Restore original text
        });
    }

    function disableTimeOptions(schedules) {
        const startSelect = document.getElementById('startTime');
        const endSelect = document.getElementById('endTime');

        schedules.forEach(schedule => {
            const existingStart = schedule.start_time.slice(0, 5);
            const existingEnd = schedule.end_time.slice(0, 5);
            const isOvernight = existingEnd <= existingStart;

            // Disable start times
            Array.from(startSelect.options).forEach(option => {
                const time = option.value;
                let shouldDisable = false;

                if (isOvernight) {
                    shouldDisable = (time >= existingStart || time < existingEnd);
                } else {
                    shouldDisable = (time >= existingStart && time < existingEnd);
                }

                if (shouldDisable) {
                    option.disabled = true;
                    option.textContent = 'Not Available - ' + option.dataset.originalText;
                    option.style.backgroundColor = '#f0f0f0';
                }
            });

            // Disable end times
            Array.from(endSelect.options).forEach(option => {
                const time = option.value;
                let shouldDisable = false;

                if (isOvernight) {
                    shouldDisable = (time > existingStart || time <= existingEnd);
                } else {
                    shouldDisable = (time > existingStart && time <= existingEnd);
                }

                if (shouldDisable) {
                    option.disabled = true;
                    option.textContent = 'Not Available - ' + option.dataset.originalText;
                    option.style.backgroundColor = '#f0f0f0';
                }
            });
        });
    }

    // Store original option text
    function initializeOriginalText(selectId) {
        const select = document.getElementById(selectId);
        Array.from(select.options).forEach(option => {
            option.dataset.originalText = option.textContent;
        });
    }

    function convertTimeToNumber(time) {
        const [hours] = time.split(':');
        return hours === '00' ? 24 : parseInt(hours, 10);
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const currentDateElement = document.getElementById("current-date");
        const currentTimeElement = document.getElementById("current-time");

        function updateDateTime() {
            const now = new Date();
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            currentDateElement.textContent = now.toLocaleDateString('en-US', dateOptions);
            currentTimeElement.textContent = now.toLocaleTimeString('en-US');
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    });
</script>

@endsection