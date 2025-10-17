<!-- Page Title -->
<div class="p-4 border-b border-gray-200">
    <h2 class="text-xl font-bold text-[#E6B800]">Tutlo Account</h2>
</div>

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    <form method="GET" action="{{ route('employees.index') }}" class="flex justify-between items-center space-x-4">
        <input type="hidden" name="tab" value="tutlo">
        
        <!-- Left side -->
        <div class="flex items-center space-x-4 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" placeholder="search name..." value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
              focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
              focus:ring-0 focus:shadow-xl">
            </div>
            <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Available at:</span>
            <select name="time_slot" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Time Range</option>
                <option value="7:00-8:00" {{ request('time_slot') == '7:00-8:00' ? 'selected' : '' }}>7:00-8:00</option>
                <option value="8:00-9:00" {{ request('time_slot') == '8:00-9:00' ? 'selected' : '' }}>8:00-9:00</option>
                <option value="9:00-10:00" {{ request('time_slot') == '9:00-10:00' ? 'selected' : '' }}>9:00-10:00</option>
                <option value="10:00-11:00" {{ request('time_slot') == '10:00-11:00' ? 'selected' : '' }}>10:00-11:00</option>
                <option value="11:00-12:00" {{ request('time_slot') == '11:00-12:00' ? 'selected' : '' }}>11:00-12:00</option>
                <option value="12:00-13:00" {{ request('time_slot') == '12:00-13:00' ? 'selected' : '' }}>12:00-13:00</option>
                <option value="13:00-14:00" {{ request('time_slot') == '13:00-14:00' ? 'selected' : '' }}>13:00-14:00</option>
                <option value="14:00-15:00" {{ request('time_slot') == '14:00-15:00' ? 'selected' : '' }}>14:00-15:00</option>
                <option value="15:00-16:00" {{ request('time_slot') == '15:00-16:00' ? 'selected' : '' }}>15:00-16:00</option>
                <option value="16:00-17:00" {{ request('time_slot') == '16:00-17:00' ? 'selected' : '' }}>16:00-17:00</option>
                <option value="17:00-18:00" {{ request('time_slot') == '17:00-18:00' ? 'selected' : '' }}>17:00-18:00</option>
                <option value="18:00-19:00" {{ request('time_slot') == '18:00-19:00' ? 'selected' : '' }}>18:00-19:00</option>
                <option value="19:00-20:00" {{ request('time_slot') == '19:00-20:00' ? 'selected' : '' }}>19:00-20:00</option>
                <option value="20:00-21:00" {{ request('time_slot') == '20:00-21:00' ? 'selected' : '' }}>20:00-21:00</option>
                <option value="21:00-22:00" {{ request('time_slot') == '21:00-22:00' ? 'selected' : '' }}>21:00-22:00</option>
            </select>
            <select name="day" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Day</option>
                <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Monday</option>
                <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Friday</option>
                <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Sunday</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-[#E6B800] text-white rounded-md text-sm hover:bg-[#E6B800]/80">
                Search
            </button>
        </div>
    </form>
</div>

<!-- Employee Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="tutloTableBody">
            @forelse($tutors as $tutor)
                @php
                    $tutloAccount = $tutor->accounts->where('account_name', 'Tutlo')->first();
                    $paymentInfo = $tutor->paymentInformation;
                    $tutorDetails = $tutor->tutorDetails;
                @endphp
                <tr class="hover:bg-gray-50 tutlo-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $tutor->full_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        <a href="mailto:{{ $tutor->email ?? '' }}">{{ $tutor->email ?? 'N/A' }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($tutloAccount)
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-700">{{ $tutloAccount->getFormattedAvailableTimeAttribute() }}</span>
                                <span class="text-xs text-yellow-600 font-medium">(Tutlo Account)</span>
                            </div>
                        @else
                            <span class="text-red-500 text-sm">No Tutlo availability</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $tutor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($tutor->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="openEmployeeModal('tutor', '{{ $tutor->tutorID }}')" 
                                    class="w-8 h-8 text-gray-600 rounded hover:bg-gray-100 inline-flex items-center justify-center transition-colors"
                                    title="View detailed tutor information">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            @if($tutor->status === 'active')
                                <button class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors text-xs font-medium"
                                        onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')" 
                                        title="Deactivate tutor - will remove from class assignments">
                                    Deactivate
                                </button>
                            @else
                                <button class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors text-xs font-medium"
                                        onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')" 
                                        title="Activate tutor - will make available for class assignments">
                                    Activate
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                        No tutors found with Tutlo accounts.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>


<!-- Pagination -->
@if(isset($tutors))
    @include('emp_management.partials.compact-pagination', ['data' => $tutors, 'tab' => 'tutlo'])
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div class="text-sm text-gray-500">
        Showing 0 results
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif
