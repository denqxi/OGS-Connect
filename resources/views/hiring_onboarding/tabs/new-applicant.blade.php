@if (request('view') === 'details')
    {{-- Show applicant details --}}
    @include('hiring_onboarding.tabs.partials.applicant-details')
@else

    <!-- Search Filters -->
    <div class="pb-3 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-500">Search Filters</h3>
        </div>
        <form method="GET" action="{{ route('hiring_onboarding.index') }}" id="searchForm" class="flex justify-between items-center space-x-4">
            <!-- Left side -->
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" id="searchIcon"></i>
                    <i class="fas fa-spinner fa-spin absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hidden" id="loadingIcon"></i>
                    <input type="text" name="search" id="searchInput" placeholder="search..." value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
                focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                focus:ring-0 focus:shadow-xl">
                </div>
                <select name="status" id="statusSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                    <option value="">Select Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="no_answer" {{ request('status') == 'no_answer' ? 'selected' : '' }}>No Answer</option>
                    <option value="re_schedule" {{ request('status') == 're_schedule' ? 'selected' : '' }}>Re-schedule</option>
                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                    <option value="not_recommended" {{ request('status') == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
                </select>
                <select name="source" id="sourceSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                    <option value="">Select Source</option>
                    @foreach($sources as $source)
                        <option value="{{ $source }}" {{ request('source') == $source ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $source)) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const statusSelect = document.getElementById('statusSelect');
            const sourceSelect = document.getElementById('sourceSelect');

            const searchForm = document.getElementById('searchForm');
            const searchIcon = document.getElementById('searchIcon');
            const loadingIcon = document.getElementById('loadingIcon');
            
            let searchTimeout;
            
            // Show loading indicator
            function showLoading() {
                searchIcon.classList.add('hidden');
                loadingIcon.classList.remove('hidden');
            }
            
            // Hide loading indicator
            function hideLoading() {
                searchIcon.classList.remove('hidden');
                loadingIcon.classList.add('hidden');
            }
            
            // Search function (manual trigger only)
            function performSearch() {
                showLoading();
                searchForm.submit();
            }
            
            // Search on Enter key press or search icon click
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        performSearch();
                    }
                });
            }
            
            // Search on search icon click
            const searchIconClick = document.getElementById('searchIcon');
            if (searchIconClick) {
                searchIconClick.addEventListener('click', function() {
                    performSearch();
                });
            }
            
            // Auto-submit form when status or source changes
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    showLoading();
                    searchForm.submit();
                });
            }
            
            if (sourceSelect) {
                sourceSelect.addEventListener('change', function() {
                    showLoading();
                    searchForm.submit();
                });
            }
        });

        // Function to set archive form action
        function setArchiveFormAction(applicantId) {
            const form = document.getElementById('archiveForm');
            if (form) {
                form.action = `{{ url('hiring-onboarding/applicant') }}/${applicantId}/fail`;
            }
        }
    </script>
    
    <!-- Employee Table -->
    <div class="overflow-x-auto">
        <table class="w-full" style="table-layout: fixed;">
            <thead class="bg-gray-50 border-b border-gray-200">
          
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Date Applied</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Applicant Name</th>
                    {{-- <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 18%;">Email</th> --}}
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 16%;">Work Availability</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Scheduled Interview</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 8%;">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 7%;">Actions</th>
                </tr>
            </thead>
            
            <!-- Start of the Records in each employee -->
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($applicants as $applicant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($applicant->created_at)->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-4 py-4 text-sm font-medium text-gray-900">
                            {{ $applicant->first_name }} {{ $applicant->last_name }}
                        </td>
                        {{-- <td class="px-4 py-4 text-sm text-gray-500">{{ $applicant->contact_number }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600" style="word-wrap: break-word;">{{ $applicant->email }}</td> --}}
                        <td class="px-3 py-4 text-sm text-gray-500" style="max-width: 200px; word-wrap: break-word;">
                            @php
                                // Normalize days: accept array or JSON string
                                $days = $applicant->days ?? [];
                                if (is_string($days)) {
                                    $decoded = json_decode($days, true);
                                    $days = is_array($decoded) ? $decoded : [];
                                }
                                $hasDays = !empty($days) && is_array($days);
                                $hasTimes = !empty($applicant->start_time) && !empty($applicant->end_time);
                            @endphp

                            @if($hasDays && $hasTimes)
                                @php
                                    // Create day abbreviations
                                    $dayAbbreviations = [
                                        'monday' => 'Mon',
                                        'tuesday' => 'Tue', 
                                        'wednesday' => 'Wed',
                                        'thursday' => 'Thu',
                                        'friday' => 'Fri',
                                        'saturday' => 'Sat',
                                        'sunday' => 'Sun'
                                    ];
                                    $abbreviatedDays = collect($days)->map(function($d) use ($dayAbbreviations) {
                                        $normalized = strtolower(str_replace(['_','-'], '', $d));
                                        return $dayAbbreviations[$normalized] ?? substr(ucfirst($d), 0, 3);
                                    })->join(', ');
                                @endphp
                                <div title="{{ collect($days)->map(function($d){ return \Illuminate\Support\Str::title(str_replace(['_','-'], ' ', $d)); })->join(', ') }} | {{ $applicant->start_time }} - {{ $applicant->end_time }}">
                                    {{ $abbreviatedDays }} | 
                                    {{ \Carbon\Carbon::parse($applicant->start_time)->format('g:i A') }}
                                    -
                                    {{ \Carbon\Carbon::parse($applicant->end_time)->format('g:i A') }}
                                </div>
                            @elseif($hasTimes)
                                {{ $applicant->start_time }} - {{ $applicant->end_time }}
                            @elseif(!empty($applicant->interview_time))
                                {{ \Carbon\Carbon::parse($applicant->interview_time)->format('M d, h:i A') }}
                            @else
                                —
                            @endif
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-500" style="word-wrap: break-word;">
                            @if(!empty($applicant->interview_time))
                                {{ \Carbon\Carbon::parse($applicant->interview_time)->format('M d, Y h:i A') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-400',
                                        'rejected' => 'bg-red-500',
                                        'no_answer' => 'bg-orange-500',
                                        're_schedule' => 'bg-purple-400',
                                        'declined' => 'bg-red-600',
                                        'not_recommended' => 'bg-red-700',
                                    ];
                                    $circleColor = $statusColors[$applicant->status] ?? 'bg-gray-500';
                                @endphp
                                <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                                <span class="text-xs font-medium text-gray-500">
                                    @switch($applicant->status)
                                        @case('no_answer')
                                            No Answer
                                            @break
                                        @case('re_schedule')
                                            Re-schedule
                                            @break
                                        @case('declined')
                                            Declined
                                            @break
                                        @case('not_recommended')
                                            Not Recommended
                                            @break
                                        @default
                                            {{ ucwords(str_replace('_', ' ', $applicant->status)) }}
                                    @endswitch
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <a href="{{ route('hiring_onboarding.applicant.show', $applicant->application_id) }}"
                                    class="w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <!-- Archive Button -->
                                <button
                                    onclick="openArchiveModal({{ $applicant->application_id }})"
                                    class="w-8 h-8 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                                    title="Archive Applicant">
                                    <i class="fas fa-archive text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No New Applicants as of the Moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing {{ $applicants->firstItem() ?? 0 }} to {{ $applicants->lastItem() ?? 0 }} of {{ $applicants->total() }} results
        </div>
        @if($applicants->hasPages())
        <div class="flex items-center space-x-2">
            {{-- Previous Page Link --}}
            @if ($applicants->onFirstPage())
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
            @else
                <a href="{{ $applicants->appends(['tab' => 'new', 'search' => request('search'), 'status' => request('status'), 'source' => request('source')])->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($applicants->appends(['tab' => 'new', 'search' => request('search'), 'status' => request('status'), 'source' => request('source')])->getUrlRange(1, $applicants->lastPage()) as $page => $url)
                @if ($page == $applicants->currentPage())
                    <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($applicants->hasMorePages())
                <a href="{{ $applicants->appends(['tab' => 'new', 'search' => request('search'), 'status' => request('status'), 'source' => request('source')])->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            @endif
        </div>
        @endif
    </div>

@endif

<!-- Include Archive Modal -->
@include('hiring_onboarding.tabs.partials.modals.archive_modal')