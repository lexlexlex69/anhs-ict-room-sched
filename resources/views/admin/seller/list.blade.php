@extends('layouts.app')

@section('content')

<div class="bg-gray-900 opacity-50 hidden fixed inset-0 z-10" id="sidebarBackdrop"></div>
      <div id="main-content" class="h-full w-full bg-gray-50 relative overflow-y-auto lg:ml-64">
         <main>
            <div class="pt-6 px-4">

            <div class="2xl:grid-cols-2 xl:gap-4 my-4">
   
   @include(' _message')
</div>
              
               <div class="2xl:grid-cols-2 xl:gap-4 my-4">
   
                  <div class="bg-white shadow rounded-lg p-4 sm:p-6 xl:p-8 ">
                  <div class="flex items-center justify-between mb-10">
                  
                  <h3 class="text-xl leading-none font-bold text-gray-900">List of Seller</h3>
                  <a href="{{ url('admin/seller/add') }}" 
                     class="inline-block px-6 py-2.5 bg-blue-600 text-white font-medium text-xs leading-tight uppercase rounded shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out">
                     Add Seller
                  </a>
               </div>
                     <div class="block w-full overflow-x-auto">
                        <table class="items-center w-full bg-transparent border-collapse">
                           <thead>
                              <tr>
                                 <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap">Profile</th>
                                 <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap">Owner Name</th>
                                 <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap min-w-140-px">Created Date</th>
                                 <th class="px-4 bg-gray-50 text-gray-700 align-middle py-3 text-xs font-semibold text-left uppercase border-l-0 border-r-0 whitespace-nowrap min-w-140-px">Action</th>
                              </tr>
                           </thead>
                           <tbody class="divide-y divide-gray-100">
                           @foreach($getRecord as $value)
                              <tr class="text-gray-500">
                                 <th class="border-t-0 px-4 align-middle text-sm font-normal whitespace-nowrap p-4 text-left">@if(!empty($value->getProfileDirect()))
                                    <img src="{{ $value->getProfileDirect() }}"
                                        style="height: 50px; width:50px; border-radius: 50px;">
                                @endif
                              
                              </th>
                                 <td class="border-t-0 px-4 align-middle text-xs font-medium text-gray-900 whitespace-nowrap p-4">{{  $value->name }}

                                 </td>
                                 <td class="border-t-0 px-4 align-middle text-xs font-medium text-gray-900 whitespace-nowrap p-4">{{ \Carbon\Carbon::parse($value->created_at)->format('F j, Y') }}
                                    
                                 </td>
                                 <td class="border-t-0 px-4 align-middle text-xs font-medium text-gray-900 whitespace-nowrap p-4">
    <div class="relative inline-block text-left">
        <!-- Dropdown Button -->
        <button type="button" 
            class="dropdown-button inline-flex justify-center w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
            aria-haspopup="true">
            Actions
            <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <div class="dropdown-menu absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 hidden">
            <div class="py-1">
                <a href="{{ url('admin/inspector/show/'.$value->id) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Show
                </a>
                <a href="{{ url('admin/inspector/edit/'.$value->id) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Edit
                </a>
                <a href="{{ url('admin/inspector/delete/'.$value->id) }}" 
                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Delete
                </a>
            </div>
        </div>
    </div>
</td>

                              </tr>

                              @endforeach
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </main>

         @include('layouts.footer')

      </div>



@endsection