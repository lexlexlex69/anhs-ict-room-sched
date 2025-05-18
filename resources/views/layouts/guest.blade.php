<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Reservation - Agusan National High School</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="flex min-h-screen relative" style="background-color: #fbf9f9ff;">
    <div class="absolute inset-0" style="background-color: #fbf9f9ff;"></div>

    @yield('content')

    <script>
        // Only include scripts that are needed for public pages
        document.addEventListener("DOMContentLoaded", function() {
            // Time display for pages that need it
            const currentDateEl = document.getElementById('current-date');
            const currentTimeEl = document.getElementById('current-time');

            if (currentDateEl && currentTimeEl) {
                function updateTime() {
                    const now = new Date();
                    const options = {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    };
                    currentDateEl.innerText = now.toLocaleDateString('en-US', options);
                    currentTimeEl.innerText = now.toLocaleTimeString();
                }
                setInterval(updateTime, 1000);
                updateTime();
            }
        });
    </script>

    @yield('scripts')
</body>

</html>