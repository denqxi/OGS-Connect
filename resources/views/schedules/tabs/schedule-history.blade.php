@if (request('view_date'))
    {{-- Show the daily schedule view for finalized schedules --}}
    @include('schedules.tabs.views.finalized-schedule', ['date' => request('view_date')])
@else

<!-- Search Filters -->
<div class="bg-white px-6 pt-6 pb-4 border-b border-gray-200">
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

        <!-- RIGHT SIDE: EXPORT BUTTONS -->
        <div class="flex items-center space-x-3">
            <button type="button"
                id="exportButton"
                onclick="exportSelectedSchedules()"
                class="flex items-center space-x-2 bg-[#0E335D] text-white px-4 py-2 rounded-md text-sm font-medium 
                        hover:bg-[#184679] transform transition duration-200 hover:scale-105 opacity-50 cursor-not-allowed"
                disabled
                title="Export only the schedules you have checked">
                <i class="fas fa-file-export"></i>
                <span id="exportButtonText">Export Selected (0)</span>
            </button>
            
            <button type="button" onclick="showExportAllModal()"
                    class="flex items-center space-x-2 bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium 
                            hover:bg-green-700 transform transition duration-200 hover:scale-105"
                    title="Export ALL fully assigned schedules in the entire history">
                <i class="fas fa-download"></i>
                <span>Export All</span>
            </button>
        </div>
    </div>
</div>


<!-- Schedule History Table -->
<div class="overflow-x-auto">
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
                        <input type="checkbox" name="selected_schedules[]" value="{{ $history->id }}" class="schedule-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="updateExportButton()">
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
                            <button type="button" onclick="openScheduleDetailsModal('{{ \Carbon\Carbon::parse($history->date)->format('Y-m-d') }}', '{{ $history->school }}', {{ json_encode($history) }})" 
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
@endif

<!-- Schedule Details Modal -->
<div id="scheduleDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="bg-[#0E335D] text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <h2 class="text-xl font-bold">Schedule Information</h2>
            <button type="button" onclick="closeScheduleDetailsModal()" class="text-white hover:text-gray-200 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Body -->
        <div class="overflow-y-auto flex-grow">
            <div class="p-6">
                <!-- Schedule Overview Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-5 mb-6 border border-blue-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                        Schedule Overview
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Date -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Date</label>
                            <p class="text-sm font-semibold text-gray-800" id="detail-date">-</p>
                        </div>
                        
                        <!-- Day -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Day</label>
                            <p class="text-sm font-semibold text-gray-800" id="detail-day">-</p>
                        </div>
                        
                        <!-- Time -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Time</label>
                            <p class="text-sm font-semibold text-gray-800" id="detail-times">-</p>
                        </div>
                        
                        <!-- Duration -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Duration</label>
                            <p class="text-sm font-semibold text-gray-800" id="detail-duration">-</p>
                        </div>
                    </div>
                </div>

                <!-- School & Class Information -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-5 mb-6 border border-green-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-school text-green-600 mr-2"></i>
                        School & Class Details
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <!-- School -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">School</label>
                            <p class="text-sm font-semibold text-gray-800" id="detail-school">-</p>
                        </div>
                        
                        <!-- Class -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Class</label>
                            <p class="text-sm font-semibold text-gray-800" id="detail-classes">-</p>
                        </div>
                    </div>
                </div>

                <!-- Assignment Information -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-5 mb-6 border border-purple-200 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-users text-purple-600 mr-2"></i>
                        Assignment Details
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-2">Status</label>
                            <div id="detail-status"></div>
                        </div>
                        
                        <!-- Main Tutor -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Main Tutor</label>
                            <div class="bg-white rounded-md px-3 py-2 border border-gray-200">
                                <p class="text-sm font-semibold text-gray-800" id="detail-main-tutor">-</p>
                            </div>
                        </div>
                        
                        <!-- Backup Tutor -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Backup Tutor</label>
                            <div class="bg-white rounded-md px-3 py-2 border border-gray-200">
                                <p class="text-sm font-semibold text-gray-800" id="detail-backup-tutor">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 flex justify-end border-t flex-shrink-0">
            <button type="button" onclick="closeScheduleDetailsModal()" class="px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition-colors shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Export All Confirmation Modal -->
<div id="exportAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                    <i class="fas fa-download text-green-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Export ALL Schedules</h3>
                    <p class="text-sm text-gray-600">
                        Are you sure you want to export <strong>ALL fully assigned schedules</strong> to PDF?
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        This will include all schedules in the history, not just the ones on this page.
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
            <button type="button" onclick="closeExportAllModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-400 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="confirmExportAll()" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md font-medium hover:bg-green-700 transition-colors">
                Export All
            </button>
        </div>
    </div>
</div>

<!-- Export Selected Confirmation Modal -->
<div id="exportSelectedModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-start mb-4">
                <div class="flex-shrink-0 bg-blue-100 rounded-full p-3 mr-4">
                    <i class="fas fa-file-export text-blue-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Export Selected Schedules</h3>
                    <p class="text-sm text-gray-600">
                        You are about to export <strong id="selectedCount">0</strong> schedule(s) to PDF.
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Only the checked schedules will be exported.
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
            <button type="button" onclick="closeExportSelectedModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-400 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="confirmExportSelected()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition-colors">
                Export Selected
            </button>
        </div>
    </div>
</div>

<!-- Hidden Export Forms -->
<form id="exportAllForm" method="POST" action="{{ route('schedules.export-all') }}" class="hidden">
    @csrf
</form>

<!-- JavaScript for Schedule History -->
<script>
    // Pass CSRF token and export route to JS
    const csrfToken = "{{ csrf_token() }}";
    const exportSelectedSchedulesRoute = "{{ route('schedules.export-selected') }}";
    
    // Modal function for viewing schedule details
    function openScheduleDetailsModal(date, school, data) {
        console.log('Opening modal with data:', data);
        console.log('Day value:', data.day);
        
        // Format date
        const dateObj = new Date(date);
        document.getElementById('detail-date').textContent = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        
        // Day - if not provided in data, get from date
        let dayName = data.day;
        if (!dayName || dayName.trim() === '') {
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            dayName = days[dateObj.getDay()];
        }
        document.getElementById('detail-day').textContent = dayName || '-';
        
        // School
        document.getElementById('detail-school').textContent = data.school || school || '-';
        
        // Class
        document.getElementById('detail-classes').textContent = data.class || '-';
        
        // Time
        if (data.time) {
            try {
                const timeObj = new Date('2000-01-01 ' + data.time);
                const duration = data.duration || 25;
                const endTime = new Date(timeObj.getTime() + duration * 60000);
                document.getElementById('detail-times').textContent = timeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }) + 
                    ' - ' + endTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            } catch (e) {
                document.getElementById('detail-times').textContent = data.time;
            }
        } else {
            document.getElementById('detail-times').textContent = '-';
        }
        
        // Duration
        document.getElementById('detail-duration').textContent = (data.duration || 25) + ' minutes';
        
        // Status with better styling
        const statusDiv = document.getElementById('detail-status');
        statusDiv.innerHTML = `
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border bg-green-100 text-green-800 border-green-200">
                <i class="fas fa-check-circle"></i>
                <span class="font-semibold">Fully Assigned</span>
            </div>
        `;
        
        // Main Tutor
        document.getElementById('detail-main-tutor').textContent = data.main_tutor_name || 'Not Assigned';
        
        // Backup Tutor
        document.getElementById('detail-backup-tutor').textContent = data.backup_tutor_name || 'Not Assigned';
        
        document.getElementById('scheduleDetailsModal').classList.remove('hidden');
    }
    
    function closeScheduleDetailsModal() {
        document.getElementById('scheduleDetailsModal').classList.add('hidden');
    }
    
    function showExportAllModal() {
        document.getElementById('exportAllModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    function closeExportAllModal() {
        document.getElementById('exportAllModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    function confirmExportAll() {
        document.getElementById('exportAllForm').submit();
    }
</script>
<script src="{{ asset('js/schedule-history.js') }}"></script>
@endif


