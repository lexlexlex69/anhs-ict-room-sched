<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling System - Agusan National High School</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Add to your existing styles */
        .slot-suggestion {
            transition: all 0.2s ease;
        }

        .slot-suggestion:hover {
            background-color: #f0f7ff;
        }

        .use-slot-btn {
            transition: color 0.2s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            animation: fadeIn 0.3s ease-in-out;
        }

        .group:hover .group-hover\:opacity-100 {
            opacity: 1;
        }

        /* Align search box to the right */
        #myTable_filter {
            text-align: right !important;
            padding-bottom: 20px;
        }

        /* Style the search label */
        #myTable_filter label {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            /* Move label and input to the right */
            font-size: 1.2rem;
            /* Bigger font */
            color: black;
            /* White text */
            gap: 10px;
            /* Space between text and input */
        }

        /* Style the search input */
        #myTable_filter input {
            padding: 8px 10px;
            /* Bigger padding */
            border-radius: 8px;
            /* Rounded corners */
            font-size: .5 rem;
            /* Bigger font */
            background-color: white;
            /* Dark blue background */
            color: black;
            /* White text */
            border: 2px solid black;
            /* White border */
            outline: 1px;
            width: 250px;
            /* Set width */
        }

        /* Placeholder color */
        #myTable_filter input::placeholder {
            color: rgba(255, 255, 255, 0.7);
            /* Light white */
        }

        /* Focus effect */
        #myTable_filter input:focus {
            border-color: blue;
            /* Yellow border */
        }
    </style>


</head>

<body class="flex min-h-screen relative" style="background-color: #fbf9f9ff;">

    <!-- Background Image -->
    <div class="absolute inset-0" style="background-color: #fbf9f9ff;"></div>
    @auth
    <!-- Mobile Toggle Button -->
    <button id="menu-toggle" class="absolute top-4 left-4 z-40 bg-white p-2 rounded-md md:hidden">
        <i class="fa-solid fa-bars text-lg"></i>
    </button>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-white bg-opacity-30 backdrop-blur-lg border-r border-white border-opacity-20 p-6 min-h-screen 
            fixed md:relative md:block transition-all duration-300 z-30">

        <div class="flex flex-col items-center">
            <img src="{{ asset('asset/img/logo.png') }}" alt="School Logo" class="w-24 mb-4">
            <h2 class="text-lg font-extrabold text-gray-800 text-center">Agusan National High School</h2>
        </div>

        <nav class="mt-6 font-karla font-extrabold">
            <ul class="space-y-4">
                @if(Auth::user()->user_type == 1)
                <li class="relative group">

                    <button class="flex items-center w-full p-3 rounded-lg text-left 
        {{ Request::is('admin/schedules*') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray hover:bg-white hover:bg-opacity-20' }}">


                        <i class="fa-solid fa-chalkboard text-lg mr-3"></i> Schedule
                        <i class="fa-solid fa-chevron-down ml-auto"></i>
                    </button>

                    <ul class="absolute left-0 mt-0 w-48 bg-white bg-opacity-90 text-sm text-blue-900 rounded shadow-lg hidden group-hover:block z-10">
                        <li>
                            <a href="{{ url('admin/schedules/AllList') }}" class="block px-4 py-2 hover:bg-blue-100">All Schedules</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/schedules/upcoming') }}" class="block px-4 py-2 hover:bg-blue-100">Upcoming</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/schedules/ongoing') }}" class="block px-4 py-2 hover:bg-blue-100">Ongoing</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/schedules/completed') }}" class="block px-4 py-2 hover:bg-blue-100">Completed</a>
                        </li>
                        <li>
                            <a href="{{ url('admin/schedules/declined') }}" class="block px-4 py-2 hover:bg-blue-100">Decline</a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="{{ url('admin/schedule/list') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('admin/schedule/list') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-calendar text-lg mr-3 "></i> Calendar
                    </a>
                </li>
                <li>
                    <a href="{{ url('admin/reservations') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('admin/reservations') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-calendar text-lg mr-3 "></i> Reservations
                    </a>
                </li>
                <li>
                    <a href="{{ url('admin/room/list') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('admin/room/list') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-door-open text-lg mr-3"></i> Rooms
                    </a>
                </li>

                <li>
                    <a href="{{ url('admin/teacher/list') }}" class="flex items-center p-3 rounded-lg 
                    {{ Request::is('admin/teacher/list') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-chalkboard-teacher text-lg mr-3"></i> Teachers
                    </a>
                </li>
                <li>
                    <a href="{{ url('admin/schedule/all') }}" class="flex items-center p-3 rounded-lg 
                    {{ Request::is('admin/schedule/all') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-chalkboard-teacher text-lg mr-3"></i> Weekly Schedules
                    </a>
                </li>

                <li>
                    <a href="{{ url('admin/account') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('admin/account') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-cog text-lg mr-3"></i> Settings
                    </a>
                </li>
                @elseif(Auth::user()->user_type == 2)
                <li>
                    <a href="{{ url('teacher/dashboard') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('teacher/dashboard') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-chalkboard text-lg mr-3"></i> Teacher Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ url('teacher/schedule/weekly') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('teacher/schedule/weekly') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-calendar-alt text-lg mr-3"></i> Weekly Schedule
                    </a>
                </li>
                <li>
                    <a href="{{ url('teacher/account') }}" class="flex items-center p-3 rounded-lg 
                            {{ Request::is('teacher/account') ? 'bg-white bg-opacity-80 text-blue-700' : 'text-gray-700  hover:bg-white hover:bg-opacity-20' }}">
                        <i class="fa-solid fa-cog text-lg mr-3"></i> Settings
                    </a>
                </li>
                @endif

                <li class="mt-10">
                    <a href="{{ url('logout') }}" class="flex items-center p-3 text-gray-700  hover:bg-red-600 hover:bg-opacity-80 rounded-lg">
                        <i class="fa-solid fa-sign-out-alt text-lg mr-3"></i> Log Out
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    @endauth
    @yield('content')


    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.13.6/datatables.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.getElementById("sidebar");
            const menuToggle = document.getElementById("menu-toggle");

            menuToggle.addEventListener("click", function() {
                sidebar.classList.toggle("-translate-x-full");
            });
        });
    </script>

    <script>
        function updateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            document.getElementById('current-date').innerText = now.toLocaleDateString('en-US', options);
            document.getElementById('current-time').innerText = now.toLocaleTimeString();
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>


    <script>
        $(document).ready(function() {
            let table = $('#myTable').DataTable({
                "lengthChange": false, // Disable "Show X entries"
                "paging": false, // Disable pagination
                "ordering": true, // Enable sorting
                "info": false // Hide "Showing X of X entries"
            });

            // Move search box to the left
            $('#myTable_filter').addClass('text-right');
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const notificationIcon = document.getElementById("notification-icon");
            const notificationDropdown = document.getElementById("notification-dropdown");
            const notificationList = document.getElementById("notification-list");
            const notificationBadge = document.getElementById("notification-badge");
            const viewAll = document.getElementById("view-all");

            function fetchNotifications() {
                fetch("/get-notifications")
                    .then(response => response.json())
                    .then(data => {
                        notificationList.innerHTML = "";

                        if (data.length > 0) {
                            // Sort notifications by latest first
                            data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                            // Show only the latest 5 notifications
                            let latestNotifications = data.slice(0, 5);
                            notificationBadge.textContent = data.length;
                            notificationBadge.classList.remove("hidden");

                            latestNotifications.forEach(notification => {
                                let listItem = document.createElement("li");
                                listItem.classList.add("p-2", "text-sm", "border-b", "hover:bg-gray-100", "cursor-pointer");

                                // Convert timestamp to readable date & time format (12-hour format with AM/PM)
                                let notificationDate = new Date(notification.created_at);
                                let formattedDate = notificationDate.toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });

                                let hours = notificationDate.getHours();
                                let minutes = notificationDate.getMinutes();
                                let ampm = hours >= 12 ? 'PM' : 'AM';
                                hours = hours % 12 || 12; // Convert 24-hour format to 12-hour
                                minutes = minutes < 10 ? '0' + minutes : minutes; // Ensure two-digit minutes

                                let formattedTime = `${hours}:${minutes} ${ampm}`;

                                // Display notification message with formatted timestamp
                                listItem.innerHTML = `<strong>${notification.message}</strong> <br>
                        <span class="text-xs text-gray-500">${formattedDate} - ${formattedTime}</span>`;

                                notificationList.appendChild(listItem);
                            });

                            // Show "View all" if there are more than 5 notifications
                            if (data.length > 5) {
                                viewAll.classList.remove("hidden");
                                viewAll.addEventListener("click", function() {
                                    window.location.href = "/notifications"; // Redirect to full notification page
                                });
                            }
                        } else {
                            notificationBadge.classList.add("hidden");
                            let emptyItem = document.createElement("li");
                            emptyItem.classList.add("p-2", "text-sm", "text-gray-500");
                            emptyItem.textContent = "No new notifications.";
                            notificationList.appendChild(emptyItem);
                        }
                    });
            }

            notificationIcon.addEventListener("click", function() {
                notificationDropdown.classList.toggle("hidden");

                if (!notificationDropdown.classList.contains("hidden")) {
                    fetch("/mark-notifications-as-read", {
                        method: "POST"
                    }).then(() => {
                        notificationBadge.classList.add("hidden");
                    });
                }
            });

            fetchNotifications();

            // 12-hour format clock
            function updateTime() {
                let now = new Date();
                let hours = now.getHours();
                let minutes = now.getMinutes();
                let ampm = hours >= 12 ? 'PM' : 'AM';

                hours = hours % 12 || 12;
                minutes = minutes < 10 ? '0' + minutes : minutes;

                document.getElementById("current-time").textContent = `${hours}:${minutes} ${ampm}`;
            }

            updateTime();
            setInterval(updateTime, 1000);
        });
    </script>



    @yield('script')


</body>

</html>