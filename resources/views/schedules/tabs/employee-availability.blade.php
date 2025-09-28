<!-- Page Title -->
<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Tutor Availability</h2>
</div>

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    
    <form method="GET" action="{{ route('schedules.index') }}" id="tutorFilterForm">
        <input type="hidden" name="tab" value="employee">
        
        <div class="flex justify-between items-center space-x-4">
            <!-- Left side -->
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search full name, email, phone..."
                           id="tutorSearch"
                           class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <div id="searchSpinner" class="absolute right-3 top-1/2 transform -translate-y-1/2 hidden">
                        <i class="fas fa-spinner fa-spin text-gray-400"></i>
                    </div>
                    <button type="button" id="clearSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 {{ request('search') ? '' : 'hidden' }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <select name="status" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">Available at:</span>
                <select name="time_range" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Times</option>
                    <option value="morning" {{ request('time_range') == 'morning' ? 'selected' : '' }}>Morning (6AM-12PM)</option>
                    <option value="afternoon" {{ request('time_range') == 'afternoon' ? 'selected' : '' }}>Afternoon (12PM-6PM)</option>
                    <option value="evening" {{ request('time_range') == 'evening' ? 'selected' : '' }}>Evening (6PM-12AM)</option>
                </select>
                
                <select name="day" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Days</option>
                    <option value="monday" {{ request('day') == 'monday' ? 'selected' : '' }}>Monday</option>
                    <option value="tuesday" {{ request('day') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                    <option value="wednesday" {{ request('day') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                    <option value="thursday" {{ request('day') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                    <option value="friday" {{ request('day') == 'friday' ? 'selected' : '' }}>Friday</option>
                    <option value="saturday" {{ request('day') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                    <option value="sunday" {{ request('day') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                </select>

                @if(request()->hasAny(['search', 'status', 'time_range', 'day']))
                    <a href="{{ route('schedules.index', ['tab' => 'employee']) }}" 
                       class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tutor Table -->
<div class="overflow-x-auto">
    <table class="w-full" id="tutorTable">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
            @forelse($tutors ?? [] as $tutor)
            @php
                $glsAccount = $tutor->accounts->firstWhere('account_name', 'GLS');
            @endphp
            <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->full_name ?? '') . ' ' . ($tutor->email ?? '') . ' ' . ($tutor->phone_number ?? '')) }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $tutor->full_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tutor->phone_number ?? 'N/A' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
                    <a href="mailto:{{ $tutor->email ?? '' }}">{{ $tutor->email ?? 'N/A' }}</a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($glsAccount)
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-700">{{ $glsAccount->formatted_available_time }}</span>
                            <span class="text-xs text-green-600 font-medium">(GLS Account)</span>
                        </div>
                    @else
                        <span class="text-red-500 text-sm">No GLS availability</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $tutor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($tutor->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    @if($tutor->status === 'active')
                        <button class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'inactive')">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    @else
                        <button class="w-8 h-8 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors"
                                onclick="toggleTutorStatus('{{ $tutor->tutorID }}', 'active')">
                            <i class="fas fa-check text-xs"></i>
                        </button>
                    @endif
                </td>
            </tr>
            @empty
            <tr id="noResultsRow">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium">No tutors found</p>
                    <p class="text-sm">Try adjusting your search criteria</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- No Search Results Message -->
    <div id="noSearchResults" class="hidden bg-white px-6 py-8 text-center text-gray-500 border-t">
        <i class="fas fa-search text-4xl mb-4 opacity-50"></i>
        <p class="text-lg font-medium">No tutors found</p>
        <p class="text-sm">Try adjusting your search terms</p>
    </div>
</div>

<!-- Pagination -->
@if(isset($tutors) && method_exists($tutors, 'hasPages') && $tutors->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between" id="paginationSection">
    <div class="text-sm text-gray-500">
        Showing {{ $tutors->count() }} of {{ $tutors->total() }} results
    </div>
    <div class="flex items-center space-x-2">
        @if ($tutors->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $tutors->appends(request()->query())->previousPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($tutors->appends(request()->query())->getUrlRange(1, $tutors->lastPage()) as $page => $url)
            @if ($page == $tutors->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        @if ($tutors->hasMorePages())
            <a href="{{ $tutors->appends(request()->query())->nextPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between" id="paginationSection">
    <div class="text-sm text-gray-500">
        Showing <span id="resultCount">{{ count($tutors ?? []) }}</span> results
    </div>
    <div class="flex items-center space-x-2">
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">1</button>
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif

<script>
    window.tutorTotalResults = @json(isset($tutors) && method_exists($tutors, 'total') ? $tutors->total() : 0);
</script>
<script src="{{ asset('js/employee-availability-globals.js') }}"></script>
<script src="{{ asset('js/employee-availability-search.js') }}"></script>
