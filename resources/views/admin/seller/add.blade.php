@extends('layouts.app')

@section('content')

<div class="bg-gray-900 opacity-50 hidden fixed inset-0 z-10" id="sidebarBackdrop"></div>
<div id="main-content" class="h-full w-full bg-gray-50 relative overflow-y-auto lg:ml-64">
    <main>
        <div class="pt-6 px-4">
            <div class="2xl:grid-cols-2 xl:gap-4 my-4">
                <div class="bg-white shadow rounded-lg p-6 sm:p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Add Seller</h3>
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                     
                        <div class="mb-4">
                            <label for="owner_name" class="block text-sm font-medium text-gray-700">Owner Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900" placeholder="Enter owner name" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900" placeholder="Enter email address" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-gray-900" placeholder="Enter Password" required>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium text-sm uppercase rounded shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Add Seller
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    @include('layouts.footer')
</div>

@endsection
