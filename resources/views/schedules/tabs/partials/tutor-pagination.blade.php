@php
    $queryParams = request()->query();
    unset($queryParams['page']); // Remove existing page parameter
    $baseUrl = route('schedules.index', array_merge($queryParams, ['tab' => 'employee']));
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
        @if ($tutors->onFirstPage())
            <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $baseUrl . '&page=' . ($tutors->currentPage() - 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center"
               data-page="{{ $tutors->currentPage() - 1 }}">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($tutors->getUrlRange(1, $tutors->lastPage()) as $page => $url)
            @if ($page == $tutors->currentPage())
                <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">{{ $page }}</button>
            @else
                <a href="{{ $baseUrl . '&page=' . $page }}"
                   class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center"
                   data-page="{{ $page }}">
                    {{ $page }}
                </a>
            @endif
        @endforeach

        @if ($tutors->hasMorePages())
            <a href="{{ $baseUrl . '&page=' . ($tutors->currentPage() + 1) }}"
               class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-center"
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
        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif
