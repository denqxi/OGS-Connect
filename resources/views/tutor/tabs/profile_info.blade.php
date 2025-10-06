<div class="bg-white dark:bg-gray-900 rounded-xl p-4 space-y-6">

    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between p-4 rounded-lg shadow-lg
           bg-gradient-to-r from-blue-100 via-green-100 to-green-200
           dark:from-gray-800 dark:via-gray-800 dark:to-gray-700">
        <div class="flex items-center space-x-4">
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=96&h=96&fit=crop&crop=face"
                alt="Profile" class="w-20 h-20 rounded-full object-cover border-2 border-[#0E335D]">
            <div>
                <h2 class="text-lg font-semibold text-[#0E335D] dark:text-[#CFE2F3]">{{ $tutor->full_name ?? 'N/A' }}</h2>
                <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $tutor->email ?? 'N/A' }}</p>
                <p class="text-gray-600 dark:text-gray-400 text-xs">{{ $tutor->tutorID ?? 'N/A' }}</p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 text-left md:text-right w-full md:w-40">
            <label class="block text-sm text-gray-700 dark:text-gray-300 mb-1">Your Account:</label>
            <select
                class="w-full border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 hover:border-[#0E335D] focus:outline-none focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D] transition-all duration-150">
                <option>GLS</option>
                <option>OGS</option>
            </select>
        </div>
    </div>


    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Personal Information -->
    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Personal Information</h3>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Row 1 -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">First Name <span
                        class="text-red-500">*</span></label>
                <input type="text" value="{{ $tutor->first_name ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Last Name <span
                        class="text-red-500">*</span></label>
                <input type="text" value="{{ $tutor->last_name ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Date of Birth <span
                        class="text-red-500">*</span></label>
                <input type="date" value="{{ $tutor->date_of_birth ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>

            <!-- Row 2 -->
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Address <span
                        class="text-red-500">*</span></label>
                <input type="text" value="{{ $tutor->tutorDetails->address ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200 
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Email <span
                        class="text-red-500">*</span></label>
                <input type="email" value="{{ $tutor->email ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">Contact Number <span
                        class="text-red-500">*</span></label>
                <input type="text" value="{{ $tutor->phone_number ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>

            <!-- Row 3 (aligned under Address column only) -->
            <div class="md:col-start-1 md:col-span-1">
                <label class="block text-sm text-gray-600 dark:text-gray-300 mb-1">MS Teams ID <span
                        class="text-red-500">*</span></label>
                <input type="text" value="{{ $tutor->tutorDetails->ms_teams_id ?? '' }}" required
                    class="border border-gray-300 dark:border-gray-600 rounded-md px-3 py-2 w-full bg-gray-50 dark:bg-gray-700 dark:text-gray-200
                  focus:outline-none focus:border-[0.5px] focus:border-[#0E335D] focus:ring-1 focus:ring-[#0E335D]">
            </div>
        </div>
    </div>

    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Payment Information -->
    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-2">Payment Information</h3>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700"
        id="paymentInfoContainer">
        
        <!-- Payment information will be dynamically loaded here -->
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Loading payment information...</p>
        </div>
    </div>

    <hr class="border-gray-300 dark:border-gray-700">

    <!-- Work Availability -->
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-700 dark:text-gray-200">Work Availability</h3>
        <div class="flex gap-2">
            <button id="saveAvailabilityBtn" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                <i class="fas fa-save mr-1"></i>Save Changes
            </button>
            <button id="resetAvailabilityBtn" 
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                <i class="fas fa-undo mr-1"></i>Reset
            </button>
        </div>
    </div>
    
    <div id="availabilityMessage" class="hidden mb-4 p-3 rounded-lg text-sm"></div>
    
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 space-y-4 border border-gray-200 dark:border-gray-700">
        <!-- Account Boxes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="availabilityContainer">
            <!-- Account boxes will be dynamically loaded here -->
            <div class="col-span-full text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Loading availability data...</p>
                </div>
                </div>
                </div>
            </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize availability management
    const availabilityManager = new AvailabilityManager();
    availabilityManager.init();
});

class AvailabilityManager {
    constructor() {
        this.accounts = {};
        this.originalData = {};
        this.hasChanges = false;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                        document.querySelector('input[name="_token"]')?.value;
        
        // Account configurations with time restrictions
        this.accountConfigs = {
            'GLS': {
                icon: 'fas fa-graduation-cap',
                bgColor: 'bg-[#E8F0FE] dark:bg-[#1E3A5F]',
                textColor: 'text-[#0E335D] dark:text-[#E8F0FE]',
                accentColor: '#0E335D',
                timeRestrictions: {
                    startTime: '07:00',
                    endTime: '15:30',
                    enabled: true
                }
            },
            'Babilala': {
                icon: 'fas fa-book-open',
                bgColor: 'bg-[#F3E8FF] dark:bg-[#6B4EC9]',
                textColor: 'text-[#A78BFA] dark:text-[#F3E8FF]',
                accentColor: '#A78BFA',
                timeRestrictions: {
                    startTime: '20:00',
                    endTime: '22:00',
                    enabled: true
                }
            },
            'Tutlo': {
                icon: 'fas fa-comments',
                bgColor: 'bg-[#FFF9E6] dark:bg-[#7A6400]',
                textColor: 'text-[#E6B800] dark:text-[#FFF9E6]',
                accentColor: '#E6B800',
                timeRestrictions: {
                    startTime: '00:00',
                    endTime: '23:30',
                    enabled: false
                }
            },
            'Talk915': {
                icon: 'fas fa-language',
                bgColor: 'bg-[#E6F4FF] dark:bg-[#005C8C]',
                textColor: 'text-[#128AD4] dark:text-[#A3D8FF]',
                accentColor: '#128AD4',
                timeRestrictions: {
                    startTime: '00:00',
                    endTime: '23:30',
                    enabled: false
                }
            }
        };
        
        this.days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        this.timeSlots = this.generateTimeSlots();
    }

    async init() {
        try {
            await this.loadAvailabilityData();
            this.setupEventListeners();
        } catch (error) {
            console.error('Error initializing availability manager:', error);
            this.showMessage('Error loading availability data', 'error');
        }
    }

    async loadAvailabilityData() {
        try {
            const response = await fetch('/tutor/availability/', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load availability data');
            }

            const result = await response.json();
            
            if (result.success) {
                this.accounts = result.data;
                this.originalData = JSON.parse(JSON.stringify(result.data));
                
                
                this.renderAvailability();
                
                // Update personal information fields if tutor info is available
                if (result.tutor_info) {
                    this.updatePersonalInfoFields(result.tutor_info);
                }
            } else {
                throw new Error(result.message || 'Failed to load availability data');
            }
        } catch (error) {
            console.error('Error loading availability:', error);
            this.showMessage('Error loading availability data: ' + error.message, 'error');
        }
    }

    renderAvailability() {
        const container = document.getElementById('availabilityContainer');
        container.innerHTML = '';

        // Create account boxes for each account
        Object.keys(this.accountConfigs).forEach(accountName => {
            const accountData = this.accounts[accountName] || this.getDefaultAccountData(accountName);
            const config = this.accountConfigs[accountName];
            
            const accountBox = this.createAccountBox(accountName, accountData, config);
            container.appendChild(accountBox);
            
            // Initialize the display for this account
            this.refreshTimeSlotsDisplay(accountName);
        });
    }

    getDefaultAccountData(accountName) {
        return {
            account_name: accountName,
            status: 'active',
            available_days: [],
            available_times: {},
            preferred_time_range: 'flexible',
            timezone: 'UTC',
            availability_notes: null
        };
    }

