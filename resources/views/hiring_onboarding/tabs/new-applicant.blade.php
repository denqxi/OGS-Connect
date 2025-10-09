@if (request('view') === 'details')
    {{-- Show applicant details --}}
    @include('hiring_onboarding.tabs.partials.applicant-details')
@else

    <!-- Page Title -->
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-800">New Applicants</h2>
    </div>
    <!-- Search Filters -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
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
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
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
        <table class="w-full" style="table-layout: fixed; min-width: 1200px;">
            <thead class="bg-gray-50 border-b border-gray-200">
          
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Timestamp</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 15%;">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Phone</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 18%;">Email</th>
                    <th class="px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 16%;">Available Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 12%;">Notes</th>
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
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $applicant->contact_number }}</td>
                        <td class="px-4 py-4 text-sm text-gray-600" style="word-wrap: break-word;">{{ $applicant->email }}</td>
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
                                    {{ $abbreviatedDays }} | {{ $applicant->start_time }} - {{ $applicant->end_time }}
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
                            @php
                                // Notes logic: if notes is blank or status is pending, show interview time and date
                                // if status is no_answer, show blank (no interview time to display)
                                // if it has new values or meeting up with requested information, it must be blank
                                $showInterviewTime = (empty($applicant->notes) || $applicant->status === 'pending') && $applicant->status !== 'no_answer';
                            @endphp
                            
                            @if($showInterviewTime && !empty($applicant->interview_time))
                                {{ \Carbon\Carbon::parse($applicant->interview_time)->format('M d, Y h:i A') }}
                            @elseif(!empty($applicant->notes))
                                {{ \Illuminate\Support\Str::limit($applicant->notes, 50) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $applicant->statusColor() }}">
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
                        </td>
                        <td class="px-4 py-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <!-- View Button -->
                                <a href="{{ route('hiring_onboarding.applicant.show', $applicant->id) }}"
                                    class="w-8 h-8 flex items-center justify-center bg-[#9DC9FD] text-[#2C5B8C] rounded hover:bg-[#7BB4FB] transition-colors">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <!-- Archive Button -->
                                <button
                                    onclick="openArchiveModal({{ $applicant->id }})"
                                    class="w-8 h-8 bg-[#F29090] text-[#7A1F1F] rounded hover:bg-[#E67878] transition-colors"
                                    title="Archive Applicant">
                                    <i class="fas fa-archive text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            No Applicants Found!
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
                <a href="{{ $applicants->previousPageUrl() }}&tab=new" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($applicants->getUrlRange(1, $applicants->lastPage()) as $page => $url)
                @if ($page == $applicants->currentPage())
                    <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
                @else
                    <a href="{{ $url }}&tab=new" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($applicants->hasMorePages())
                <a href="{{ $applicants->nextPageUrl() }}&tab=new" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
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