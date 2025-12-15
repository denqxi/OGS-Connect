<div id="payrollWorkDetailsInner">
    <div class="overflow-x-auto">
        <table class="w-full" id="tutorTable">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['sort' => request('sort') === 'day' && request('direction') === 'desc' ? '' : 'day', 'direction' => request('sort') === 'day' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Date
                            @if(request('sort') === 'day')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['sort' => request('sort') === 'tutor_name' && request('direction') === 'desc' ? '' : 'tutor_name', 'direction' => request('sort') === 'tutor_name' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Name
                            @if(request('sort') === 'tutor_name')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['sort' => request('sort') === 'start_time' && request('direction') === 'desc' ? '' : 'start_time', 'direction' => request('sort') === 'start_time' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Start Time - End Time
                            @if(request('sort') === 'start_time')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="window.location.href='{{ route('payroll.index', array_merge(request()->query(), ['sort' => request('sort') === 'rate' && request('direction') === 'desc' ? '' : 'rate', 'direction' => request('sort') === 'rate' ? (request('direction') === 'asc' ? 'desc' : '') : 'asc'])) }}'">
                        <div class="flex items-center gap-1">
                            Rate
                            @if(request('sort') === 'rate')
                                <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }} text-xs"></i>
                            @else
                                <i class="fas fa-sort text-xs opacity-30"></i>
                            @endif
                        </div>
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
                @php
                    $rows = $workDetails ?? collect();
                @endphp

                @forelse($rows as $detail)
                    @php
                        $tutor = $detail->tutor;
                        // Format date as "December 10, 2025"
                        $dateObj = $detail->day ? \Carbon\Carbon::parse($detail->day) : ($detail->created_at ?? null);
                        $displayDate = $dateObj ? $dateObj->format('F j, Y') : 'N/A';
                        
                        $StartTime = $detail->start_time ? \Carbon\Carbon::parse($detail->start_time)->format('g:i A') : '—';
                        $EndTime = $detail->end_time ? \Carbon\Carbon::parse($detail->end_time)->format('g:i A') : '—';
                        $status = $detail->status ?? 'pending';
                        
                        // Per Class and Rate
                        $perClass = $detail->class_no ?? 'N/A';
                        $rate = $detail->rate_per_class ?? $detail->rate_per_hour ?? 'N/A';
                    @endphp
                    <tr class="hover:bg-gray-50 tutor-row" data-searchable="{{ strtolower(($tutor->tutorID ?? '') . ' ' . ($tutor->email ?? '')) }}">
                        <!-- Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $displayDate }}</td>
                        
                        <!-- Name -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tutor?->full_name ?? ($tutor?->username ?? 'N/A') }}</td>
                        
                        <!-- Start Time - End Time -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $StartTime }} - {{ $EndTime }}</td>
                        
                        <!-- Rate -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            {{ is_numeric($rate) ? '₱' . number_format($rate, 2) : $rate }}
                        </td>
                        
                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors"
                                    onclick="openPayrollDetailModal({{ json_encode($detail) }}, '{{ $status }}')"
                                    title="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="noResultsRow">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-users text-4xl mb-4 opacity-50"></i>
                            <p class="text-lg font-medium">No work details found</p>
                            <p class="text-sm">Try adjusting your search criteria</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @php
            $queryParams = request()->query();
            unset($queryParams['page']);
            $baseUrl = route('payroll.work-details', $queryParams);
            $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
            $currentPage = isset($workDetails) && method_exists($workDetails, 'currentPage') ? $workDetails->currentPage() : 1;
            $lastPage = isset($workDetails) && method_exists($workDetails, 'lastPage') ? $workDetails->lastPage() : 1;
            $totalRows = isset($workDetails) && method_exists($workDetails, 'total') ? $workDetails->total() : (isset($workDetails) ? $workDetails->count() : 0);
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
        @if(isset($workDetails) && method_exists($workDetails, 'hasPages') && $workDetails->hasPages() && $totalRows >= 5)
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full">
                <div class="text-sm text-gray-500">
                    @if($totalRows > 0)
                        Showing {{ $workDetails->firstItem() }} to {{ $workDetails->lastItem() }} of {{ $totalRows }} results
                    @else
                        Showing 0 results
                    @endif
                </div>
                <div class="flex items-center justify-center space-x-2 w-[300px]">
                    @if ($workDetails->onFirstPage())
                        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                            <i class="fas fa-chevron-left"></i>
                        </button>
                    @else
                        <a href="{{ $baseUrl . $separator . 'page=' . ($currentPage - 1) }}"
                           class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                           data-page="{{ $currentPage - 1 }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @if($useCompactPagination)
                        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $currentPage }}</button>
                    @else
                        @if($startPage > 1)
                            <a href="{{ $baseUrl . $separator . 'page=1' }}"
                               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                               data-page="1">1</a>
                            @if($startPage > 2)
                                <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $startPage; $page <= $endPage; $page++)
                            @if ($page == $currentPage)
                                <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $page }}</button>
                            @else
                                <a href="{{ $baseUrl . $separator . 'page=' . $page }}"
                                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                                   data-page="{{ $page }}">{{ $page }}</a>
                            @endif
                        @endfor

                        @if($endPage < $lastPage)
                            @if($endPage < $lastPage - 1)
                                <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                            @endif
                            <a href="{{ $baseUrl . $separator . 'page=' . $lastPage }}"
                               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                               data-page="{{ $lastPage }}">{{ $lastPage }}</a>
                        @endif
                    @endif

                    @if ($workDetails->hasMorePages())
                        <a href="{{ $baseUrl . $separator . 'page=' . ($currentPage + 1) }}"
                           class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                           data-page="{{ $currentPage + 1 }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    @endif
                </div>
            </div>
        @elseif($totalRows > 0)
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    Showing {{ isset($workDetails) && method_exists($workDetails, 'firstItem') ? $workDetails->firstItem() : 1 }} to {{ isset($workDetails) && method_exists($workDetails, 'lastItem') ? $workDetails->lastItem() : $totalRows }} of {{ $totalRows }} results
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Payroll Work Detail Modal -->
<div id="payrollWorkDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h2 class="text-xl font-bold">Work Details</h2>
            <button type="button" onclick="closePayrollDetailModal()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="overflow-y-auto flex-grow">
            <div class="p-6">
                <!-- Schedule Information Card -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 mb-6 border border-purple-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>
                        Schedule Information
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-date">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Day</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-day">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">School</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-school">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-class">-</p>
                        </div>
                    </div>
                </div>

                <!-- Tutor Information Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 mb-6 border border-blue-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-blue-600 mr-2"></i>
                        Tutor Information
                    </h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Name</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-name">-</p>
                        </div>
                    </div>
                </div>

                <!-- Time & Rate Information Card -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 mb-6 border border-green-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-green-600 mr-2"></i>
                        Time & Rate Details
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Start Time</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-start-time">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">End Time</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-end-time">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Hours</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-hours">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Rate</label>
                            <p class="text-sm font-semibold text-gray-800" id="payroll-modal-rate">-</p>
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Amount</label>
                            <p class="text-lg font-bold text-green-700" id="payroll-modal-amount">-</p>
                        </div>
                    </div>
                </div>

                <!-- Proof of Work Card -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 border border-purple-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-image text-purple-600 mr-2"></i>
                        Proof of Work
                    </h3>
                    
                    <div class="flex justify-center" id="payroll-modal-proof-container">
                        <p class="text-gray-500 text-center py-8">No proof image uploaded</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Actions -->
        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t flex-shrink-0">
            <div class="flex gap-3" id="payroll-modal-action-buttons">
                <!-- Dynamic buttons will be inserted here -->
            </div>
            
            <button type="button" onclick="closePayrollDetailModal()" class="px-6 py-2 bg-gray-500 text-white rounded-md font-medium hover:bg-gray-600 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>
