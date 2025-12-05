@if(isset($dailyData) && method_exists($dailyData, 'hasPages') && $dailyData->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        Showing {{ $dailyData->firstItem() }} to {{ $dailyData->lastItem() }} of {{ $dailyData->total() }} entries
    </div>
    <div class="flex items-center space-x-2">
        @if ($dailyData->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $dailyData->appends(request()->query())->previousPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($dailyData->appends(request()->query())->getUrlRange(1, $dailyData->lastPage()) as $page => $url)
            @if ($page == $dailyData->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        @if ($dailyData->hasMorePages())
            <a href="{{ $dailyData->appends(request()->query())->nextPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
</div>
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        @php
            $total = count($dailyData ?? []);
        @endphp
        Showing {{ $total > 0 ? 1 : 0 }} to {{ $total }} of {{ $total }} entries
    </div>
    <div class="flex items-center space-x-2">
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">1</button>
        <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif
