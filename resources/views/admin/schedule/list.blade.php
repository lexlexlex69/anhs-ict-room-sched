@extends('layouts.app')

@section('content')

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

    <!-- Schedule Section -->
    <section class="mt-6">
        <div class="bg-white bg-opacity-30 backdrop-blur-lg p-6 rounded-lg shadow-lg">
            
            <h2 class="text-lg font-karla font-semibold text-gray-900 mb-4">Confirmed Schedule</h2>

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


            <!-- Calendar Navigation -->
            <div class="flex justify-between items-center mt-4">
                <button id="prev-month" class="px-2 py-1 bg-blue-500 text-white rounded">◀ Prev</button>
                
                <div class="flex items-center space-x-2">
                    <select id="month-select" class="p-1 border rounded bg-white text-gray-900">
                        <option value="0">January</option>
                        <option value="1">February</option>
                        <option value="2">March</option>
                        <option value="3">April</option>
                        <option value="4">May</option>
                        <option value="5">June</option>
                        <option value="6">July</option>
                        <option value="7">August</option>
                        <option value="8">September</option>
                        <option value="9">October</option>
                        <option value="10">November</option>
                        <option value="11">December</option>
                    </select>

                    <select id="year-select" class="p-1 border rounded bg-white text-gray-900">
                        <!-- JavaScript will populate this -->
                    </select>
                </div>

                <button id="next-month" class="px-2 py-1 bg-blue-500 text-white rounded">Next ▶</button>
            </div>

            <!-- Calendar -->
            <div class="mt-4">
                <h3 id="calendar-title" class="text-xl font-semibold">📅 Loading...</h3>
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
        </div>
    </section>

</main>

<!-- JavaScript for Calendar -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const monthSelect = document.getElementById("month-select");
    const yearSelect = document.getElementById("year-select");
    const calendarGrid = document.getElementById("calendar-grid");
    const scheduleData = JSON.parse(document.getElementById("schedule-data").value || "[]");
    const calendarTitle = document.getElementById("calendar-title");
    const prevMonthBtn = document.getElementById("prev-month");
    const nextMonthBtn = document.getElementById("next-month");

    const now = new Date();
    let currentMonth = now.getMonth();
    let currentYear = now.getFullYear();

    // Set the default selected month
    monthSelect.value = currentMonth;

    // Populate year dropdown (range from 10 years before to 10 years after)
    const startYear = currentYear - 10;
    const endYear = currentYear + 10;
    for (let year = startYear; year <= endYear; year++) {
        const option = document.createElement("option");
        option.value = year;
        option.textContent = year;
        if (year === currentYear) option.selected = true;
        yearSelect.appendChild(option);
    }

    // Function to determine schedule status based on current time
    function getScheduleStatus(scheduleDate, startTime, endTime) {
        const now = new Date();
        const today = now.toISOString().split('T')[0]; // YYYY-MM-DD format
        
        // Create proper date objects for accurate comparison
        const scheduleDay = new Date(scheduleDate);
        scheduleDay.setHours(0, 0, 0, 0); // Reset time part
        
        const nowDay = new Date(now);
        nowDay.setHours(0, 0, 0, 0); // Reset time part
        
        // Parse hours and minutes for more accurate comparison
        const [startHour, startMin] = startTime.split(':').map(num => parseInt(num, 10));
        const [endHour, endMin] = endTime.split(':').map(num => parseInt(num, 10));
        
        const currentHour = now.getHours();
        const currentMin = now.getMinutes();
        
        // Check if schedule is today
        const isToday = scheduleDay.getTime() === nowDay.getTime();
        
        // Helpers for time comparison
        const startTimeMinutes = startHour * 60 + startMin;
        const endTimeMinutes = endHour * 60 + endMin;
        const currentTimeMinutes = currentHour * 60 + currentMin;
        
        if (scheduleDay < nowDay || (isToday && endTimeMinutes <= currentTimeMinutes)) {
            return 'completed'; // Past schedule
        } else if (isToday && startTimeMinutes <= currentTimeMinutes && endTimeMinutes > currentTimeMinutes) {
            return 'ongoing'; // Currently happening
        } else {
            return 'upcoming'; // Future schedule
        }
    }

    // Function to update the calendar
    function updateCalendar(month, year) {
        calendarGrid.innerHTML = ""; // Clear previous calendar
        calendarTitle.textContent = `${monthSelect.options[month].text} ${year}`;
        monthSelect.value = month;
        yearSelect.value = year;

        const firstDay = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            calendarGrid.innerHTML += `<div></div>`; // Empty spaces for first row
        }

        const currentDate = new Date();

        for (let day = 1; day <= totalDays; day++) {
            const isToday = day === currentDate.getDate() && month === currentDate.getMonth() && year === currentDate.getFullYear() ? "border-red-200 border-2" : "";
            let scheduleHTML = "";

            // Find schedules for this day
            scheduleData.forEach(schedule => {
                const scheduleDate = new Date(schedule.date);
                if (scheduleDate.getFullYear() === year && scheduleDate.getMonth() === month && scheduleDate.getDate() === day) {
                    const startTime = convertTo12Hour(schedule.start_time);
                    const endTime = convertTo12Hour(schedule.end_time);
                    
                    // Determine status based on current time, not the API value
                    const currentStatus = getScheduleStatus(schedule.date, schedule.start_time, schedule.end_time);
                    
                    // Map status to color class
                    const statusClass = 
                        currentStatus === 'upcoming' ? 'bg-green-500' :
                        currentStatus === 'ongoing' ? 'bg-yellow-500' : 
                        'bg-red-500'; // completed
                    
                    scheduleHTML += `
                        <div class="text-white text-xs mt-2 p-1 rounded ${statusClass}">
                            ${startTime} - ${endTime} (${schedule.room_name})<br>(${schedule.subject}) - ${schedule.teacher_name}
                        </div>
                    `;
                }
            });

            calendarGrid.innerHTML += `
                <div class="bg-white text-gray p-4 rounded-lg ${isToday}">
                    <p class="text-xl font-extrabold">${day}</p>
                    ${scheduleHTML}
                </div>
            `;
        }
    }

    // Convert 24-hour to 12-hour format
    function convertTo12Hour(time) {
        const [hours, minutes] = time.split(":");
        const hour = parseInt(hours, 10);
        const period = hour >= 12 ? "PM" : "AM";
        const formattedHour = hour % 12 || 12;
        return `${formattedHour}:${minutes} ${period}`;
    }

    // Initial calendar render
    updateCalendar(currentMonth, currentYear);

    // Event Listeners for Month and Year Selection
    monthSelect.addEventListener("change", () => {
        currentMonth = parseInt(monthSelect.value);
        updateCalendar(currentMonth, currentYear);
    });

    yearSelect.addEventListener("change", () => {
        currentYear = parseInt(yearSelect.value);
        updateCalendar(currentMonth, currentYear);
    });

    // Add event listeners for Next and Prev buttons
    prevMonthBtn.addEventListener("click", () => {
        currentMonth--;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }
        updateCalendar(currentMonth, currentYear);
    });

    nextMonthBtn.addEventListener("click", () => {
        currentMonth++;
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }
        updateCalendar(currentMonth, currentYear);
    });
    
    // Update calendar every minute to refresh status colors
    setInterval(() => {
        updateCalendar(currentMonth, currentYear);
    }, 60000); // Update every minute
});


</script>

@endsection
