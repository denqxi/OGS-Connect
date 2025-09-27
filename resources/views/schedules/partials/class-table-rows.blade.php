@forelse($dailyData ?? [] as $data)
<tr class="hover:bg-gray-50 table-row" data-searchable="{{ strtolower($data->schools ?? '') }}">
    <td class="px-6 py-4 text-sm font-medium text-gray-900">
        {{ $data->date ? \Carbon\Carbon::parse($data->date)->format('F j, Y') : '-' }}
    </td>
    <td class="px-6 py-4 text-sm text-gray-500">
        @php
            $dayMap = [
                'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                'thur' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'
            ];
        @endphp
        {{ $dayMap[$data->day] ?? $data->day }}
    </td>
    <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $data->schools ?? '-' }}</td>
    <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $data->class_count ?? 0 }}</td>
    <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $data->total_required ?? 0 }}</td>
    <td class="px-6 py-4 text-sm">
        @php
            $totalAssigned = $data->total_assigned ?? 0;
            $totalRequired = $data->total_required ?? 0;
            
            if ($totalAssigned == 0) {
                $statusText = 'Not Assigned';
                $statusColor = 'bg-red-100 text-red-800';
            } elseif ($totalAssigned >= $totalRequired) {
                $statusText = 'Fully Assigned';
                $statusColor = 'bg-green-100 text-green-800';
            } else {
                $statusText = 'Partially Assigned';
                $statusColor = 'bg-yellow-100 text-yellow-800';
            }
        @endphp
        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
            {{ $statusText }}
        </span>
        <div class="text-xs text-gray-500 mt-1">{{ $totalAssigned }}/{{ $totalRequired }}</div>
    </td>
    <td class="px-6 py-4 text-sm">
        @php
            $viewDate = $data->date;
            if ($viewDate instanceof \Carbon\Carbon) {
                $viewDate = $viewDate->format('Y-m-d');
            } else {
                $viewDate = \Carbon\Carbon::parse($viewDate)->format('Y-m-d');
            }
        @endphp
        <a href="{{ route('schedules.index', ['tab' => 'class', 'view_date' => $viewDate]) }}" 
           class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors">
            <i class="fas fa-search text-xs"></i>
        </a>
    </td>
</tr>
@empty
<tr id="noResultsRow">
    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
        <p class="text-lg font-medium">No scheduling data found</p>
        <p class="text-sm">Try adjusting your search criteria</p>
    </td>
</tr>
@endforelse