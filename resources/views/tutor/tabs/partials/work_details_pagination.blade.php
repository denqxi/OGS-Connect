@php
    $queryParams = request()->query();
    unset($queryParams['page']);

    // Build base URL with tab=work_details
    $baseParams = array_merge($queryParams, ['tab' => 'work_details']);
    $baseUrl = route('tutor.portal', $baseParams);
    $separator = strpos($baseUrl, '?') !== false ? '&' : '?';

    $currentPage = $workDetails->currentPage();
    $lastPage = $workDetails->lastPage();
    $useCompactPagination = $lastPage > 7;

    if ($useCompactPagination) {
        $startPage = $currentPage;
        $endPage = $currentPage;
    } else {
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

@if($workDetails->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div class="text-sm text-gray-500">
        @if($workDetails->total() > 0)
            Showing {{ $workDetails->firstItem() }} to {{ $workDetails->lastItem() }} of {{ $workDetails->total() }} results
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
            <a href="{{ $baseUrl . $separator . 'page=' . ($workDetails->currentPage() - 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
               data-page="{{ $workDetails->currentPage() - 1 }}">
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
                @if ($page == $workDetails->currentPage())
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
            <a href="{{ $baseUrl . $separator . 'page=' . ($workDetails->currentPage() + 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center transition-colors"
               data-page="{{ $workDetails->currentPage() + 1 }}">
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
        @if($workDetails->total() > 0)
            Showing {{ $workDetails->count() }} of {{ $workDetails->total() }} results
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
