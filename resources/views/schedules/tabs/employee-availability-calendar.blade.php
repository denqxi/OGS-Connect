<!-- Calendar View for Scheduled Classes -->
<div class="min-h-screen bg-gray-50 p-6">
    <!-- Page Title -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-900">Class Schedule Calendar</h2>
        <p class="text-gray-600 mt-2">View and manage scheduled classes</p>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Calendar Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-6">
                    <button id="prevMonth" class="flex items-center justify-center w-10 h-10 bg-white/20 hover:bg-white/30 rounded-lg transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <div class="min-w-64">
                        <h3 class="text-2xl font-bold text-center" id="currentMonth">December 2025</h3>
                    </div>
                    
                    <button id="nextMonth" class="flex items-center justify-center w-10 h-10 bg-white/20 hover:bg-white/30 rounded-lg transition">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
                
                <button id="todayBtn" class="px-6 py-2 bg-white text-blue-600 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Today
                </button>
            </div>

            <!-- Legend -->
            <div class="flex items-center space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                    <span>Finalized</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                    <span>Draft/Tentative</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                    <span>Cancelled</span>
                </div>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="p-6">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-2 mb-4">
                <div class="text-center font-bold text-gray-700 py-3 text-sm">SUN</div>
                <div class="text-center font-bold text-gray-700 py-3 text-sm">MON</div>
                <div class="text-center font-bold text-gray-700 py-3 text-sm">TUE</div>
                <div class="text-center font-bold text-gray-700 py-3 text-sm">WED</div>
                <div class="text-center font-bold text-gray-700 py-3 text-sm">THU</div>
                <div class="text-center font-bold text-gray-700 py-3 text-sm">FRI</div>
                <div class="text-center font-bold text-gray-700 py-3 text-sm">SAT</div>
            </div>

            <!-- Calendar Days Grid -->
            <div class="grid grid-cols-7 gap-2" id="calendarGrid">
                <!-- Calendar days will be generated here -->
            </div>
        </div>
    </div>

    <!-- Class Details Modal -->
    <div id="classModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white flex items-center justify-between">
                <h3 class="text-2xl font-bold" id="modalDate">December 4, 2025</h3>
                <button onclick="closeClassModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div id="dayClasses" class="space-y-4">
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
        cell.className = 'bg-gray-50 border border-gray-200 rounded-lg p-3 min-h-32 text-gray-400';
        cell.innerHTML = `<div class="text-lg font-bold">${day}</div>`;
    } else {
        cell.className = 'border-2 border-gray-200 rounded-lg p-3 min-h-32 hover:shadow-lg transition cursor-pointer bg-white';
        
        if (isToday) {
            cell.classList.remove('border-gray-200');
            cell.classList.add('border-blue-500', 'bg-blue-50');
        }
        
        if (classes.length > 0) {
            cell.classList.remove('bg-white');
            cell.classList.add('bg-blue-50');
        }
        
        let html = `<div class="text-lg font-bold text-gray-900 mb-2">${day}</div>`;
        
        // Show class indicators
        classes.forEach((classItem, index) => {
            if (index < 3) { // Show max 3 classes
                const statusColor = getStatusColor(classItem.class_status || classItem.schedule_status);
                const school = classItem.school || 'Unknown School';
                const tutor = classItem.tutor_name || 'TBD';
                
                html += `<div class="text-xs font-medium mb-1 p-1 rounded ${statusColor}">
                    <div class="truncate">${school}</div>
                    <div class="text-xs opacity-90 truncate">${tutor}</div>
                </div>`;
            }
        });
        
        if (classes.length > 3) {
            html += `<div class="text-xs text-gray-600 px-1 font-semibold">+${classes.length - 3} more</div>`;
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

function getStatusColor(status) {
    switch(status) {
        case 'finalized':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'draft':
        case 'tentative':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-blue-100 text-blue-800';
    }
}

function getClassesForDate(date) {
    const dateStr = date.toISOString().split('T')[0]; // YYYY-MM-DD format
    
    return allClasses.filter(classItem => {
        // Handle ISO format (2025-06-13T00:00:00.000000Z) or regular format
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
    
    const dayClassesDiv = document.getElementById('dayClasses');
    dayClassesDiv.innerHTML = '';
    
    if (classes.length === 0) {
        dayClassesDiv.innerHTML = '<p class="text-gray-500 text-center py-12">No classes scheduled for this day</p>';
    } else {
        classes.forEach(classItem => {
            const statusColor = getStatusBadgeColor(classItem.class_status || classItem.schedule_status);
            const school = classItem.school || 'Unknown School';
            const tutor = classItem.tutor_name || 'TBD';
            const supervisor = classItem.assigned_supervisor ? 'Assigned' : 'Unassigned';
            const time = classItem.time_jst || 'Time TBD';
            
            const classHTML = `
                <div class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="font-bold text-lg text-gray-900">${school}</h4>
                            <p class="text-sm text-gray-600 mt-1">Class: ${classItem.class || 'N/A'}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">
                            ${(classItem.class_status || classItem.schedule_status || 'pending').toUpperCase()}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Tutor</p>
                            <p class="text-sm font-medium text-gray-900">${tutor}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Time</p>
                            <p class="text-sm font-medium text-gray-900">${time}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Supervisor</p>
                            <p class="text-sm font-medium text-gray-900">${supervisor}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600 font-semibold mb-1">Students</p>
                            <p class="text-sm font-medium text-gray-900">${classItem.number_required || '0'} required</p>
                        </div>
                    </div>
                </div>
            `;
            dayClassesDiv.innerHTML += classHTML;
        });
    }
    
    document.getElementById('classModal').classList.remove('hidden');
}

function getStatusBadgeColor(status) {
    switch(status) {
        case 'finalized':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'tentative':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function closeClassModal() {
    document.getElementById('classModal').classList.add('hidden');
}
</script>

<style>
    #calendarGrid > div:nth-child(n+29):nth-child(-n+35) {
        min-height: 8rem;
    }
</style>
