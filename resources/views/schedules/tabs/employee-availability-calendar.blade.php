<!-- Calendar View for Scheduled Classes -->
<div class="bg-gray-50 p-4">
    <!-- Page Title -->
    <div class="mb-4">
        <h2 class="text-2xl font-bold text-gray-900">Class Schedule Calendar</h2>
        <p class="text-gray-600 text-sm mt-1">View and manage scheduled classes</p>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg shadow overflow-hidden max-w-7xl mx-auto">
        <!-- Calendar Header -->
        <div class="bg-gradient-to-r from-[#0E335D] to-[#184679] p-4 text-white">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <button id="prevMonth" class="flex items-center justify-center w-9 h-9 bg-white/20 hover:bg-white/30 rounded-lg transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <div class="min-w-48">
                        <h3 class="text-xl font-bold text-center" id="currentMonth">December 2025</h3>
                    </div>
                    
                    <button id="nextMonth" class="flex items-center justify-center w-9 h-9 bg-white/20 hover:bg-white/30 rounded-lg transition">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <button id="todayBtn" class="px-4 py-2 bg-white text-[#0E335D] rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                    Today
                </button>
            </div>

            <!-- Legend -->
            <div class="flex items-center space-x-4 text-xs">
                <div class="flex items-center space-x-1.5">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span>Fully Assigned</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                    <span>Pending Acceptance</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                    <span>Not Assigned</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="p-4">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-1 mb-2">
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">SUN</div>
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">MON</div>
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">TUE</div>
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">WED</div>
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">THU</div>
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">FRI</div>
                <div class="text-center font-semibold text-gray-700 py-2 text-xs">SAT</div>
            </div>

            <!-- Calendar Days Grid -->
            <div class="grid grid-cols-7 gap-1" id="calendarGrid">
                <!-- Calendar days will be generated here -->
            </div>
        </div>
    </div>

    <!-- Class Details Modal -->
    <div id="classModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[85vh] overflow-hidden flex flex-col">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-[#0E335D] to-[#184679] p-5 text-white flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-xl font-bold" id="modalDate">December 4, 2025</h3>
                    <p class="text-sm text-white/80 mt-1" id="modalClassCount">0 classes scheduled</p>
                </div>
                <button onclick="closeClassModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-5 overflow-y-auto flex-grow">
                <div id="dayClasses" class="space-y-3">
                    <!-- Classes will be populated here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentDate = new Date();
let allClasses = [];

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    // Get classes data from the controller
    allClasses = @json($dailyData ?? []);
    
    renderCalendar();
    
    document.getElementById('prevMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });
    
    document.getElementById('nextMonth').addEventListener('click', function() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
    
    document.getElementById('todayBtn').addEventListener('click', function() {
        currentDate = new Date();
        renderCalendar();
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeClassModal();
        }
    });
    
    // Close modal on background click
    document.getElementById('classModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeClassModal();
        }
    });
});

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Update month display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();
    
    // Build calendar grid
    const calendarGrid = document.getElementById('calendarGrid');
    calendarGrid.innerHTML = '';
    
    const today = new Date();
    
    // Previous month days
    for (let i = firstDay - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const cell = createDayCell(day, month - 1, year, true, []);
        calendarGrid.appendChild(cell);
    }
    
    // Current month days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const isToday = date.toDateString() === today.toDateString();
        const classes = getClassesForDate(date);
        
        const cell = createDayCell(day, month, year, false, classes, isToday);
        calendarGrid.appendChild(cell);
    }
    
    // Next month days
    const totalCells = calendarGrid.children.length;
    const remainingCells = 42 - totalCells;
    for (let day = 1; day <= remainingCells; day++) {
        const cell = createDayCell(day, month + 1, year, true, []);
        calendarGrid.appendChild(cell);
    }
}

function createDayCell(day, month, year, isOtherMonth, classes, isToday = false) {
    const cell = document.createElement('div');
    
    if (isOtherMonth) {
        cell.className = 'bg-gray-50 border border-gray-200 rounded p-2 min-h-24 text-gray-400';
        cell.innerHTML = `<div class="text-sm font-semibold">${day}</div>`;
    } else {
        cell.className = 'border border-gray-200 rounded p-2 min-h-24 hover:shadow-md transition cursor-pointer bg-white';
        
        if (isToday) {
            cell.classList.remove('border-gray-200');
            cell.classList.add('border-[#0E335D]', 'border-2', 'bg-blue-50');
        }
        
        if (classes.length > 0) {
            cell.classList.add('hover:bg-gray-50');
        }
        
        let html = `<div class="text-sm font-bold text-gray-900 mb-1.5">${day}</div>`;
        
        // Show class indicators
        classes.forEach((classItem, index) => {
            if (index < 2) { // Show max 2 classes for compact view
                const statusColor = getStatusColor(classItem.class_status);
                const time = classItem.time ? formatTime(classItem.time) : 'N/A';
                
                html += `<div class="text-xs mb-1 p-1 rounded ${statusColor}">
                    <div class="font-medium truncate">${time}</div>
                    <div class="text-xs opacity-90 truncate">${classItem.school || 'N/A'}</div>
                </div>`;
            }
        });
        
        if (classes.length > 2) {
            html += `<div class="text-xs text-gray-600 font-semibold mt-1">+${classes.length - 2} more</div>`;
        }
        
        cell.innerHTML = html;
        cell.onclick = function() {
            if (classes.length > 0) {
                const date = new Date(year, month, day);
                openClassModal(date, classes);
            }
        };
    }
    
    return cell;
}

