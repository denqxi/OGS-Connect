@php
    $queryParams = request()->query();
    unset($queryParams['page']); // Remove existing page parameter
    
    // Build base URL properly
    $baseParams = array_merge($queryParams, ['tab' => 'class']);
    $baseUrl = route('schedules.index', $baseParams);
    
    // Add proper query string separator
    $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
    
    // Calculate smart pagination range
    $currentPage = $dailyData->currentPage();
    $lastPage = $dailyData->lastPage();
    
    // Always use compact format for consistency
@endphp

@if(isset($dailyData) && method_exists($dailyData, 'hasPages') && $dailyData->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection" data-pagination-version="compact-v2">
    <div class="flex items-center space-x-4">
        <div class="text-sm text-gray-500">
            @if($dailyData->total() > 0)
                Showing {{ $dailyData->firstItem() }} to {{ $dailyData->lastItem() }} of {{ $dailyData->total() }} results
            @else
                Showing 0 results
            @endif
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-600">Rows per page:</span>
            <select onchange="changeRowsPerPage(this.value)" class="border border-gray-300 rounded-md px-2 py-1 text-sm text-gray-600 bg-white">
                <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
            </select>
        </div>
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        {{-- Previous Button --}}
        @if ($dailyData->onFirstPage())
            <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $baseUrl . $separator . 'page=' . ($dailyData->currentPage() - 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
               data-page="{{ $dailyData->currentPage() - 1 }}">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        {{-- Ultra compact pagination: just < current page > --}}
        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $currentPage }}</button>

        {{-- Next Button --}}
        @if ($dailyData->hasMorePages())
            <a href="{{ $baseUrl . $separator . 'page=' . ($dailyData->currentPage() + 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
               data-page="{{ $dailyData->currentPage() + 1 }}">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div class="text-sm text-gray-500">
        @if(isset($dailyData) && method_exists($dailyData, 'total') && $dailyData->total() > 0)
            Showing {{ count($dailyData) }} of {{ $dailyData->total() }} results
        @else
            Showing {{ count($dailyData ?? []) }} results
        @endif
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">1</button>
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif
