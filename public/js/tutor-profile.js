document.addEventListener('DOMContentLoaded', function() {
    // Initialize availability management
    const availabilityManager = new AvailabilityManager();
    availabilityManager.init();
    
    // Initialize personal information management
    setupPersonalInfoEventListeners();
});

function setupPersonalInfoEventListeners() {
    const editBtn = document.getElementById('editPersonalInfoBtn');
    const saveBtn = document.getElementById('savePersonalInfoBtn');
    const cancelBtn = document.getElementById('cancelPersonalInfoBtn');
    
    if (editBtn) {
        editBtn.addEventListener('click', enablePersonalInfoEditing);
    }
    
    if (saveBtn) {
        saveBtn.addEventListener('click', savePersonalInfo);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', cancelPersonalInfoEditing);
    }
}

function enablePersonalInfoEditing() {
    // Enable form fields
    const fields = ['firstName', 'lastName', 'dateOfBirth', 'address', 'email', 'phoneNumber', 'msTeamsId'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.removeAttribute('readonly');
            field.classList.remove('bg-gray-50', 'dark:bg-gray-700');
            field.classList.add('bg-white', 'dark:bg-gray-900');
        }
    });
    
    // Show/hide buttons
    document.getElementById('editPersonalInfoBtn').classList.add('hidden');
    document.getElementById('savePersonalInfoBtn').classList.remove('hidden');
    document.getElementById('cancelPersonalInfoBtn').classList.remove('hidden');
}

function cancelPersonalInfoEditing() {
    // Disable form fields
    const fields = ['firstName', 'lastName', 'dateOfBirth', 'address', 'email', 'phoneNumber', 'msTeamsId'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.setAttribute('readonly', 'readonly');
            field.classList.add('bg-gray-50', 'dark:bg-gray-700');
            field.classList.remove('bg-white', 'dark:bg-gray-900');
        }
    });
    
    // Show/hide buttons
    document.getElementById('editPersonalInfoBtn').classList.remove('hidden');
    document.getElementById('savePersonalInfoBtn').classList.add('hidden');
    document.getElementById('cancelPersonalInfoBtn').classList.add('hidden');
    
    // Hide any messages
    const messageDiv = document.getElementById('personalInfoMessage');
    if (messageDiv) {
        messageDiv.classList.add('hidden');
    }
}

async function savePersonalInfo() {
    // Show confirmation modal
    showConfirmationModal(
        'Update Personal Information',
        'Are you sure you want to update your personal information?',
        proceedWithPersonalInfoUpdate
    );
}

async function proceedWithPersonalInfoUpdate() {
    const saveBtn = document.getElementById('savePersonalInfoBtn');
    const messageDiv = document.getElementById('personalInfoMessage');
    
    // Disable save button
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
    
    try {
        const formData = {
            first_name: document.getElementById('firstName').value,
            last_name: document.getElementById('lastName').value,
            date_of_birth: document.getElementById('dateOfBirth').value,
            address: document.getElementById('address').value,
            email: document.getElementById('email').value,
            phone_number: document.getElementById('phoneNumber').value,
            ms_teams_id: document.getElementById('msTeamsId').value
        };
        
        const response = await fetch('/tutor/update-personal-info', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success notification
            showNotification('Personal information updated successfully!', 'success');
            
            // Disable editing mode
            cancelPersonalInfoEditing();
        } else {
            // Show error notification
            showNotification(result.message || 'Failed to update personal information. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error updating personal information:', error);
        showNotification('An error occurred while updating your personal information. Please try again.', 'error');
    } finally {
        // Re-enable save button
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i>Save Changes';
    }
}

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

    getDaysForAccount(accountName) {
        // GLS account only shows weekdays (Monday-Friday)
        if (accountName === 'GLS') {
            return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        }
        // All other accounts show all days including weekends
        return this.days;
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
                ${this.getDaysForAccount(accountName).map(day => `
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

// Modal confirmation function
function showConfirmationModal(title, message, onConfirm) {
    // Remove existing modal if any
    const existingModal = document.getElementById('confirmationModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Create modal
    const modal = document.createElement('div');
    modal.id = 'confirmationModal';
    modal.className = 'fixed inset-0 z-50 overflow-y-auto';
    modal.innerHTML = `
        <div class="flex items-center justify-center min-h-screen px-4 py-4">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeConfirmationModal()"></div>
            <div class="relative w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-lg">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                    <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400 text-xl"></i>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">${title}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">${message}</p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="closeConfirmationModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-md transition-colors duration-200">
                            Cancel
                        </button>
                        <button onclick="proceedWithConfirmation()" 
                            class="px-4 py-2 text-sm font-medium text-white bg-[#F39C12] hover:bg-[#D97706] rounded-md transition-colors duration-200">
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Store the confirmation callback
    window.confirmationCallback = onConfirm;

    // Add modal to body
    document.body.appendChild(modal);
}

// Close modal function
function closeConfirmationModal() {
    const modal = document.getElementById('confirmationModal');
    if (modal) {
        modal.remove();
    }
    window.confirmationCallback = null;
}

// Proceed with confirmation
function proceedWithConfirmation() {
    if (window.confirmationCallback) {
        window.confirmationCallback();
    }
    closeConfirmationModal();
}

// Toast notification function
function showNotification(message, type = 'info') {
    // Remove existing notification if any
    const existingNotification = document.getElementById('toastNotification');
    if (existingNotification) {
        existingNotification.remove();
    }

    // Create notification
    const notification = document.createElement('div');
    notification.id = 'toastNotification';
    notification.className = `fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-in-out translate-y-0 opacity-100`;
    
    // Get icon and colors based on type
    let icon, bgColor, textColor, borderColor;
    switch(type) {
        case 'success':
            icon = 'fas fa-check-circle';
            bgColor = 'bg-green-50 dark:bg-green-900';
            textColor = 'text-green-800 dark:text-green-200';
            borderColor = 'border-green-200 dark:border-green-700';
            break;
        case 'error':
            icon = 'fas fa-exclamation-circle';
            bgColor = 'bg-red-50 dark:bg-red-900';
            textColor = 'text-red-800 dark:text-red-200';
            borderColor = 'border-red-200 dark:border-red-700';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            bgColor = 'bg-yellow-50 dark:bg-yellow-900';
            textColor = 'text-yellow-800 dark:text-yellow-200';
            borderColor = 'border-yellow-200 dark:border-yellow-700';
            break;
        case 'info':
        default:
            icon = 'fas fa-info-circle';
            bgColor = 'bg-blue-50 dark:bg-blue-900';
            textColor = 'text-blue-800 dark:text-blue-200';
            borderColor = 'border-blue-200 dark:border-blue-700';
            break;
    }

    notification.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${icon} ${textColor}"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium ${textColor}">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button onclick="closeNotification()" class="inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add to body
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        closeNotification();
    }, 5000);
}

// Close notification function
function closeNotification() {
    const notification = document.getElementById('toastNotification');
    if (notification) {
        notification.remove();
    }
}