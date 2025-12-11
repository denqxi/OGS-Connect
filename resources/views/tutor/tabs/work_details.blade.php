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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time - End Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                    
                    // Check if tutor has submitted work details for this assignment
                    $hasSubmitted = $workDetail !== null;
                    // Only approved if work detail status is 'approved' (from supervisor in payroll tab)
                    $isApproved = $hasSubmitted && strtolower($workDetail->status ?? '') === 'approved';
                    
                    // Tutor name - get from authenticated user
                    $tutorName = Auth::guard('tutor')->user()->full_name ?? Auth::guard('tutor')->user()->username ?? 'N/A';
                    
                    // Start and End Time
                    $startTime = $workDetail && $workDetail->start_time ? \Carbon\Carbon::parse($workDetail->start_time)->format('g:i A') : ($schedule ? \Carbon\Carbon::parse($schedule->time)->format('g:i A') : '—');
                    $endTime = '—';
                    if ($workDetail && $workDetail->end_time) {
                        $endTime = \Carbon\Carbon::parse($workDetail->end_time)->format('g:i A');
                    } elseif ($schedule && $schedule->time && $schedule->duration) {
                        $endTime = \Carbon\Carbon::parse($schedule->time)->addMinutes($schedule->duration)->format('g:i A');
                    }
                    
                    // Per Class and Rate
                    $perClass = $schedule->class ?? 'N/A';
                    $rate = $schedule->rate ?? 'N/A';
                @endphp
                <tr class="hover:bg-gray-50">
                    <!-- Date -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $schedule ? \Carbon\Carbon::parse($schedule->date)->format('F j, Y') : 'N/A' }}
                    </td>
                    
                    <!-- Name -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ $tutorName }}
                    </td>
                    
                    <!-- Start Time - End Time -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $startTime }} - {{ $endTime }}
                    </td>
                    
                    <!-- Rate -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                        {{ is_numeric($rate) ? '₱' . number_format($rate, 2) : $rate }}
                    </td>
                    
                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center justify-center space-x-2">
                            @if ($assignment->class_status === 'pending_acceptance')
                                {{-- View Details with Accept/Reject inside modal --}}
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors"
                                    onclick="openTutorWorkDetailModal({{ json_encode($assignment) }}, {{ json_encode($schedule) }}, {{ json_encode($workDetail) }})"
                                    title="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            @elseif (!$isApproved)
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors"
                                    onclick="openTutorWorkDetailModal({{ json_encode($assignment) }}, {{ json_encode($schedule) }}, {{ json_encode($workDetail) }})"
                                    title="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            @else
                                <button type="button" 
                                    class="w-8 h-8 flex items-center justify-center bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors"
                                    onclick="openTutorWorkDetailModal({{ json_encode($assignment) }}, {{ json_encode($schedule) }}, {{ json_encode($workDetail) }})"
                                    title="View Details">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noResultsRow">
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No assigned classes found</p>
                        <p class="text-sm">You will see your class schedule here once a supervisor assigns you as main tutor</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Tutor Work Detail Modal -->
<div id="tutorWorkDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h2 class="text-xl font-bold">Work Details</h2>
            <button type="button" onclick="closeTutorWorkDetailModal()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="overflow-y-auto flex-grow">
            <div class="p-6">
                <!-- Schedule Information Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 mb-6 border border-blue-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                        Schedule Information
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-date">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Day</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-day">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">School</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-school">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-class">-</p>
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
                            <p class="text-sm font-semibold text-gray-800" id="modal-start-time">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">End Time</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-end-time">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-duration">-</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Rate</label>
                            <p class="text-sm font-semibold text-gray-800" id="modal-rate">-</p>
                        </div>
                    </div>
                </div>

                <!-- Proof of Work Card -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 border border-purple-200 shadow-sm" id="proof-section">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-image text-purple-600 mr-2"></i>
                        Proof of Work
                    </h3>
                    
                    <div class="flex justify-center" id="modal-proof-container">
                        <p class="text-gray-500 text-center py-8">No proof image uploaded yet</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Actions -->
        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center border-t flex-shrink-0">
            <div class="flex gap-3" id="modal-action-buttons">
                <!-- Dynamic buttons will be inserted here -->
            </div>
            
            <button type="button" onclick="closeTutorWorkDetailModal()" class="px-6 py-2 bg-gray-500 text-white rounded-md font-medium hover:bg-gray-600 transition-colors">
                Close
            </button>
        </div>
    </div>
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

<!-- View Work Detail Modal -->
<div id="viewWorkDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="sticky top-0 bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Work Detail Summary</h2>
            <button onclick="document.getElementById('viewWorkDetailModal').style.display = 'none'" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Work Summary Section -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Work Summary</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Date</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_schedule_date">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Day</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_schedule_day">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Scheduled Time</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_schedule_time">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">School</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_school">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Scheduled Duration</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_duration_scheduled">—</p>
                    </div>
                </div>
            </div>

            <!-- Actual Times Section -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Actual Times Submitted</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Start Time</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_actual_start">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">End Time</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_actual_end">—</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-medium">Total Duration</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_duration_actual">—</p>
                    </div>
                </div>
            </div>

            <!-- Proof Section -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">Proof of Work</h3>
                <img id="vwd_proof_image" src="" alt="Proof of work" class="w-full rounded border border-gray-200 mb-2" style="max-height: 300px; object-fit: contain; display: none;">
                <a id="vwd_proof_link" href="" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm" style="display: none;">
                    <i class="fas fa-external-link-alt mr-1"></i> Open in new window
                </a>
                <p id="vwd_no_proof" class="text-sm text-gray-500">No proof image provided</p>
            </div>

            <!-- Supervisor Approval Section -->
            <div id="vwd_approval_section" style="display: none;">
                <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-green-200 text-green-700">Approval Information</h3>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 space-y-3">
                    <div>
                        <p class="text-xs text-green-700 uppercase tracking-wider font-medium">Approved By</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_approved_by">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-green-700 uppercase tracking-wider font-medium">Approved Date & Time</p>
                        <p class="text-sm text-gray-900 mt-1" id="vwd_approved_date">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-green-700 uppercase tracking-wider font-medium">Supervisor Note</p>
                        <p class="text-sm text-gray-900 mt-1 italic" id="vwd_approval_note">—</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex justify-end">
            <button onclick="document.getElementById('viewWorkDetailModal').style.display = 'none'" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/tutor-work.js') }}"></script>

