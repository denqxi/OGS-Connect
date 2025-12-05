@if (request('view_date'))
    {{-- Show the daily schedule view for finalized schedules --}}
    @include('schedules.tabs.views.finalized-schedule', ['date' => request('view_date')])
@else
<!-- Page Title -->
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <h2 class="text-xl font-semibold text-gray-800">Schedule History</h2>
</div>

<!-- Search Filters -->
<div class="bg-white px-6 pb-4 border-b border-gray-200">
    <div class="flex items-center justify-between">
        <!-- LEFT SIDE: LABEL + FILTERS IN ONE ROW -->
        <div class="flex items-center space-x-4 overflow-x-auto whitespace-nowrap">
            <h3 class="text-sm font-medium text-gray-700">Search Filters:</h3>

            <form method="GET" action="{{ route('schedules.index') }}" id="filterForm" class="flex items-center space-x-3">
                <input type="hidden" name="tab" value="history">

                <!-- Date -->
                <select name="date" id="filterDate"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Dates</option>
                    @if(isset($availableDates) && $availableDates->count() > 0)
                        @foreach($availableDates as $availableDate)
                            <option value="{{ $availableDate }}" {{ request('date') == $availableDate ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($availableDate)->format('M d, Y') }}
                            </option>
                        @endforeach
                    @else
                        <option value="" disabled>No dates available</option>
                    @endif
                </select>

                <!-- Day -->
                <select name="day" id="filterDay"
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="this.form.submit()">
                    <option value="">All Days</option>
                    @if(isset($availableDays) && $availableDays->count() > 0)
                        @foreach($availableDays as $availableDay)
                            <option value="{{ strtolower($availableDay) }}" {{ request('day') == strtolower($availableDay) ? 'selected' : '' }}>
                                {{ ucfirst($availableDay) }}
                            </option>
                        @endforeach
                    @endif
                </select>

                <!-- Clear -->
                @if(request()->hasAny(['date', 'day']))
                    <a href="{{ route('schedules.index', ['tab' => 'history']) }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- RIGHT SIDE: EXPORT BUTTON -->
        <div class="relative">
            <button type="button"
                id="exportButton"
                onclick="exportSelectedSchedules()"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-full text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105 opacity-50 cursor-not-allowed"
                disabled>
                <i class="fas fa-file-export"></i>
                <span id="exportButtonText">Export File (0 selected)</span>
            </button>
        </div>
    </div>
</div>


<!-- Schedule History Table -->
<div class="bg-white overflow-x-auto">
    <table class="w-full table-auto">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-12">
                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="toggleAllSchedules()">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @if(isset($scheduleHistory) && $scheduleHistory->count() > 0)
                @foreach($scheduleHistory as $history)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm">
                        <input type="checkbox" name="selected_schedules[]" value="{{ $history->date }}" class="schedule-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="updateExportButton()">
                    </td>
                    <!-- Date -->
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $history->date ? \Carbon\Carbon::parse($history->date)->format('F j, Y') : '-' }}
                    </td>
                    
                    <!-- Day -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $history->day ?? '-' }}
                    </td>
                    
                    <!-- School -->
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                        {{ $history->school ?? '-' }}
                    </td>
                    
                    <!-- Class -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $history->class ?? '-' }}
                    </td>
                    
                    <!-- Status -->
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-green-500"></span>
                            <span>Fully Assigned</span>
                        </div>
                    </td>
                    
                    <!-- Actions -->
                    <td class="px-6 py-4 text-sm">
                        <div class="flex items-center justify-center">
                            <button onclick="openScheduleDetailsModal('{{ \Carbon\Carbon::parse($history->date)->format('Y-m-d') }}', '{{ $history->school }}', {{ json_encode($history) }})" 
                                    class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                    title="View Details">
                                <i class="fas fa-eye text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg font-medium">No finalized schedules found</p>
                        <p class="text-sm">Schedules will appear here after being marked as fully assigned</p>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if(isset($scheduleHistory) && method_exists($scheduleHistory, 'hasPages') && $scheduleHistory->hasPages())
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
    <div class="text-sm text-gray-500">
        Showing {{ $scheduleHistory->firstItem() ?? 0 }} to {{ $scheduleHistory->lastItem() ?? 0 }} of {{ $scheduleHistory->total() }} entries
    </div>
    <div class="flex items-center space-x-2">
        @if ($scheduleHistory->onFirstPage())
            <button class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50" disabled>
                <i class="fas fa-chevron-left"></i>
            </button>
        @else
            <a href="{{ $scheduleHistory->appends(request()->query())->previousPageUrl() }}" 
               class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">
                <i class="fas fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($scheduleHistory->appends(request()->query())->getUrlRange(1, $scheduleHistory->lastPage()) as $page => $url)
            @if ($page == $scheduleHistory->currentPage())
                <button class="px-3 py-1 bg-slate-700 text-white rounded text-sm">{{ $page }}</button>
            @else
                <a href="{{ $url }}" class="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-gray-50">{{ $page }}</a>
            @endif
        @endforeach

        @if ($scheduleHistory->hasMorePages())
            <a href="{{ $scheduleHistory->appends(request()->query())->nextPageUrl() }}" 
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
        @if(isset($scheduleHistory) && $scheduleHistory->count() > 0)
            Showing 1 to {{ $scheduleHistory->count() }} of {{ $scheduleHistory->count() }} entries
        @else
            Showing 0 to 0 of 0 entries
        @endif
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

