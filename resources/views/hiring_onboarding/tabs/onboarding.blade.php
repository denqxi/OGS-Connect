<!-- Page Title -->
<div class="p-4 border-b border-gray-200">
    <h2 class="text-xl font-bold text-gray-800">Onboarding</h2>
</div>

@php
    // Ensure variables exist when this tab partial is rendered by different controllers
    $onboardings = $onboardings ?? collect();
    $accounts = $accounts ?? collect();
@endphp

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    <form method="GET" action="{{ route('hiring_onboarding.index') }}" id="searchForm" class="flex justify-between items-center space-x-4">
        <input type="hidden" name="tab" value="onboarding">
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule for Interview</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
    @forelse ($onboardings as $onboarding)
        <tr class="hover:bg-gray-50">
            <!-- Timestamp -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->moved_to_onboarding_at
                    ? \Carbon\Carbon::parse($onboarding->moved_to_onboarding_at)->format('Y-m-d H:i')
                    : ($onboarding->moved_to_demo_at
                        ? \Carbon\Carbon::parse($onboarding->moved_to_demo_at)->format('Y-m-d H:i')
                        : ($onboarding->created_at
                            ? $onboarding->created_at->format('Y-m-d H:i')
                            : '—')) }}
            </td>

            <!-- Name -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ trim($onboarding->first_name . ' ' . $onboarding->last_name) ?: '—' }}
            </td>

            <!-- Contact -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->contact_number ?? '—' }}
            </td>

            <!-- Email -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->email ?? '—' }}
            </td>

            <!-- Account -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->assigned_account ?? '—' }}
            </td>

            <!-- Schedule (interview date & time) -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                @if(!empty($onboarding->interview_time))
                    {{ \Carbon\Carbon::parse($onboarding->interview_time)->format('M d, Y h:i A') }}
                @elseif(!empty($onboarding->demo_schedule))
                    {{ \Carbon\Carbon::parse($onboarding->demo_schedule)->format('M d, Y h:i A') }}
                @elseif(!empty($onboarding->start_time) && !empty($onboarding->end_time))
                    {{ $onboarding->start_time }} - {{ $onboarding->end_time }}
                @else
                    —
                @endif
            </td>

            <!-- Notes -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                @php
                    // Notes logic for onboarding: if fail value or reason is missed, notes will show "Missed" but can be changed
                    $showMissed = $onboarding->status === 'failed' && (empty($onboarding->notes) || $onboarding->notes === 'Missed');
                @endphp
                
                @if($showMissed)
                    <span class="text-red-600 font-medium">Missed</span>
                @elseif(!empty($onboarding->notes))
                    {{ \Illuminate\Support\Str::limit($onboarding->notes, 50) }}
                @else
                    —
                @endif
            </td>

            <!-- Status -->
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $onboarding->statusColor() }}">
                    {{ ucwords(str_replace('_', ' ', $onboarding->status ?? '—')) }}
                </span>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('hiring_onboarding.applicant.showUneditable', $onboarding->id) }}?tab=onboarding" title="View Details"
                        class="w-8 h-8 flex items-center justify-center bg-[#9DC9FD] text-[#2C5B8C] rounded hover:bg-[#7BB4FB] transition-colors" aria-label="view">
                        <i class="fas fa-eye text-xs"></i>
                    </a>

                    <button onclick="showOnboardingPassFailModal({{ $onboarding->id }}, '{{ $onboarding->first_name }} {{ $onboarding->last_name }}', '{{ $onboarding->assigned_account }}', '{{ $onboarding->interview_time ? \Carbon\Carbon::parse($onboarding->interview_time)->format('M d, Y h:i A') : ($onboarding->demo_schedule ? \Carbon\Carbon::parse($onboarding->demo_schedule)->format('M d, Y h:i A') : '—') }}', '{{ $onboarding->email }}')"
                        class="w-8 h-8 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200 transition-colors" title="Update Hiring Stage">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                No onboarding applications found.
            </td>
        </tr>
    @endforelse
</tbody>

    </table>
</div>

<!-- Pagination -->
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        Showing {{ $onboardings->firstItem() ?? 0 }} to {{ $onboardings->lastItem() ?? 0 }} of {{ $onboardings->total() }} results
    </div>
    @if($onboardings->hasPages())
    <div class="flex items-center space-x-2">
        {{-- Previous Page Link --}}
        @if ($onboardings->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $onboardings->previousPageUrl() }}&tab=onboarding" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($onboardings->getUrlRange(1, $onboardings->lastPage()) as $page => $url)
            @if ($page == $onboardings->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}&tab=onboarding" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($onboardings->hasMorePages())
            <a href="{{ $onboardings->nextPageUrl() }}&tab=onboarding" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
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
        
        // Auto-submit form when account changes
        if (accountSelect) {
            accountSelect.addEventListener('change', function() {
                showLoading();
                searchForm.submit();
            });
        }
    });
</script>

