<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Application</title>
    <!-- Include Tailwind CSS using Vite -->
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100 p-4">

    <!-- Alerts Section -->
    @if(!empty(session('error')))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
        {{ session('error') }}
    </div>
    @endif

    @if(!empty(session('success')))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
        {{ session('success') }}
    </div>
    @endif

    @if(!empty(session('error_cert')))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
        {!! session('error_cert') !!}
    </div>
    @endif

</body>
</html>
