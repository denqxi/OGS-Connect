<div>
<!-- Page Title -->
<div class="p-4 border-b border-gray-200">
    <h2 class="text-xl font-bold text-gray-800">For Demo</h2>
</div>

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    
    <form method="GET" action="{{ route('hiring_onboarding.index') }}" id="searchForm" class="flex justify-between items-center space-x-4">
        <input type="hidden" name="tab" value="demo">
        <!-- Left side -->
        <div class="flex items-center space-x-4 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" id="searchIcon"></i>
                <i class="fas fa-spinner fa-spin absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hidden" id="loadingIcon"></i>
                <input type="text" name="search" id="searchInput" placeholder="search name..." value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
              focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
              focus:ring-0 focus:shadow-xl">
            </div>
            <select name="status" id="statusSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Select Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Passed</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="no_answer" {{ request('status') == 'no_answer' ? 'selected' : '' }}>No Answer</option>
                <option value="re_schedule" {{ request('status') == 're_schedule' ? 'selected' : '' }}>Re-schedule</option>
                <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
                <option value="not_recommended" {{ request('status') == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
            </select>
            <select name="account" id="accountSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Select Account</option>
                @foreach($accounts as $account)
                    <option value="{{ $account }}" {{ request('account') == $account ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $account)) }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<!-- Employee Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Available Schedule</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($screenings as $screening)
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $screening->screening_date_time ? $screening->screening_date_time->format('M d, Y h:i A') : 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ $screening->first_name }} {{ $screening->last_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $screening->contact_number }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $screening->email }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ ucwords(str_replace('_', ' ', $screening->assigned_account ?? 'N/A')) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                @if($screening->screening_date_time)
                    {{ $screening->screening_date_time->format('M d, Y H:i') }}
                @else
                    {{ $screening->start_time }} - {{ $screening->end_time }}
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ Str::limit($screening->notes ?? 'No notes', 20) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-400',
                            'rejected' => 'bg-red-500',
                            'no_answer' => 'bg-orange-500',
                            're_schedule' => 'bg-purple-400',
                            'declined' => 'bg-red-600',
                            'not_recommended' => 'bg-red-700',
                            'passed' => 'bg-green-500',
                            'failed' => 'bg-red-500',
                        ];
                        $circleColor = $statusColors[$screening->status] ?? 'bg-gray-500';
                    @endphp
                    <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                    <span class="text-xs font-medium text-gray-700">
                        {{ ucwords(str_replace('_', ' ', $screening->status)) }}
                    </span>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center space-x-2">
                    <!-- View Detail Applicant Button -->
                    <a href="{{ route('hiring_onboarding.applicant.showUneditable', $screening->id) }}"
                        class="w-8 h-8 flex items-center justify-center bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors" title="View Details">
                        <i class="fas fa-eye text-xs"></i>
                    </a>
                    <!-- Edit Button -->
                    @if($screening->status === 'onboarding')
                        <button 
                            type="button"
                            onclick="showOnboardingConfirmationModal({{ $screening->id }}, '{{ $screening->first_name }} {{ $screening->last_name }}')"
                            class="w-8 h-8 flex items-center justify-center bg-purple-100 text-purple-600 rounded hover:bg-purple-200 transition-colors"
                            title="Review Onboarding"
                        >
                            <i class="fas fa-clipboard-check text-xs"></i>
                        </button>
                    @elseif($screening->status === 'demo')
                        <button 
                            type="button"
                            onclick="showDemoDetailsConfirmation({{ $screening->id }}, '{{ $screening->first_name }} {{ $screening->last_name }}')"
                            class="w-8 h-8 flex items-center justify-center bg-violet-100 text-violet-600 rounded hover:bg-violet-200 transition-colors"
                            title="Review Demo & Confirm Decision"
                        >
                            <i class="fas fa-clipboard-check text-xs"></i>
                        </button>
                    @else
                        <button 
                            type="button"
                            onclick="loadEditModalData({{ $screening->id }})"
                            class="w-8 h-8 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200 transition-colors"
                            title="Edit"
                        >
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                    @endif

                    
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                No for demo applications found.
            </td>
        </tr>
        @endforelse
    </tbody>

    </table>
</div>

<!-- Pagination -->
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        Showing {{ $screenings->firstItem() ?? 0 }} to {{ $screenings->lastItem() ?? 0 }} of {{ $screenings->total() }} results
    </div>
    @if($screenings->hasPages())
    <div class="flex items-center space-x-2">
        {{-- Previous Page Link --}}
        @if ($screenings->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $screenings->previousPageUrl() }}&tab=demo" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($screenings->getUrlRange(1, $screenings->lastPage()) as $page => $url)
            @if ($page == $screenings->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}&tab=demo" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($screenings->hasMorePages())
            <a href="{{ $screenings->nextPageUrl() }}&tab=demo" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
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

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusSelect');
        const accountSelect = document.getElementById('accountSelect');

        const searchForm = document.getElementById('searchForm');
        const searchIcon = document.getElementById('searchIcon');
        const loadingIcon = document.getElementById('loadingIcon');
        
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
        if (searchIcon) {
            searchIcon.addEventListener('click', function() {
                performSearch();
            });
        }
        
        // Auto-submit form when status or account changes
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                showLoading();
                searchForm.submit();
            });
        }
        
        if (accountSelect) {
            accountSelect.addEventListener('change', function() {
                showLoading();
                searchForm.submit();
            });
        }
    });
</script>