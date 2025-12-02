<!-- Search Filters -->
<div class="px-4 md:px-6 pt-4 md:pt-6 pb-3 border-b border-gray-200">
    <form method="GET" action="{{ route('employees.index') }}" id="tutloFilterForm">
        <input type="hidden" name="tab" value="tutlo">

        <div class="flex items-center justify-between gap-x-6">

            <!-- Left label -->
            <h3 class="text-sm font-medium text-gray-700 whitespace-nowrap">
                Search Filters
            </h3>

            <!-- Middle filters -->
            <div class="flex items-center gap-x-4 flex-1">

                <!-- Search Input (SHORTER WIDTH) -->
                <div class="relative" style="max-width: 250px; width: 100%;">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                    <input type="text" 
                           name="search"
                           value="{{ request('search') }}"
                           id="tutloSearch"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm 
                                  focus:outline-none focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50">
                </div>

                <!-- Status -->
                <select name="status" id="filtertutloStatus"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                               focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                        onchange="handletutloFilterChange('status')">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Right filters -->
            <div class="flex items-center gap-x-4">

                <span class="text-sm text-gray-600 whitespace-nowrap">Available at:</span>

                <!-- Time Slot -->
                <select name="time_slot" id="filtertutloTimeSlot"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                               focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                        onchange="handletutloFilterChange('time_slot')">
                    <option value="">All Times</option>
                    <option value="07:00 - 08:00" {{ request('time_slot') == '07:00 - 08:00' ? 'selected' : '' }}>07:00 - 08:00 AM</option>
                    <option value="08:00 - 09:00" {{ request('time_slot') == '08:00 - 09:00' ? 'selected' : '' }}>08:00 - 09:00 AM</option>
                    <option value="09:00 - 10:00" {{ request('time_slot') == '09:00 - 10:00' ? 'selected' : '' }}>09:00 - 10:00 AM</option>
                    <option value="10:00 - 11:00" {{ request('time_slot') == '10:00 - 11:00' ? 'selected' : '' }}>10:00 - 11:00 AM</option>
                    <option value="11:00 - 12:00" {{ request('time_slot') == '11:00 - 12:00' ? 'selected' : '' }}>11:00 - 12:00 PM</option>
                    <option value="12:00 - 13:00" {{ request('time_slot') == '12:00 - 13:00' ? 'selected' : '' }}>12:00 - 13:00 PM</option>
                    <option value="13:00 - 14:00" {{ request('time_slot') == '13:00 - 14:00' ? 'selected' : '' }}>01:00 - 02:00 PM</option>
                    <option value="14:00 - 15:00" {{ request('time_slot') == '14:00 - 15:00' ? 'selected' : '' }}>02:00 - 03:00 PM</option>
                    <option value="15:00 - 16:00" {{ request('time_slot') == '15:00 - 16:00' ? 'selected' : '' }}>03:00 - 04:00 PM</option>
                </select>

                <!-- Day -->
                <select name="day" id="filtertutloDay"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 bg-white
                               focus:border-[#2A5382] focus:ring-1 focus:ring-[#2A5382]/50"
                        onchange="handletutloFilterChange('day')">
                    <option value="">All Days</option>
                    <option value="mon" {{ request('day') == 'mon' ? 'selected' : '' }}>Monday</option>
                    <option value="tue" {{ request('day') == 'tue' ? 'selected' : '' }}>Tuesday</option>
                    <option value="wed" {{ request('day') == 'wed' ? 'selected' : '' }}>Wednesday</option>
                    <option value="thur" {{ request('day') == 'thur' ? 'selected' : '' }}>Thursday</option>
                    <option value="fri" {{ request('day') == 'fri' ? 'selected' : '' }}>Friday</option>
                </select>
            </div>

        </div>
    </form>
</div>

<!-- Employee Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tutor ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'tutlo', 'sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Name
                        @if(request('sort') === 'name')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ESL Teaching Experience</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Setup</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Day of Teaching</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Educational Attainment</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('employees.index', array_merge(request()->all(), ['tab' => 'tutlo', 'sort' => 'status', 'direction' => request('sort') === 'status' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Status
                        @if(request('sort') === 'status')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="tutloTableBody">
            @forelse($tutors as $tutor)
                @php
                    $paymentInfo = $tutor->paymentInformation;
                    $tutorDetails = $tutor->tutorDetails;
                @endphp
                <tr class="hover:bg-gray-50 tutlo-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tutor->tutorID ? 'OGS-T' . str_pad($tutor->tutorID, 4, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <a href="#" class="text-black-600 hover:text-black-800">{{ $tutor->full_name }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutor->phone_number ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutor->email ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutorDetails->address ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutorDetails->esl_experience ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $paymentInfo->payment_method_uppercase ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutorDetails->work_setup ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutorDetails->formatted_first_day_teaching ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tutorDetails->formatted_educational_attainment ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($tutor->workPreferences)
                            {{ $tutor->formatted_available_time }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $tutor->status === 'active' ? 'bg-[#65DB7F]' : 'bg-[#F65353]' }}"></span>
                            <span class="text-xs font-medium text-gray-500">
                                {{ ucfirst($tutor->status) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex space-x-2">
                            <button onclick="openEmployeeModal('tutor', '{{ $tutor->tutorID }}')" 
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                    title="View Details">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                            @if($tutor->status === 'active')
                                <button onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')" class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 inline-flex items-center justify-center transition-colors" title="Deactivate">
                                    <i class="fas fa-user-slash text-xs"></i>
                                </button>
                            @else
                                <button onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')" class="w-8 h-8 bg-green-100 text-green-600 rounded hover:bg-green-200 inline-flex items-center justify-center transition-colors" title="Activate">
                                    <i class="fas fa-user-check text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No Tutlo tutors found</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const form = document.getElementById('tutloFilterForm');
        
        if (searchInput && form) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.submit();
                }
            });
        }
    });
</script>