function formatTime(timeString) {
    if (!timeString) return 'N/A';
    try {
        const [hours, minutes] = timeString.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    } catch (e) {
        return timeString;
    }
}

function getStatusColor(status) {
    if (!status) {
        return 'bg-red-100 text-red-800';
    }
    
    switch(status.toLowerCase()) {
        case 'fully_assigned':
            return 'bg-green-100 text-green-800';
        case 'pending_acceptance':
            return 'bg-blue-100 text-blue-800';
        case 'partially_assigned':
            return 'bg-yellow-100 text-yellow-800';
        case 'not_assigned':
            return 'bg-red-100 text-red-800';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-red-100 text-red-800';
    }
}

function getClassesForDate(date) {
    // Format the date as YYYY-MM-DD in local timezone
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;
    
    return allClasses.filter(classItem => {
        if (!classItem.date) return false;
        
        // Handle different date formats
        let classDate = classItem.date;
        if (classDate.includes('T')) {
            classDate = classDate.split('T')[0];
        } else if (classDate.includes(' ')) {
            classDate = classDate.split(' ')[0];
        }
        
        return classDate === dateStr;
    });
}

function openClassModal(date, classes) {
    const dateStr = date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    document.getElementById('modalDate').textContent = dateStr;
    document.getElementById('modalClassCount').textContent = `${classes.length} ${classes.length === 1 ? 'class' : 'classes'} scheduled`;
    
    const dayClassesDiv = document.getElementById('dayClasses');
    dayClassesDiv.innerHTML = '';
    
    if (classes.length === 0) {
        dayClassesDiv.innerHTML = '<p class="text-gray-500 text-center py-12">No classes scheduled for this day</p>';
    } else {
        classes.forEach(classItem => {
            const statusColor = getStatusBadgeColor(classItem.class_status);
            const statusText = getStatusText(classItem.class_status);
            const school = classItem.school || 'Unknown School';
            const className = classItem.class || 'N/A';
            const tutor = classItem.tutor_name || 'Not Assigned';
            const time = classItem.time ? formatTime(classItem.time) : 'N/A';
            const duration = classItem.duration || '25';
            const day = classItem.day || 'N/A';
            
            const classHTML = `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition bg-white">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h4 class="font-bold text-lg text-gray-900">${school}</h4>
                            <p class="text-sm text-gray-600 mt-0.5">Class: ${className}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${statusColor} whitespace-nowrap ml-3">
                            ${statusText}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-200">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-0.5">Tutor</p>
                            <p class="text-sm font-medium text-gray-900">${tutor}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-0.5">Day</p>
                            <p class="text-sm font-medium text-gray-900">${day}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-0.5">Time</p>
                            <p class="text-sm font-medium text-gray-900">${time}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-0.5">Duration</p>
                            <p class="text-sm font-medium text-gray-900">${duration} minutes</p>
                        </div>
                    </div>
                </div>
            `;
            dayClassesDiv.innerHTML += classHTML;
        });
    }
    
    document.getElementById('classModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function getStatusBadgeColor(status) {
    if (!status) {
        return 'bg-red-100 text-red-800';
    }
    
    switch(status.toLowerCase()) {
        case 'fully_assigned':
            return 'bg-green-100 text-green-800';
        case 'pending_acceptance':
            return 'bg-blue-100 text-blue-800';
        case 'partially_assigned':
            return 'bg-yellow-100 text-yellow-800';
        case 'not_assigned':
            return 'bg-red-100 text-red-800';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-red-100 text-red-800';
    }
}

function getStatusText(status) {
    if (!status) {
        return 'Not Assigned';
    }
    
    switch(status.toLowerCase()) {
        case 'fully_assigned':
            return 'Fully Assigned';
        case 'pending_acceptance':
            return 'Pending Acceptance';
        case 'partially_assigned':
            return 'Partially Assigned';
        case 'not_assigned':
            return 'Not Assigned';
        case 'cancelled':
            return 'Cancelled';
        default:
            return 'Not Assigned';
    }
}

function closeClassModal() {
    document.getElementById('classModal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>

<style>
    /* Compact calendar styling */
    #calendarGrid > div {
        min-height: 6rem;
    }
    
    /* Ensure scrollbar doesn't cause layout shift */
    #classModal .overflow-y-auto {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
    }
    
    #classModal .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }
    
    #classModal .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #classModal .overflow-y-auto::-webkit-scrollbar-thumb {
        background-color: rgba(156, 163, 175, 0.5);
        border-radius: 3px;
    }
</style>