    createAccountBox(accountName, accountData, config) {
        const box = document.createElement('div');
        box.className = `${config.bgColor} rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow duration-200`;
        box.dataset.accountName = accountName;

        const availableDays = accountData.available_days || [];
        const availableTimes = accountData.available_times || {};

        box.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <i class="${config.icon} ${config.textColor}"></i>
                    <h4 class="font-semibold ${config.textColor}">${accountName}</h4>
                </div>
                ${config.timeRestrictions.enabled ? `
                    <div class="text-xs ${config.textColor} bg-white dark:bg-gray-700 px-2 py-1 rounded">
                        <i class="fas fa-clock mr-1"></i>
                        ${config.timeRestrictions.startTime} - ${config.timeRestrictions.endTime}
                </div>
                ` : `
                    <div class="text-xs ${config.textColor} bg-white dark:bg-gray-700 px-2 py-1 rounded">
                        <i class="fas fa-clock mr-1"></i>
                        Open Time
                </div>
                `}
            </div>

                <div class="flex flex-wrap gap-2 mb-3">
                ${this.days.map(day => `
                    <label class="flex items-center space-x-1 text-gray-700 dark:text-gray-300 text-sm">
                        <input type="checkbox"
                               class="w-4 h-4 border-gray-300 dark:border-gray-500 day-checkbox" 
                               data-day="${day}"
                               ${availableDays.includes(day) ? 'checked' : ''}
                               style="accent-color: ${config.accentColor}">
                        <span>${day.substring(0, 3)}</span>
                    </label>
                `).join('')}
                </div>

            <div class="space-y-2" id="timeSlots-${accountName}">
                ${this.renderTimeSlots(accountName, availableTimes, config)}
            </div>

        `;

        return box;
    }

    renderTimeSlots(accountName, availableTimes, config) {
        const daySlots = availableTimes || {};
        let html = '';

        // Only show time slots for checked days
        const accountBox = document.querySelector(`[data-account-name="${accountName}"]`);
        if (accountBox) {
            const checkedDays = Array.from(accountBox.querySelectorAll('.day-checkbox:checked'));
            const checkedDayNames = checkedDays.map(checkbox => checkbox.dataset.day);

            checkedDayNames.forEach(day => {
                const dayTimes = daySlots[day] || [];
                if (dayTimes.length > 0) {
                    dayTimes.forEach((timeRange, index) => {
                        html += this.createTimeSlotHTML(accountName, day, timeRange, index, config);
                    });
                }
            });
        }

        return html || '<p class="text-sm text-gray-500 dark:text-gray-400">No time slots set</p>';
    }

    createTimeSlotHTML(accountName, day, timeRange, index, config) {
        const [startTime, endTime] = timeRange.split(' - ').map(time => time.trim());
        const accountTimeSlots = this.getTimeSlotsForAccount(accountName);
        
        
        return `
            <div class="time-slot-item flex items-center gap-2 p-2 bg-white dark:bg-gray-700 rounded border" data-day="${day}" data-index="${index}">
                <span class="text-xs text-gray-600 dark:text-gray-400 w-12">${day.substring(0, 3)}:</span>
                <select class="start-time text-xs border rounded px-2 py-1 w-20" style="border-color: ${config.accentColor}" data-account="${accountName}" data-day="${day}">
                    ${accountTimeSlots.map(time => `<option value="${time}" ${time === startTime ? 'selected' : ''}>${time}</option>`).join('')}
                    </select>
                <span class="text-xs text-gray-500">-</span>
                <select class="end-time text-xs border rounded px-2 py-1 w-20" style="border-color: ${config.accentColor}" data-account="${accountName}" data-day="${day}">
                    ${accountTimeSlots.map(time => {
                        const isDisabled = this.isTimeBefore(time, startTime);
                        return `<option value="${time}" ${time === endTime ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${time}</option>`;
                    }).join('')}
                    </select>
            </div>
        `;
    }

    generateTimeSlots() {
        const slots = [];
        for (let hour = 0; hour < 24; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                slots.push(timeString);
            }
        }
        return slots;
    }

    // Convert 24-hour format to 12-hour format for display
    formatTimeForDisplay(time24) {
        // Handle case where time might already be in 12-hour format
        if (time24.includes('AM') || time24.includes('PM')) {
            return time24;
        }
        
        const [hours, minutes] = time24.split(':');
        const hour = parseInt(hours);
        const minute = parseInt(minutes);
        
        if (hour === 0) {
            return `12:${minutes} AM`;
        } else if (hour < 12) {
            return `${hour}:${minutes} AM`;
        } else if (hour === 12) {
            return `12:${minutes} PM`;
        } else {
            return `${hour - 12}:${minutes} PM`;
        }
    }

    // Convert 12-hour format back to 24-hour format
    formatTimeForBackend(time12) {
        const [time, period] = time12.split(' ');
        const [hours, minutes] = time.split(':');
        let hour = parseInt(hours);
        
        if (period === 'AM') {
            if (hour === 12) hour = 0;
        } else { // PM
            if (hour !== 12) hour += 12;
        }
        
        return `${hour.toString().padStart(2, '0')}:${minutes}`;
    }

    // Check if time1 is before time2 (both in 24-hour format)
    isTimeBefore(time1, time2) {
        const [hour1, minute1] = time1.split(':').map(Number);
        const [hour2, minute2] = time2.split(':').map(Number);
        
        const minutes1 = hour1 * 60 + minute1;
        const minutes2 = hour2 * 60 + minute2;
        
        return minutes1 <= minutes2;
    }

    getTimeSlotsForAccount(accountName) {
        const config = this.accountConfigs[accountName];
        if (!config.timeRestrictions.enabled) {
            console.log(`Account ${accountName}: Using all time slots (no restrictions)`);
            return this.timeSlots; // Return all time slots for unrestricted accounts
        }

        const { startTime, endTime } = config.timeRestrictions;
        const startHour = parseInt(startTime.split(':')[0]);
        const startMinute = parseInt(startTime.split(':')[1]);
        const endHour = parseInt(endTime.split(':')[0]);
        const endMinute = parseInt(endTime.split(':')[1]);
        
        // Calculate start and end times in minutes outside the loop
        const startInMinutes = startHour * 60 + startMinute;
        const endInMinutes = endHour * 60 + endMinute;

        const restrictedSlots = [];
        for (let hour = 0; hour < 24; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                
                // Check if time is within allowed range
                const timeInMinutes = hour * 60 + minute;
                
                if (timeInMinutes >= startInMinutes && timeInMinutes <= endInMinutes) {
                    restrictedSlots.push(timeString);
                }
            }
        }
        return restrictedSlots;
    }

    setupEventListeners() {
        // Save button
        document.getElementById('saveAvailabilityBtn').addEventListener('click', () => {
            this.saveAvailability();
        });

        // Reset button
        document.getElementById('resetAvailabilityBtn').addEventListener('click', () => {
            this.resetAvailability();
        });

        // Day checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('day-checkbox')) {
                this.handleDayChange(e.target);
            }
        });

        // Time slot changes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('start-time')) {
                this.handleStartTimeChange(e.target);
                this.handleTimeSlotChange(e.target);
            } else if (e.target.classList.contains('end-time')) {
                this.handleTimeSlotChange(e.target);
            }
        });



    }

    handleDayChange(checkbox) {
        const accountName = checkbox.closest('[data-account-name]').dataset.accountName;
        const day = checkbox.dataset.day;
        const isChecked = checkbox.checked;

        if (!this.accounts[accountName]) {
            this.accounts[accountName] = this.getDefaultAccountData(accountName);
        }

        if (isChecked) {
            if (!this.accounts[accountName].available_days.includes(day)) {
                this.accounts[accountName].available_days.push(day);
            }
            
            // Automatically create a default time slot for this day if it doesn't exist
            if (!this.accounts[accountName].available_times[day] || this.accounts[accountName].available_times[day].length === 0) {
                const config = this.accountConfigs[accountName];
                let defaultStartTime, defaultEndTime;
                
                if (config.timeRestrictions.enabled) {
                    defaultStartTime = config.timeRestrictions.startTime;
                    // Add 1 hour to start time for end time, but don't exceed the restriction
                    const startHour = parseInt(defaultStartTime.split(':')[0]);
                    const startMinute = parseInt(defaultStartTime.split(':')[1]);
                    const endHour = parseInt(config.timeRestrictions.endTime.split(':')[0]);
                    const endMinute = parseInt(config.timeRestrictions.endTime.split(':')[1]);
                    
                    const startInMinutes = startHour * 60 + startMinute;
                    const endInMinutes = endHour * 60 + endMinute;
                    const defaultEndInMinutes = Math.min(startInMinutes + 60, endInMinutes);
                    
                    const defaultEndHour = Math.floor(defaultEndInMinutes / 60);
                    const defaultEndMin = defaultEndInMinutes % 60;
                    defaultEndTime = `${defaultEndHour.toString().padStart(2, '0')}:${defaultEndMin.toString().padStart(2, '0')}`;
                } else {
                    defaultStartTime = '09:00';
                    defaultEndTime = '10:00';
                }
                
                this.accounts[accountName].available_times[day] = [`${defaultStartTime} - ${defaultEndTime}`];
            }
        } else {
            this.accounts[accountName].available_days = this.accounts[accountName].available_days.filter(d => d !== day);
            // Remove time slots for this day
            if (this.accounts[accountName].available_times[day]) {
                delete this.accounts[accountName].available_times[day];
            }
        }

        // Re-render the time slots to show/hide based on checked days
        this.refreshTimeSlotsDisplay(accountName);
        this.markAsChanged();
    }

    handleStartTimeChange(select) {
        const timeSlotItem = select.closest('.time-slot-item');
        const endTimeSelect = timeSlotItem.querySelector('.end-time');
        const startTime = select.value;
        
        // Update end time dropdown options
        const accountName = select.dataset.account;
        const config = this.accountConfigs[accountName];
        const accountTimeSlots = this.getTimeSlotsForAccount(accountName);
        
        endTimeSelect.innerHTML = accountTimeSlots.map(time => {
            const isDisabled = this.isTimeBefore(time, startTime);
            const isSelected = endTimeSelect.value === time && !isDisabled;
            return `<option value="${time}" ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}>${time}</option>`;
        }).join('');
        
        // If current end time is now disabled, select the next available time
        if (this.isTimeBefore(endTimeSelect.value, startTime)) {
            const nextAvailableTime = accountTimeSlots.find(time => !this.isTimeBefore(time, startTime));
            if (nextAvailableTime) {
                endTimeSelect.value = nextAvailableTime;
            }
        }
    }

    handleTimeSlotChange(select) {
        const timeSlotItem = select.closest('.time-slot-item');
        const accountName = timeSlotItem.closest('[data-account-name]').dataset.accountName;
        const day = timeSlotItem.dataset.day;
        const startTime = timeSlotItem.querySelector('.start-time').value;
        const endTime = timeSlotItem.querySelector('.end-time').value;

        if (!this.accounts[accountName]) {
            this.accounts[accountName] = this.getDefaultAccountData(accountName);
        }

        if (!this.accounts[accountName].available_times[day]) {
            this.accounts[accountName].available_times[day] = [];
        }

        const timeRange = `${startTime} - ${endTime}`;
        this.accounts[accountName].available_times[day] = [timeRange];

        this.markAsChanged();
    }



    refreshTimeSlotsDisplay(accountName) {
        const container = document.getElementById(`timeSlots-${accountName}`);
        const config = this.accountConfigs[accountName];
        const accountData = this.accounts[accountName] || this.getDefaultAccountData(accountName);
        
        // Re-render time slots based on current checked days and saved data
        const newTimeSlotsHTML = this.renderTimeSlots(accountName, accountData.available_times, config);
        container.innerHTML = newTimeSlotsHTML;
    }

    markAsChanged() {
        this.hasChanges = true;
        document.getElementById('saveAvailabilityBtn').disabled = false;
    }

    async saveAvailability() {
        if (!this.hasChanges) {
            this.showMessage('No changes to save', 'info');
            return;
        }

        const saveBtn = document.getElementById('saveAvailabilityBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
        saveBtn.disabled = true;

        try {
            const response = await fetch('/tutor/availability/update-multiple', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    accounts: Object.values(this.accounts).map(account => ({
                        account_name: account.account_name,
                        available_days: account.available_days,
                        available_times: account.available_times,
                        preferred_time_range: account.preferred_time_range,
                        timezone: account.timezone,
                        availability_notes: account.availability_notes
                    }))
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showMessage('Availability updated successfully!', 'success');
                this.originalData = JSON.parse(JSON.stringify(this.accounts));
                this.hasChanges = false;
                saveBtn.disabled = true;
            } else {
                throw new Error(result.message || 'Failed to save availability');
            }
        } catch (error) {
            console.error('Error saving availability:', error);
            this.showMessage('Error saving availability: ' + error.message, 'error');
        } finally {
            saveBtn.innerHTML = originalText;
        }
    }

    resetAvailability() {
        if (confirm('Are you sure you want to reset all changes?')) {
            this.accounts = JSON.parse(JSON.stringify(this.originalData));
            this.renderAvailability();
            this.hasChanges = false;
            document.getElementById('saveAvailabilityBtn').disabled = true;
            this.showMessage('Changes reset', 'info');
        }
    }

    updatePersonalInfoFields(tutorInfo) {
        // Update profile header
        const fullNameElement = document.querySelector('h2');
        if (fullNameElement && tutorInfo.full_name) {
            fullNameElement.textContent = tutorInfo.full_name;
        }
        
        const emailElement = document.querySelector('p.text-gray-700');
        if (emailElement && tutorInfo.email) {
            emailElement.textContent = tutorInfo.email;
        }
        
        // Update personal information form fields
        const fields = [
            { selector: 'input[type="text"]:nth-of-type(1)', value: tutorInfo.first_name },
            { selector: 'input[type="text"]:nth-of-type(2)', value: tutorInfo.last_name },
            { selector: 'input[type="date"]', value: tutorInfo.date_of_birth },
            { selector: 'input[type="text"]:nth-of-type(3)', value: tutorInfo.address },
            { selector: 'input[type="email"]', value: tutorInfo.email },
            { selector: 'input[type="text"]:nth-of-type(4)', value: tutorInfo.contact_number },
            { selector: 'input[type="text"]:nth-of-type(5)', value: tutorInfo.ms_teams_id || '' }
        ];
        
        fields.forEach(field => {
            const element = document.querySelector(field.selector);
            if (element && field.value) {
                element.value = field.value;
            }
        });
        
        // Update payment information if available
        if (tutorInfo.payment_info) {
            this.updatePaymentInfoDisplay(tutorInfo.payment_info);
        } else {
            this.showNoPaymentInfo();
        }
    }

    updatePaymentInfoDisplay(paymentInfo) {
        const container = document.getElementById('paymentInfoContainer');
        if (!container) return;

        const paymentMethods = {
            'gcash': 'GCash',
            'paypal': 'PayPal',
            'paymaya': 'PayMaya',
            'bank_transfer': 'Bank Transfer',
            'cash': 'Cash'
        };


        const formatPaymentMethod = (method) => {
            return paymentMethods[method] || method || 'N/A';
        };

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Payment Method -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Payment Method</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">${formatPaymentMethod(paymentInfo.payment_method)}</p>
                </div>


                <!-- Account Name -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Account Name</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">${paymentInfo.account_name || 'N/A'}</p>
                </div>

                <!-- Bank Information -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Bank Information</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        ${paymentInfo.bank_name || 'N/A'}
                        ${paymentInfo.account_number ? `<br><span class="text-xs text-gray-500">${paymentInfo.account_number}</span>` : ''}
                    </p>
                </div>

                <!-- Digital Wallet Information -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Digital Wallet</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        ${paymentInfo.gcash_number || paymentInfo.paypal_email || paymentInfo.paymaya_number || 'N/A'}
                    </p>
                </div>
            </div>

        `;
    }

    showNoPaymentInfo() {
        const container = document.getElementById('paymentInfoContainer');
        if (!container) return;

        container.innerHTML = `
            <div class="text-center py-8">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
        </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No Payment Information</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Payment information has not been set up yet.</p>
                <div class="mt-6">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Set Up Payment Information
                    </button>
    </div>
</div>
        `;
    }

    showMessage(message, type) {
        const messageDiv = document.getElementById('availabilityMessage');
        messageDiv.className = `mb-4 p-3 rounded-lg text-sm ${this.getMessageClasses(type)}`;
        messageDiv.textContent = message;
        messageDiv.classList.remove('hidden');

        // Auto-hide after 5 seconds
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 5000);
    }

    getMessageClasses(type) {
        switch (type) {
            case 'success':
                return 'bg-green-100 border border-green-400 text-green-700';
            case 'error':
                return 'bg-red-100 border border-red-400 text-red-700';
            case 'warning':
                return 'bg-yellow-100 border border-yellow-400 text-yellow-700';
            case 'info':
            default:
                return 'bg-blue-100 border border-blue-400 text-blue-700';
        }
    }
}
</script>