<!-- Schedule Details Modal (same as class-scheduling) -->
<div id="scheduleDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between sticky top-0 z-10">
            <h2 class="text-xl font-bold">Schedule Details</h2>
            <button onclick="closeScheduleDetailsModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="p-6 space-y-4">
            <!-- Date & Day -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                    <label class="block text-sm font-bold text-blue-900 uppercase mb-2">Date</label>
                    <input type="text" id="detail-date" readonly class="w-full bg-transparent border-0 font-semibold text-blue-900 focus:outline-none">
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                    <label class="block text-sm font-bold text-purple-900 uppercase mb-2">Day</label>
                    <input type="text" id="detail-day" readonly class="w-full bg-transparent border-0 font-semibold text-purple-900 focus:outline-none">
                </div>
            </div>
            
            <!-- School -->
            <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-4 rounded-lg border border-indigo-200">
                <label class="block text-sm font-bold text-indigo-900 uppercase mb-2">School</label>
                <input type="text" id="detail-school" readonly class="w-full bg-transparent border-0 font-semibold text-indigo-900 focus:outline-none">
            </div>
            
            <!-- Class -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                <label class="block text-sm font-bold text-green-900 uppercase mb-2">Class</label>
                <input type="text" id="detail-classes" readonly class="w-full bg-transparent border-0 font-semibold text-green-900 focus:outline-none">
            </div>
            
            <!-- Time -->
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-4 rounded-lg border border-yellow-200">
                <label class="block text-sm font-bold text-yellow-900 uppercase mb-2">Time</label>
                <input type="text" id="detail-times" readonly class="w-full bg-transparent border-0 font-semibold text-yellow-900 focus:outline-none">
            </div>
            
            <!-- Duration -->
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg border border-orange-200">
                <label class="block text-sm font-bold text-orange-900 uppercase mb-2">Duration</label>
                <input type="text" id="detail-duration" readonly class="w-full bg-transparent border-0 font-semibold text-orange-900 focus:outline-none">
            </div>
            
            <!-- Status -->
            <div class="bg-gradient-to-br from-red-50 to-red-100 p-4 rounded-lg border border-red-200">
                <label class="block text-sm font-bold text-red-900 uppercase mb-2">Status</label>
                <div id="detail-status"></div>
            </div>
            
            <!-- Main Tutor -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                <label class="block text-sm font-bold text-blue-900 uppercase mb-2">Main Tutor</label>
                <input type="text" id="detail-main-tutor" readonly class="w-full bg-transparent border-0 font-semibold text-blue-900 focus:outline-none">
            </div>
            
            <!-- Backup Tutor -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                <label class="block text-sm font-bold text-purple-900 uppercase mb-2">Backup Tutor</label>
                <input type="text" id="detail-backup-tutor" readonly class="w-full bg-transparent border-0 font-semibold text-purple-900 focus:outline-none">
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 flex justify-end border-t">
            <button onclick="closeScheduleDetailsModal()" class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                Close
            </button>
        </div>
    </div>
</div>

<!-- JavaScript for Schedule History -->
<script>
    // Pass CSRF token and export route to JS
    const csrfToken = "{{ csrf_token() }}";
    const exportSelectedSchedulesRoute = "{{ route('schedules.export-selected') }}";
    
    // Modal function for viewing schedule details
    function openScheduleDetailsModal(date, school, data) {
        console.log('Opening modal with data:', data);
        
        // Format date
        document.getElementById('detail-date').value = new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        
        // Day
        document.getElementById('detail-day').value = data.day || '-';
        
        // School
        document.getElementById('detail-school').value = data.school || school || '-';
        
        // Class
        document.getElementById('detail-classes').value = data.class || '-';
        
        // Time
        if (data.time) {
            try {
                const timeObj = new Date('2000-01-01 ' + data.time);
                const duration = data.duration || 25;
                const endTime = new Date(timeObj.getTime() + duration * 60000);
                document.getElementById('detail-times').value = timeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) + 
                    ' - ' + endTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            } catch (e) {
                document.getElementById('detail-times').value = data.time;
            }
        } else {
            document.getElementById('detail-times').value = '-';
        }
        
        // Duration
        document.getElementById('detail-duration').value = (data.duration || 25) + ' minutes';
        
        // Status
        const statusDiv = document.getElementById('detail-status');
        statusDiv.innerHTML = '<span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Fully Assigned</span>';
        
        // Main Tutor
        document.getElementById('detail-main-tutor').value = data.main_tutor_name || 'Not Assigned';
        
        // Backup Tutor
        document.getElementById('detail-backup-tutor').value = data.backup_tutor_name || 'Not Assigned';
        
        document.getElementById('scheduleDetailsModal').classList.remove('hidden');
    }
    
    function closeScheduleDetailsModal() {
        document.getElementById('scheduleDetailsModal').classList.add('hidden');
    }
</script>
<script src="{{ asset('js/schedule-history.js') }}"></script>
@endif


