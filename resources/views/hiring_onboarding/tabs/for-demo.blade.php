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
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $status)) }}
                    </option>
                @endforeach
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
        @forelse($demos as $demo)
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $demo->moved_to_demo_at ? $demo->moved_to_demo_at->format('Y-m-d H:i') : 'N/A' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ $demo->first_name }} {{ $demo->last_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $demo->contact_number }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $demo->email }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ ucwords(str_replace('_', ' ', $demo->assigned_account ?? 'N/A')) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                @if($demo->demo_schedule)
                    {{ $demo->demo_schedule->format('M d, Y H:i') }}
                @else
                    {{ $demo->start_time }} - {{ $demo->end_time }}
                @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ Str::limit($demo->notes ?? 'No notes', 20) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $demo->statusColor() }}">
                    {{ ucwords(str_replace('_', ' ', $demo->status)) }}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center space-x-2">
                    <!-- View Detail Applicant Button -->
                    <a href="{{ route('hiring_onboarding.applicant.showUneditable', $demo->id) }}"
                        class="w-8 h-8 flex items-center justify-center bg-[#9DC9FD] text-[#2C5B8C] rounded hover:bg-[#7BB4FB] transition-colors" title="View Details">
                        <i class="fas fa-eye text-xs"></i>
                    </a>
                    <!-- Edit Button -->
                    @if($demo->status === 'onboarding')
                        <button 
                            type="button"
                            onclick="showOnboardingConfirmationModal({{ $demo->id }}, '{{ $demo->first_name }} {{ $demo->last_name }}')"
                            class="w-8 h-8 flex items-center justify-center bg-purple-100 text-purple-600 rounded hover:bg-purple-200 transition-colors"
                            title="Review Onboarding"
                        >
                            <i class="fas fa-clipboard-check text-xs"></i>
                        </button>
                    @elseif($demo->status === 'demo')
                        <button 
                            type="button"
                            onclick="showDemoDetailsConfirmation({{ $demo->id }}, '{{ $demo->first_name }} {{ $demo->last_name }}')"
                            class="w-8 h-8 flex items-center justify-center bg-violet-100 text-violet-600 rounded hover:bg-violet-200 transition-colors"
                            title="Review Demo & Confirm Decision"
                        >
                            <i class="fas fa-clipboard-check text-xs"></i>
                        </button>
                    @else
                        <button 
                            type="button"
                            onclick="loadEditModalData({{ $demo->id }})"
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
        Showing {{ $demos->firstItem() ?? 0 }} to {{ $demos->lastItem() ?? 0 }} of {{ $demos->total() }} results
    </div>
    @if($demos->hasPages())
    <div class="flex items-center space-x-2">
        {{-- Previous Page Link --}}
        @if ($demos->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $demos->previousPageUrl() }}&tab=demo" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($demos->getUrlRange(1, $demos->lastPage()) as $page => $url)
            @if ($page == $demos->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}&tab=demo" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($demos->hasMorePages())
            <a href="{{ $demos->nextPageUrl() }}&tab=demo" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
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