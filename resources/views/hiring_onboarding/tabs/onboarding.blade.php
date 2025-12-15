<!-- Page Title -->
@php
    // Ensure variables exist when this tab partial is rendered by different controllers
    $onboardings = $onboardings ?? collect();
    $accounts = $accounts ?? collect();
@endphp

<!-- Search Filters -->
<div class="px-4 md:px-6 py-4 border-b border-gray-200 overflow-x-auto">
    <form method="GET" action="{{ route('hiring_onboarding.index') }}" id="searchForm"
          class="flex flex-row flex-nowrap items-center gap-4 w-full">

        <input type="hidden" name="tab" value="onboarding">

        <!-- Title -->
        <span class="text-sm font-medium text-gray-700 shrink-0">Search Filters:</span>

        <!-- Search input -->
        <div class="relative flex-shrink-0 w-64">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" id="searchIcon"></i>
            <i class="fas fa-spinner fa-spin absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hidden" id="loadingIcon"></i>
            <input type="text" name="search" id="searchInput" placeholder="search name..."
                   value="{{ request('search') }}"
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm
                          focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] focus:ring-0 focus:shadow-xl shrink-0">
        </div>

        <!-- Account select -->
        <select name="account" id="accountSelect"
                class="shrink-0 border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
            <option value="">Select Account</option>
            @foreach($accounts as $account)
                <option value="{{ $account }}" {{ request('account') == $account ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $account)) }}
                </option>
            @endforeach
        </select>

    </form>
</div>

 

<!-- Employee Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'onboarding', 'sort' => request('sort') === 'created_at' && request('direction') === 'desc' ? '' : 'created_at', 'direction' => request('sort') === 'created_at' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Date Approved
                        @if(request('sort') === 'created_at')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'onboarding', 'sort' => request('sort') === 'first_name' && request('direction') === 'desc' ? '' : 'first_name', 'direction' => request('sort') === 'first_name' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Name
                        @if(request('sort') === 'first_name')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th> --}}
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'onboarding', 'sort' => request('sort') === 'assigned_account' && request('direction') === 'desc' ? '' : 'assigned_account', 'direction' => request('sort') === 'assigned_account' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Account
                        @if(request('sort') === 'assigned_account')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'onboarding', 'sort' => request('sort') === 'interview_time' && request('direction') === 'desc' ? '' : 'interview_time', 'direction' => request('sort') === 'interview_time' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Schedule for Interview
                        @if(request('sort') === 'interview_time')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'onboarding', 'sort' => request('sort') === 'status' && request('direction') === 'desc' ? '' : 'status', 'direction' => request('sort') === 'status' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
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
        <tbody class="bg-white divide-y divide-gray-200">
    @forelse ($onboardings as $onboarding)
        <tr class="hover:bg-gray-50">
            <!-- Timestamp/Date Approved -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->created_at ? $onboarding->created_at->format('M d, Y h:i A') : 'N/A' }}
                {{-- {{ $onboarding->moved_to_onboarding_at
                    ? \Carbon\Carbon::parse($onboarding->moved_to_onboarding_at)->format('Y-m-d H:i')
                    : ($onboarding->moved_to_demo_at
                        ? \Carbon\Carbon::parse($onboarding->moved_to_demo_at)->format('Y-m-d H:i')
                        : ($onboarding->created_at
                            ? $onboarding->created_at->format('Y-m-d H:i')
                            : '—')) }} --}}
            </td>

            <!-- Name -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ trim($onboarding->first_name . ' ' . $onboarding->middle_name . ' '. $onboarding->last_name) ?: '—' }}
            </td>

            {{-- <!-- Contact -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->contact_number ?? '—' }}
            </td>

            <!-- Email -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ $onboarding->email ?? '—' }}
            </td> --}}

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

            <!-- Status -->
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    @php
                        $statusColors = [
                            'pending' => 'bg-[#FBBF24]',
                            'rejected' => 'bg-[#F65353]',
                            'no_answer' => 'bg-[#FF7515]',
                            're_schedule' => 'bg-[#A78BFA]',
                            'declined' => 'bg-[#E02F2F]',
                            'not_recommended' => 'bg-[#AA1B1B]',
                            'passed' => 'bg-[#65DB7F]',
                            'failed' => 'bg-[#F65353]',
                            'completed' => 'bg-[#1E40AF]',
                            'screening' => 'bg-[#65DB7F]',
                            'demo' => 'bg-[#FBBF24]',
                            'training' => 'bg-[#9DC9FD]',
                            'onboarding' => 'bg-[#A78BFA]',
                        ];
                        $circleColor = $statusColors[$onboarding->status ?? 'pending'] ?? 'bg-gray-500';
                    @endphp
                    <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                    <span class="text-xs font-medium text-gray-700">
                        {{ ucwords(str_replace('_', ' ', $onboarding->status ?? '—')) }}
                    </span>
                </div>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('hiring_onboarding.applicant.showUneditable', $onboarding->id) }}?tab=onboarding" title="View Details"
                        class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors" aria-label="view">
                        <i class="fas fa-eye text-xs"></i>
                    </a>

                    @if($onboarding->status === 'onboarding')
                        <button onclick="showOnboardingPassFailModal({{ $onboarding->id }}, '{{ $onboarding->first_name }} {{ $onboarding->last_name }}', '{{ $onboarding->email }}', '{{ $onboarding->contact_number }}', '{{ $onboarding->assigned_account }}', '{{ $onboarding->interview_time ? \Carbon\Carbon::parse($onboarding->interview_time)->format('M d, Y h:i A') : ($onboarding->demo_schedule ? \Carbon\Carbon::parse($onboarding->demo_schedule)->format('M d, Y h:i A') : '—') }}', '{{ addslashes($onboarding->notes ?? 'No notes available') }}')"
                            class="w-8 h-8 flex items-center justify-center bg-purple-100 text-purple-600 rounded hover:bg-purple-200 transition-colors" title="Review Onboarding">
                            <i class="fas fa-clipboard-check text-xs"></i>
                        </button>
                    @else
                        <button onclick="loadEditModalData({{ $onboarding->id }})"
                            class="w-8 h-8 flex items-center justify-center bg-yellow-100 text-yellow-600 rounded hover:bg-yellow-200 transition-colors" title="Edit Details">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                No onboarding applications found.
            </td>
        </tr>
    @endforelse
</tbody>

    </table>
</div>

<!-- Pagination -->
@if($onboardings->total() >= 6)
@php
    $queryParams = request()->query();
    unset($queryParams['page']);
    
    $params = [
        'tab' => 'onboarding',
        'sort' => request('sort'),
        'direction' => request('direction'),
        'search' => request('search'),
        'account' => request('account')
    ];
    
    $baseUrl = route('hiring_onboarding.index', $params);
    $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
    
    $currentPage = $onboardings->currentPage();
    $lastPage = $onboardings->lastPage();
    $useCompactPagination = $lastPage > 7;
    
    if (!$useCompactPagination) {
        $startPage = max(1, $currentPage - 2);
        $endPage = min($lastPage, $currentPage + 2);
        
        if ($endPage - $startPage < 4) {
            if ($startPage == 1) {
                $endPage = min($lastPage, $startPage + 4);
            } else {
                $startPage = max(1, $endPage - 4);
            }
        }
    }
@endphp
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full">
    <div class="text-sm text-gray-500">
        @if($onboardings->total() > 0)
            Showing {{ $onboardings->firstItem() }} to {{ $onboardings->lastItem() }} of {{ $onboardings->total() }} results
        @else
            Showing 0 results
        @endif
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        {{-- Previous Button --}}
        @if ($onboardings->onFirstPage())
            <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $baseUrl . $separator . 'page=' . ($onboardings->currentPage() - 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @if($useCompactPagination)
            {{-- Ultra compact pagination --}}
            <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $currentPage }}</button>
        @else
            {{-- Normal pagination with range --}}
            @if($startPage > 1)
                <a href="{{ $baseUrl . $separator . 'page=1' }}"
                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors">
                    1
                </a>
                @if($startPage > 2)
                    <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                @endif
            @endif

            @for($page = $startPage; $page <= $endPage; $page++)
                @if ($page == $onboardings->currentPage())
                    <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $page }}</button>
                @else
                    <a href="{{ $baseUrl . $separator . 'page=' . $page }}"
                       class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                    <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                @endif
                <a href="{{ $baseUrl . $separator . 'page=' . $lastPage }}"
                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors">
                    {{ $lastPage }}
                </a>
            @endif
        @endif

        {{-- Next Button --}}
        @if ($onboardings->hasMorePages())
            <a href="{{ $baseUrl . $separator . 'page=' . ($onboardings->currentPage() + 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
@elseif($onboardings->total() > 0)
<div class="px-6 py-4 border-t border-gray-200">
    <div class="text-sm text-gray-500">
        Showing {{ $onboardings->firstItem() }} to {{ $onboardings->lastItem() }} of {{ $onboardings->total() }} results
    </div>
</div>
@endif

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

