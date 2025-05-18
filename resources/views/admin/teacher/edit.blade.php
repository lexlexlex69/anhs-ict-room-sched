@extends('layouts.app')

@section('content')

    <!-- Main Content -->
    <main class="relative z-10 flex-1 px-8 font-karla font-semibold">

        <!-- Top Bar -->
        <header style="background-color: #1E40AF;" class="flex justify-between items-center bg-blue-800 text-white px-6 py-4 rounded-lg shadow-md">
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
    <i class="fa-solid fa-bell text-lg cursor-pointer text-white" id="notification-icon"></i>

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

        <!-- Room Submission Form -->
        <section class="mt-6">
            <div class="bg-white bg-opacity-80 backdrop-blur-lg p-6 rounded-lg shadow-lg">
                <h2 class="text-xl font-semibold text-gray-900">Edit Teacher Details</h2>
                
                <form action="" method="POST" class="mt-4 space-y-4">
                    @csrf
                    
                    <!-- Room Name -->
                    <div>
                        <label class="text-sm font-bold text-gray-700">First Name</label>
                        <input type="text" name="first_name"  value="{{ old ('first_name',$getRecord->first_name) }}" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required placeholder="Enter First Name">
                    </div>


                    <div>
                        <label class="text-sm font-bold text-gray-700">Email</label>
                        <input type="email" name="email"  value="{{ old ('email',$getRecord->email) }}" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required placeholder="Enter Email ">
                    </div>


                    <div>
                        <label class="text-sm font-bold text-gray-700">Password</label>
                        <input type="password" name="password"  value="{{ old ('password',$getRecord->password) }}" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required placeholder="Enter Paasword ">
                    </div>


<div>
    <label class="text-sm font-bold text-gray-700">Subject</label>
    <select name="subject" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
        <option value="" disabled {{ old('subject', $getRecord->subject) == '' ? 'selected' : '' }}>Select Subject</option>
        <option value="E-tech" {{ old('subject', $getRecord->subject) == 'E-tech' ? 'selected' : '' }}>E-tech</option>
        <option value="Programming" {{ old('subject', $getRecord->subject) == 'Programming' ? 'selected' : '' }}>Programming</option>
    </select>
</div>


                    <!-- Submit Button -->
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        Submit Room
                    </button>
                </form>
            </div>
        </section>

       
    </main>

@endsection
