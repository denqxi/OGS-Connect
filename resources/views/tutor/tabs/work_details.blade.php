<div class="p-6 border-b border-gray-200">
    <h2 class="text-xl font-semibold text-gray-800">Work Details</h2>
</div>

<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    <div class="flex justify-between items-center space-x-4">
        <div class="flex items-center space-x-4 flex-1 max-w-lg">
            <form method="GET" action="{{ route('tutor.portal') }}" class="relative flex-1">
                <input type="hidden" name="tab" value="work_details">
                <select name="status" id="filterStatus"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white"
                    onchange="this.form.submit()">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Work Details Table -->
<div class="overflow-x-auto">
    <table class="w-full" id="tutorTable">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Start</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual End</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor Note</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="tutorTableBody">
            @php
                $rows = $workDetails ?? collect();
            @endphp

            @forelse($rows as $assignment)
                @php
                    $schedule = $assignment->schedule;
                    
                    // Status primarily from tutor_work_details (fallback to assignment class_status)
                    $workDetail = $assignment->workDetail ?? null;
                    $detailStatus = $workDetail->status ?? null;
                    $statusMap = [
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'not_assigned' => 'Not Assigned',
                        'partially_assigned' => 'Pending',
                        'pending_acceptance' => 'Pending Acceptance',
                        'fully_assigned' => 'Pending',
                    ];
                    $statusColors = [
                        'pending' => 'bg-yellow-400',
                        'approved' => 'bg-green-500',
                        'rejected' => 'bg-red-500',
                        'not_assigned' => 'bg-gray-400',
                        'partially_assigned' => 'bg-yellow-400',
                        'pending_acceptance' => 'bg-orange-400',
                        'fully_assigned' => 'bg-yellow-400',
                    ];
                    $statusKey = $detailStatus ?? $assignment->class_status;
                    $status = $statusMap[$statusKey] ?? ucfirst($statusKey ?? 'Pending');
                    $circleColor = $statusColors[$statusKey] ?? 'bg-gray-500';
                    
                    // Check if tutor has submitted work details for this assignment
                    $hasSubmitted = $workDetail !== null;
                    // Only approved if work detail status is 'approved' (from supervisor in payroll tab)
                    $isApproved = $hasSubmitted && strtolower($workDetail->status ?? '') === 'approved';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $schedule ? \Carbon\Carbon::parse($schedule->date)->format('M d, Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $schedule ? \Carbon\Carbon::parse($schedule->date)->format('l') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $schedule ? \Carbon\Carbon::parse($schedule->time)->format('g:i A') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $schedule->school ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $schedule->duration ?? 'N/A' }} min</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ $workDetail && $workDetail->start_time ? \Carbon\Carbon::parse($workDetail->start_time)->format('g:i A') : '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ $workDetail && $workDetail->end_time ? \Carbon\Carbon::parse($workDetail->end_time)->format('g:i A') : '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        @if($workDetail && $workDetail->proof_image)
                            <a href="{{ asset('storage/' . $workDetail->proof_image) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-image"></i> View
                            </a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        @php
                            // Show supervisor note for both approved and rejected statuses (get the latest approval)
                            $supervisorNote = null;
                            if ($workDetail) {
                                $workStatus = strtolower($workDetail->status ?? '');
                                if (in_array($workStatus, ['approved', 'reject', 'rejected'])) {
                                    $supervisorNote = $workDetail->approvals?->sortByDesc('approved_at')->first()?->note ?? null;
                                }
                            }
                        @endphp
                        {{ $supervisorNote ?? '—' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $circleColor }}"></span>
                            <span class="text-xs font-medium text-gray-500">{{ $status }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <div class="flex items-center space-x-2">
                            @if ($assignment->class_status === 'pending_acceptance')
                                {{-- Accept/Reject buttons for pending acceptance --}}
                                <button type="button" 
                                    class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors"
                                    onclick="acceptAssignment({{ $assignment->id }})"
                                    title="Accept Assignment">
                                    <i class="fas fa-check mr-1"></i> Accept
                                </button>
                                <button type="button" 
                                    class="px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors"
                                    onclick="rejectAssignment({{ $assignment->id }})"
                                    title="Reject Assignment">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                            @elseif (!$isApproved)
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors"
                                    onclick="openWorkDetailForm({{ $assignment->id }}, {{ $workDetail ? $workDetail->id : 'null' }}, {{ $assignment->schedule_daily_data_id }})"
                                    title="{{ $hasSubmitted ? 'Edit Work Details' : 'Add Work Details' }}">
                                    <i class="fas {{ $hasSubmitted ? 'fa-edit' : 'fa-plus' }} text-xs"></i>
                                </button>
                                @if($hasSubmitted)
                                    <button type="button" 
                                        class="w-8 h-8 flex items-center justify-center bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors"
                                        onclick="confirmDeleteWorkDetail({{ $workDetail->id }})"
                                        title="Delete Work Details">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                @endif
                            @else
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors"
                                    onclick="viewWorkDetail({{ $workDetail->id }})"
                                    title="View Work Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noResultsRow">
                    <td colspan="10" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No assigned classes found</p>
                        <p class="text-sm">You will see your class schedule here once a supervisor assigns you as main tutor</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if (isset($workDetails) && method_exists($workDetails, 'links'))
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $workDetails->links() }}
    </div>
@else
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full"
        id="paginationSection">
        <div class="text-sm text-gray-500">
            Showing 0 results
        </div>
        <div class="flex items-center justify-center space-x-2 w-[300px]">
            <button
                class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center"
                disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
            <button
                class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center"
                disabled>
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
@endif

<script src="{{ asset('js/tutor-work.js') }}"></script>
