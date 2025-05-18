@extends('layouts.app')

@section('content')

<!-- Main Content -->
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

    <!-- Profile Section -->
    <section class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Profile Form -->
        <form action="" method="POST" enctype="multipart/form-data" class="md:col-span-3 bg-white bg-opacity-80 backdrop-blur-lg p-6 rounded-lg shadow-md">
            @csrf


            @include(' _message')

            <div class="grid grid-cols-3 gap-6">

                <!-- Left Side: Profile Picture -->
                <div class="flex flex-col items-center">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h2>

    <div class="relative w-32 h-32">
        @if($getRecord->getProfilePictureUrl() && !Str::contains($getRecord->getProfilePictureUrl(), 'default-profile.png'))
            <img src="{{ $getRecord->getProfilePictureUrl() }}" 
                 class="w-32 h-32 rounded-full object-cover border shadow-md" alt="Profile Picture">
        @else
            <div class="w-32 h-32 rounded-full bg-gray-200 border shadow-md flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        @endif
    </div>

    <input type="file" name="profile_pic" class="mt-4 w-full text-sm border p-2 rounded-md">
</div>

                <!-- Middle & Right Side: Profile Information -->
                <div class="col-span-2">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Information</h2>

                    <!-- Email & Name -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-bold">Email</label>
                            <input type="email" name="email" class="w-full p-2 border rounded-md mt-1" value="{{ Auth::user()->email }}">
                        </div>
                        <div>
                            <label class="text-sm font-bold">First Name</label>
                            <input type="text" name="first_name" class="w-full p-2 border rounded-md mt-1" value="{{ Auth::user()->first_name }}">
                        </div>
                    </div>

                    <!-- Role & Status -->
                    <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
    <label class="text-sm font-bold">Role</label>
    <input type="text" class="w-full p-2 border rounded-md mt-1" 
           value="{{ Auth::user()->user_type == 1 ? 'Admin' : (Auth::user()->user_type == 2 ? 'Teacher' : 'Unknown') }}" 
           disabled>
</div>

<div>
    <label class="text-sm font-bold">Status</label>
    <input type="text" name="status" class="w-full p-2 border rounded-md mt-1" 
           value="{{ Auth::user()->status == 0 ? 'Active' : (Auth::user()->status == 1 ? 'Inactive' : 'Unknown') }}" 
           disabled>
</div>

                    </div>

                    <!-- Last Name & Subject -->
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="text-sm font-bold">Last Name</label>
                            <input type="text" name="last_name" class="w-full p-2 border rounded-md mt-1" value="{{ Auth::user()->last_name }}">
                        </div>
                        <div>
    <label class="text-sm font-bold">Subject</label>
    <select name="subject" class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-700" required>
        <option value="" disabled {{ Auth::user()->subject == '' ? 'selected' : '' }}>Select Subject</option>
        <option value="E-tech" {{ Auth::user()->subject == 'E-tech' ? 'selected' : '' }}>E-tech</option>
        <option value="Programming" {{ Auth::user()->subject == 'Programming' ? 'selected' : '' }}>Programming</option>
    </select>
</div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-2">Contact Info</h3>

                    <!-- Contact Number -->
                    <div class="grid grid-cols-2 gap-4">
    <div>
        <label class="text-sm font-bold">Phone Number</label>
        <div class="flex items-center border rounded-md mt-1 p-2">
            <span class="text-gray-600 font-semibold">+63</span>
            <input type="text" name="phone_number" id="phone_number"
                   class="w-full p-2 outline-none border-0 focus:ring-0"
                   placeholder="9123456789" 
                   value="{{ old('phone_number', Auth::user()->phone_number ? substr(Auth::user()->phone_number, -10) : '') }}"
                   maxlength="10">
        </div>
        @error('phone_number')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>


                    <!-- Submit Button -->
                    <button type="submit" class="mt-6 w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>

            </div>
        </form>

    </section>

</main>

@endsection
