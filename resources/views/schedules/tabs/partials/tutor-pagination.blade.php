@php
    $queryParams = request()->query();
    unset($queryParams['page']); // Remove existing page parameter
    
    // Build base URL properly
    $baseParams = array_merge($queryParams, ['tab' => 'employee']);
    $baseUrl = route('schedules.index', $baseParams);
    
    // Add proper query string separator
    $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
    
    // Calculate smart pagination range
    $currentPage = $tutors->currentPage();
    $lastPage = $tutors->lastPage();
    
    // If there are too many pages (more than 7), use compact format
    $useCompactPagination = $lastPage > 7;
    
    if ($useCompactPagination) {
        // For compact pagination, we'll show: < current page >
        $startPage = $currentPage;
        $endPage = $currentPage;
    } else {
        // For normal pagination, show range around current page
        $startPage = max(1, $currentPage - 2);
        $endPage = min($lastPage, $currentPage + 2);
        
        // Adjust range if we're near the beginning or end
        if ($endPage - $startPage < 4) {
            if ($startPage == 1) {
                $endPage = min($lastPage, $startPage + 4);
            } else {
                $startPage = max(1, $endPage - 4);
            }
        }
    }
@endphp

@if($tutors->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div class="text-sm text-gray-500">
        @if($tutors->total() > 0)
            Showing {{ $tutors->firstItem() }} to {{ $tutors->lastItem() }} of {{ $tutors->total() }} results
        @else
            Showing 0 results
        @endif
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        {{-- Previous Button --}}
        @if ($tutors->onFirstPage())
            <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $baseUrl . $separator . 'page=' . ($tutors->currentPage() - 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
               data-page="{{ $tutors->currentPage() - 1 }}">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @if($useCompactPagination)
            {{-- Ultra compact pagination: just < current page > --}}
            <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $currentPage }}</button>
        @else
            {{-- Normal pagination with range --}}
            {{-- First page if not in range --}}
            @if($startPage > 1)
                <a href="{{ $baseUrl . $separator . 'page=1' }}"
                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                   data-page="1">
                    1
                </a>
                @if($startPage > 2)
                    <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                @endif
            @endif

            {{-- Page numbers in range --}}
            @for($page = $startPage; $page <= $endPage; $page++)
                @if ($page == $tutors->currentPage())
                    <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center font-medium">{{ $page }}</button>
                @else
                    <a href="{{ $baseUrl . $separator . 'page=' . $page }}"
                       class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                       data-page="{{ $page }}">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Last page if not in range --}}
            @if($endPage < $lastPage)
                @if($endPage < $lastPage - 1)
                    <span class="w-8 h-8 flex items-center justify-center text-gray-400">...</span>
                @endif
                <a href="{{ $baseUrl . $separator . 'page=' . $lastPage }}"
                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
                   data-page="{{ $lastPage }}">
                    {{ $lastPage }}
                </a>
            @endif
        @endif

        {{-- Next Button --}}
        @if ($tutors->hasMorePages())
            <a href="{{ $baseUrl . $separator . 'page=' . ($tutors->currentPage() + 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
               data-page="{{ $tutors->currentPage() + 1 }}">
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
        @if($tutors->total() > 0)
            Showing {{ $tutors->count() }} of {{ $tutors->total() }} results
        @else
            Showing 0 results
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
