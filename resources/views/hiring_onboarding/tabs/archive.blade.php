<!-- Search Filters -->
<div class="px-4 md:px-6 py-4 border-b border-gray-200 overflow-x-auto">
    <form method="GET" action="{{ route('hiring_onboarding.index') }}" id="searchForm"
          class="flex flex-row flex-nowrap items-center gap-4 w-full">

        <input type="hidden" name="tab" value="archive">

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

        <!-- Status select -->
        <select name="status" id="statusSelect"
                class="shrink-0 border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
            <option value="">Select Status</option>
            <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Declined</option>
            <option value="not_recommended" {{ request('status') == 'not_recommended' ? 'selected' : '' }}>Not Recommended</option>
            <option value="no_answer" {{ request('status') == 'no_answer' ? 'selected' : '' }}>No Answer</option>
        </select>

    </form>
</div>

<!-- Archive Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'archive', 'sort' => request('sort') === 'archived_at' && request('direction') === 'desc' ? '' : 'archived_at', 'direction' => request('sort') === 'archived_at' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Archived At
                        @if(request('sort') === 'archived_at')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'archive', 'sort' => request('sort') === 'first_name' && request('direction') === 'desc' ? '' : 'first_name', 'direction' => request('sort') === 'first_name' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'archive', 'sort' => request('sort') === 'status' && request('direction') === 'desc' ? '' : 'status', 'direction' => request('sort') === 'status' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Status
                        @if(request('sort') === 'status')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('hiring_onboarding.index', array_merge(request()->all(), ['tab' => 'archive', 'sort' => request('sort') === 'interview_time' && request('direction') === 'desc' ? '' : 'interview_time', 'direction' => request('sort') === 'interview_time' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                    <div class="flex items-center gap-1">
                        Interview Time
                        @if(request('sort') === 'interview_time')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                        @else
                            <i class="fas fa-sort text-xs opacity-30"></i>
                        @endif
                    </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($archivedApplicants ?? [] as $applicant)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $applicant->archived_at ? $applicant->archived_at->format('M d, Y h:i A') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $applicant->first_name }} {{ $applicant->last_name }}
                    </td>
                    {{-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $applicant->contact_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $applicant->email }}</td> --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            @php
                                $statusColors = [
                                    'declined' => 'bg-[#E02F2F]',
                                    'not_recommended' => 'bg-[#AA1B1B]',
                                    'no_answer' => 'bg-[#FF7515]',
                                    'no_answer_3_attempts' => 'bg-[#FF7515]',
                                    're_schedule' => 'bg-[#A78BFA]',
                                    'rejected' => 'bg-[#F65353]',
                                ];
                                $circleColor = $statusColors[$applicant->status] ?? 'bg-gray-500';
                            @endphp
                            <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                            <span class="text-xs font-medium text-gray-500">
                                {{ ucwords(str_replace('_', ' ', $applicant->status)) }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($applicant->interview_time)
                            {{ $applicant->interview_time->format('M d, Y h:i A') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="{{ route('hiring_onboarding.archived.show', $applicant->id) }}"
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors"
                                    title="View Details">
                            <i class="fas fa-eye text-xs"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        No Archived Applicants Found!
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if(isset($archivedApplicants) && $archivedApplicants->total() >= 6)
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing {{ $archivedApplicants->firstItem() ?? 0 }} to {{ $archivedApplicants->lastItem() ?? 0 }} of {{ $archivedApplicants->total() }} results
        </div>
        @if($archivedApplicants->hasPages())
        <div class="flex items-center space-x-2">
            @php
                $params = [
                    'tab' => 'archive',
                    'sort' => request('sort'),
                    'direction' => request('direction'),
                    'search' => request('search'),
                    'status' => request('status')
                ];
            @endphp
            {{-- Previous Page Link --}}
            @if ($archivedApplicants->onFirstPage())
                <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
            @else
                <a href="{{ $archivedApplicants->appends($params)->previousPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($archivedApplicants->appends($params)->getUrlRange(1, $archivedApplicants->lastPage()) as $page => $url)
                @if ($page == $archivedApplicants->currentPage())
                    <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($archivedApplicants->hasMorePages())
                <a href="{{ $archivedApplicants->appends($params)->nextPageUrl() }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50">
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
@elseif(isset($archivedApplicants) && $archivedApplicants->total() > 0)
<div class="px-6 py-4 border-t border-gray-200">
    <div class="text-sm text-gray-500">
        Showing {{ $archivedApplicants->firstItem() }} to {{ $archivedApplicants->lastItem() }} of {{ $archivedApplicants->total() }} results
    </div>
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusSelect');

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
        
        // Auto-submit form when status changes
        if (statusSelect) {
            statusSelect.addEventListener('change', function() {
                showLoading();
                searchForm.submit();
            });
        }
    });
</script>