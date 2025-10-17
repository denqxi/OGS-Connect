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
    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
        {{ $data->schools ?? '-' }}
        @if(($data->cancelled_class_count ?? 0) > 0)
            <div class="inline-flex items-center ml-2">
                <span class="px-1.5 py-0.5 bg-red-100 text-red-600 rounded text-xs font-medium" title="{{ $data->cancelled_class_count }} cancelled class(es)">
                    <i class="fas fa-times-circle mr-1"></i>{{ $data->cancelled_class_count }} cancelled
                </span>
            </div>
        @endif
    </td>
    <td class="px-6 py-4 text-sm text-center text-gray-500">{{ ($data->active_class_count ?? 0) + ($data->cancelled_class_count ?? 0) }}</td>
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
            $assignedSupervisors = $data->assigned_supervisors ?? '';
            if ($assignedSupervisors) {
                $supervisorIds = explode(', ', $assignedSupervisors);
                $supervisorNames = [];
                foreach ($supervisorIds as $supervisorId) {
                    $supervisor = \App\Models\Supervisor::where('supID', $supervisorId)->first();
                    if ($supervisor) {
                        $supervisorNames[] = $supervisor->full_name;
                    }
                }
                $displaySupervisors = implode(', ', $supervisorNames);
            } else {
                $displaySupervisors = 'Unassigned';
            }
        @endphp
        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $assignedSupervisors ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
            {{ $displaySupervisors }}
        </span>
    </td>
    <td class="px-6 py-4 text-sm">
        @php
            $viewDate = $data->date;
            if ($viewDate instanceof \Carbon\Carbon) {
                $viewDate = $viewDate->format('Y-m-d');
            } else {
                $viewDate = \Carbon\Carbon::parse($viewDate)->format('Y-m-d');
            }
            
            // Check if current supervisor owns this schedule
            $currentSupervisorId = session('supervisor_id');
            if (!$currentSupervisorId && auth('supervisor')->check()) {
                $currentSupervisorId = auth('supervisor')->user()->supID;
            }
            
            $canModify = true;
            if ($currentSupervisorId && $data->assigned_supervisors) {
                $assignedSupervisors = explode(', ', $data->assigned_supervisors);
                $canModify = in_array($currentSupervisorId, $assignedSupervisors);
            }
        @endphp
        
        <div class="flex items-center space-x-2">
            <!-- View Button (always available) -->
            <a href="{{ route('schedules.index', ['tab' => 'class', 'view_date' => $viewDate]) }}" 
               class="w-8 h-8 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
               title="View Schedule">
                <i class="fas fa-eye text-xs"></i>
            </a>
            
            <!-- Ownership Indicator -->
            @if($data->assigned_supervisors && !$canModify)
                <div class="w-8 h-8 bg-gray-100 text-gray-400 rounded inline-flex items-center justify-center"
                     title="Owned by Another Supervisor - View Only">
                    <i class="fas fa-user-lock text-xs"></i>
                </div>
            @endif
        </div>
    </td>
</tr>
@empty
<tr id="noResultsRow">
    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
        <p class="text-lg font-medium">No scheduling data found</p>
        <p class="text-sm">Try adjusting your search criteria</p>
    </td>
</tr>
@endforelse