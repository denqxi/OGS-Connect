// Per Day Schedule Modal and Functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Per-day schedule modal script loaded');
    
    const modal = document.getElementById("editScheduleModal");
    const closeBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelModal");

    // Check if modal elements exist
    if (!modal) {
        console.error('Modal element not found');
        return;
    }
    
    console.log('Modal elements found successfully');
    
    // Check if edit buttons exist
    setTimeout(() => {
        const editButtons = document.querySelectorAll('.editBtn');
        console.log('Found edit buttons:', editButtons.length);
        editButtons.forEach((btn, index) => {
            console.log(`Edit button ${index}:`, btn);
        });
    }, 1000);

    // Modal elements
    const modalClass = document.getElementById("modalClass");
    const modalSchool = document.getElementById("modalSchool");
    const modalTime = document.getElementById("modalTime");
    const modalDate = document.getElementById("modalDate");
    const modalRequired = document.getElementById("modalRequired");

    // Tutor grid & dropdown
    let tutors = [];
    let backupTutor = null; // Store selected backup tutor
    let currentClassId = null; // Store current class ID for saving changes
    let currentClassInfo = null; // Store current class information (date, day, time_slot)
    const tutorGrid = document.getElementById("tutorGrid");
    const addTutorSelect = document.getElementById("addTutorSelect");
    const backupTutorSelect = document.getElementById("backupTutorSelect");
    
    // Searchable dropdown elements
    const addTutorSearch = document.getElementById("addTutorSearch");
    const backupTutorSearch = document.getElementById("backupTutorSearch");
    const addTutorDropdown = document.getElementById("addTutorDropdown");
    const backupTutorDropdown = document.getElementById("backupTutorDropdown");
    const addTutorContainer = document.getElementById("addTutorContainer");
    const backupTutorContainer = document.getElementById("backupTutorContainer");
    
    // Store original options for filtering
    let originalMainTutorOptions = [];
    let originalBackupTutorOptions = [];
    
    // Track dropdown states
    let isMainDropdownOpen = false;
    let isBackupDropdownOpen = false;

    function fetchAvailableTutors() {
        // Clear current options except the placeholder
        if (addTutorSelect) {
            addTutorSelect.innerHTML = '<option value="">Add tutor</option>';
        }
        if (backupTutorSelect) {
            backupTutorSelect.innerHTML = '<option value="">Select backup tutor</option>';
        }
        
        // Clear dropdown contents
        if (addTutorDropdown) {
            addTutorDropdown.innerHTML = '';
        }
        if (backupTutorDropdown) {
            backupTutorDropdown.innerHTML = '';
        }
        
        // Build URL with class information for availability filtering
        let url = '/api/available-tutors';
        const params = new URLSearchParams();
        
        if (currentClassId) {
            params.append('class_id', currentClassId);
        }
        
        // Add class-specific filtering parameters
        if (currentClassInfo) {
            if (currentClassInfo.date) {
                params.append('date', currentClassInfo.date);
            }
            if (currentClassInfo.day) {
                params.append('day', currentClassInfo.day);
            }
            if (currentClassInfo.time_slot) {
                params.append('time_slot', currentClassInfo.time_slot);
            }
        }
        
        if (params.toString()) {
            url += '?' + params.toString();
        }
        
        console.log('Fetching available tutors with URL:', url);
        console.log('Current class info:', currentClassInfo);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tutors) {
                // Get usernames of currently assigned main tutors (not display names)
                const assignedMainTutorUsernames = tutors.map(tutor => 
                    typeof tutor === 'object' ? tutor.username : tutor
                );
                // Get username of currently assigned backup tutor
                const assignedBackupTutorUsername = backupTutor ? backupTutor.username : null;
                // Main tutor dropdown: server already excludes main tutors but includes backup tutors
                const availableMainTutors = data.tutors.filter(tutor => {
                    const isAssignedMain = assignedMainTutorUsernames.some(username => 
                        username && tutor.username && username.trim().toLowerCase() === tutor.username.trim().toLowerCase()
                    );
                    // Server already filtered out main tutors, but double-check here
                    // Allow backup tutors to be available for promotion to main
                    return !isAssignedMain;
                });
                // Backup tutor dropdown: exclude already assigned main tutors, but allow current backup to stay visible
                const availableBackupTutors = data.tutors.filter(tutor => {
                    const isAssignedMain = assignedMainTutorUsernames.some(username => 
                        username && tutor.username && username.trim().toLowerCase() === tutor.username.trim().toLowerCase()
                    );
                    // Don't exclude current backup tutor - they should remain visible
                    return !isAssignedMain;
                });
                
                // Populate main tutor dropdown
                if (addTutorDropdown) {
                    if (availableMainTutors.length === 0) {
                        addTutorDropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No tutors available</div>';
                    } else {
                        availableMainTutors.forEach(tutor => {
                            const div = document.createElement('div');
                            div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                            div.textContent = tutor.full_name;
                            div.dataset.value = tutor.full_name;
                            div.dataset.username = tutor.username;
                            div.addEventListener('click', () => selectMainTutor(tutor));
                            addTutorDropdown.appendChild(div);
                        });
                    }
                }
                
                // Populate backup tutor dropdown
                if (backupTutorDropdown) {
                    if (availableBackupTutors.length === 0) {
                        backupTutorDropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No tutors available for backup</div>';
                    } else {
                        availableBackupTutors.forEach(tutor => {
                            const div = document.createElement('div');
                            div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                            div.textContent = tutor.full_name;
                            div.dataset.value = tutor.full_name;
                            div.dataset.username = tutor.username;
                            div.addEventListener('click', () => selectBackupTutor(tutor));
                            backupTutorDropdown.appendChild(div);
                        });
                    }
                }
                
                // Store original options for search functionality - use the same filtered lists
                originalMainTutorOptions = availableMainTutors;
                originalBackupTutorOptions = availableBackupTutors;
            } else {
                console.error('Failed to fetch tutors:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching tutors:', error);
        });
    }

    // Searchable dropdown functionality
    function selectMainTutor(tutor) {
        const requiredTutors = parseInt(modalRequired.textContent) || 0;
        
        // Check if we've reached the required tutor limit
        if (tutors.length >= requiredTutors) {
            showNotification(`Cannot add more tutors. Maximum of ${requiredTutors} tutors allowed for this class.`, 'warning');
            closeDropdown('main');
            return;
        }
        
        // Check if this tutor is currently the backup tutor
        if (backupTutor && tutor.username === backupTutor.username) {
            // Clear backup tutor since they're being promoted to main
            backupTutor = null;
            if (backupTutorSearch) {
                backupTutorSearch.value = '';
                backupTutorSearch.placeholder = 'Select backup tutor';
            }
        }
        
        // Check for time conflicts before adding
        if (tutor.username && currentClassId) {
            checkTutorConflictAndAdd(tutor, false); // false = not backup
        } else {
            // Fallback if no username or class ID
            tutors.push({
                fullName: tutor.full_name,
                username: tutor.username
            });
            renderTutors();
            fetchAvailableTutors();
        }
        
        closeDropdown('main');
        if (addTutorSearch) {
            addTutorSearch.value = '';
        }
    }
    
    function selectBackupTutor(tutor) {
        console.log('Backup tutor selected:', tutor);
        
        // Check for time conflicts before assigning backup tutor
        if (tutor.username && currentClassId && currentClassInfo) {
            fetch('/api/check-tutor-time-conflict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tutor_username: tutor.username,
                    date: currentClassInfo.date,
                    time_slot: currentClassInfo.time_slot,
                    exclude_class_id: currentClassId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.has_conflict) {
                    backupTutor = {
                        fullName: tutor.full_name,
                        username: tutor.username
                    };
                    fetchAvailableTutors();
                } else if (data.has_conflict) {
                    const conflictDetails = data.conflicts ? 
                        data.conflicts.map(c => `• ${c.class} at ${c.school} (${c.time})`).join('\n') : '';
                    const proceed = confirm(`BACKUP TUTOR TIME CONFLICT WARNING: ${data.message}\n\nConflicting classes:\n${conflictDetails}\n\nDo you still want to assign this backup tutor?`);
                    if (proceed) {
                        backupTutor = {
                            fullName: tutor.full_name,
                            username: tutor.username
                        };
                        fetchAvailableTutors();
                    }
                } else {
                    showNotification('Error checking time conflicts: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error checking time conflicts:', error);
                const proceed = confirm('Unable to check for time conflicts. Do you want to proceed?');
                if (proceed) {
                    backupTutor = {
                        fullName: tutor.full_name,
                        username: tutor.username
                    };
                    fetchAvailableTutors();
                }
            });
        } else {
            backupTutor = {
                fullName: tutor.full_name,
                username: tutor.username
            };
            fetchAvailableTutors();
        }
        closeDropdown('backup');
        if (backupTutorSearch) {
            backupTutorSearch.value = tutor.full_name;
        }
    }
    
    function checkTutorConflictAndAdd(tutor, isBackup) {
        fetch('/api/check-tutor-time-conflict', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                tutor_username: tutor.username,
                date: currentClassInfo ? currentClassInfo.date : null,
                time_slot: currentClassInfo ? currentClassInfo.time_slot : null,
                exclude_class_id: currentClassId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.has_conflict) {
                // No conflict
                if (isBackup) {
                    backupTutor = {
                        fullName: tutor.full_name,
                        username: tutor.username
                    };
                } else {
                    // Check if this tutor was previously the backup tutor
                    if (backupTutor && tutor.username === backupTutor.username) {
                        // Clear backup tutor since they're being promoted to main
                        backupTutor = null;
                        if (backupTutorSearch) {
                            backupTutorSearch.value = '';
                            backupTutorSearch.placeholder = 'Select backup tutor';
                        }
                    }
                    
                    tutors.push({
                        fullName: tutor.full_name,
                        username: tutor.username
                    });
                    renderTutors();
                }
                fetchAvailableTutors();
            } else if (data.has_conflict) {
                // Conflict detected
                const conflictDetails = data.conflicts ? 
                    data.conflicts.map(c => `• ${c.class} at ${c.school} (${c.time})`).join('\n') : '';
                const message = isBackup ? 
                    `BACKUP TUTOR TIME CONFLICT WARNING: ${data.message}\n\nConflicting classes:\n${conflictDetails}\n\nDo you still want to assign this backup tutor?` :
                    `TIME CONFLICT WARNING: ${data.message}\n\nConflicting classes:\n${conflictDetails}\n\nDo you still want to assign this tutor?`;
                
                const proceed = confirm(message);
                if (proceed) {
                    if (isBackup) {
                        backupTutor = {
                            fullName: tutor.full_name,
                            username: tutor.username
                        };
                    } else {
                        // Check if this tutor was previously the backup tutor
                        if (backupTutor && tutor.username === backupTutor.username) {
                            // Clear backup tutor since they're being promoted to main
                            backupTutor = null;
                            if (backupTutorSearch) {
                                backupTutorSearch.value = '';
                                backupTutorSearch.placeholder = 'Select backup tutor';
                            }
                        }
                        
                        tutors.push({
                            fullName: tutor.full_name,
                            username: tutor.username
                        });
                        renderTutors();
                    }
                    fetchAvailableTutors();
                }
            } else {
                showNotification('Error checking time conflicts: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error checking time conflicts:', error);
            const proceed = confirm('Unable to check for time conflicts. Do you want to proceed?');
            if (proceed) {
                if (isBackup) {
                    backupTutor = {
                        fullName: tutor.full_name,
                        username: tutor.username
                    };
                } else {
                    // Check if this tutor was previously the backup tutor
                    if (backupTutor && tutor.username === backupTutor.username) {
                        // Clear backup tutor since they're being promoted to main
                        backupTutor = null;
                        if (backupTutorSearch) {
                            backupTutorSearch.value = '';
                            backupTutorSearch.placeholder = 'Select backup tutor';
                        }
                    }
                    
                    tutors.push({
                        fullName: tutor.full_name,
                        username: tutor.username
                    });
                    renderTutors();
                }
                fetchAvailableTutors();
            }
        });
    }
    
    function openDropdown(type) {
        if (type === 'main') {
            isMainDropdownOpen = true;
            if (addTutorDropdown) {
                addTutorDropdown.classList.remove('hidden');
            }
            if (addTutorSearch) {
                addTutorSearch.removeAttribute('readonly');
                addTutorSearch.focus();
            }
        } else if (type === 'backup') {
            isBackupDropdownOpen = true;
            if (backupTutorDropdown) {
                backupTutorDropdown.classList.remove('hidden');
            }
            if (backupTutorSearch) {
                backupTutorSearch.removeAttribute('readonly');
                backupTutorSearch.focus();
            }
        }
    }
    
    function closeDropdown(type) {
        if (type === 'main') {
            isMainDropdownOpen = false;
            if (addTutorDropdown) {
                addTutorDropdown.classList.add('hidden');
            }
            if (addTutorSearch) {
                addTutorSearch.setAttribute('readonly', 'readonly');
            }
        } else if (type === 'backup') {
            isBackupDropdownOpen = false;
            if (backupTutorDropdown) {
                backupTutorDropdown.classList.add('hidden');
            }
            if (backupTutorSearch) {
                backupTutorSearch.setAttribute('readonly', 'readonly');
            }
        }
    }
    
    function filterDropdown(searchTerm, type) {
        const options = type === 'main' ? originalMainTutorOptions : originalBackupTutorOptions;
        const dropdown = type === 'main' ? addTutorDropdown : backupTutorDropdown;
        
        if (!dropdown) return;
        
        const filteredOptions = searchTerm ? 
            options.filter(tutor => 
                tutor.full_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                tutor.username.toLowerCase().includes(searchTerm.toLowerCase())
            ) : options;
        
        dropdown.innerHTML = '';
        
        if (filteredOptions.length === 0) {
            dropdown.innerHTML = '<div class="px-3 py-2 text-gray-500 text-sm">No tutors found</div>';
        } else {
            filteredOptions.forEach(tutor => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                div.textContent = tutor.full_name;
                div.dataset.value = tutor.full_name;
                div.dataset.username = tutor.username;
                
                if (type === 'main') {
                    div.addEventListener('click', () => selectMainTutor(tutor));
                } else {
                    div.addEventListener('click', () => selectBackupTutor(tutor));
                }
                
                dropdown.appendChild(div);
            });
        }
    }

    // Event listeners for searchable dropdowns
    if (addTutorSearch) {
        addTutorSearch.addEventListener('click', () => {
            if (!isMainDropdownOpen) {
                openDropdown('main');
            }
        });
        
        addTutorSearch.addEventListener('input', (e) => {
            filterDropdown(e.target.value, 'main');
        });
        
        addTutorSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown('main');
                e.target.value = '';
            }
        });
    }
    
    if (backupTutorSearch) {
        backupTutorSearch.addEventListener('click', () => {
            if (!isBackupDropdownOpen) {
                openDropdown('backup');
            }
        });
        
        backupTutorSearch.addEventListener('input', (e) => {
            filterDropdown(e.target.value, 'backup');
        });
        
        backupTutorSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown('backup');
                e.target.value = '';
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!addTutorContainer?.contains(e.target) && isMainDropdownOpen) {
            closeDropdown('main');
        }
        if (!backupTutorContainer?.contains(e.target) && isBackupDropdownOpen) {
            closeDropdown('backup');
        }
    });

    function renderTutors() {
        if (tutorGrid) {
            tutorGrid.innerHTML = "";
            const required = parseInt(modalRequired.textContent) || 0;
            if (tutors.length === 0) {
                tutorGrid.innerHTML = '<div class="col-span-2 text-center text-gray-500 py-4">No tutors assigned</div>';
            } else {
                // Only render tutors that are in the tutors array (main tutors), regardless of backup tutor
                for (let i = 0; i < required; i++) {
                    const tutor = tutors[i];
                    const div = document.createElement('div');
                    if (tutor) {
                        div.className = 'py-2 px-3 bg-green-50 border border-green-200 rounded text-green-700 text-center font-medium text-sm flex items-center justify-between';
                        div.innerHTML = `
                            <span>${tutor.fullName || tutor}</span>
                            <button class="removeTutorBtn ml-2 text-red-500 hover:text-red-700" data-index="${i}" title="Remove Tutor">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                    } else {
                        div.className = 'py-2 px-3 bg-gray-50 rounded text-gray-400 text-center text-sm';
                        div.textContent = 'Not Assigned';
                    }
                    tutorGrid.appendChild(div);
                }
            }
        }
    }

    // Remove tutor event delegation (for dynamically rendered remove buttons)
    tutorGrid?.addEventListener('click', function(e) {
        const btn = e.target.closest('.removeTutorBtn');
        if (btn) {
            const index = parseInt(btn.getAttribute('data-index'));
            if (!isNaN(index)) {
                tutors.splice(index, 1);
                renderTutors();
                fetchAvailableTutors();
            }
        }
    });

    // Event listeners for searchable dropdowns
    if (addTutorSearch) {
        addTutorSearch.addEventListener('click', () => {
            if (!isMainDropdownOpen) {
                openDropdown('main');
            }
        });
        
        addTutorSearch.addEventListener('input', (e) => {
            filterDropdown(e.target.value, 'main');
        });
        
        addTutorSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown('main');
                e.target.value = '';
            }
        });
    }
    
    if (backupTutorSearch) {
        backupTutorSearch.addEventListener('click', () => {
            if (!isBackupDropdownOpen) {
                openDropdown('backup');
            }
        });
        
        backupTutorSearch.addEventListener('input', (e) => {
            filterDropdown(e.target.value, 'backup');
        });
        
        backupTutorSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDropdown('backup');
                e.target.value = '';
            }
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!addTutorContainer?.contains(e.target) && isMainDropdownOpen) {
            closeDropdown('main');
        }
        if (!backupTutorContainer?.contains(e.target) && isBackupDropdownOpen) {
            closeDropdown('backup');
        }
    });

    // Use event delegation for edit buttons (since they're dynamically rendered)
    document.addEventListener("click", function(e) {
        if (e.target.closest(".editBtn")) {
            console.log('Edit button clicked!');
            const btn = e.target.closest(".editBtn");
            
            if (modalClass) modalClass.textContent = btn.dataset.class;
            if (modalSchool) modalSchool.textContent = btn.dataset.school;
            if (modalTime) modalTime.textContent = btn.dataset.time;
            if (modalDate) modalDate.textContent = btn.dataset.date;
            if (modalRequired) modalRequired.textContent = btn.dataset.required;
            
            currentClassId = btn.dataset.classId; // Store class ID for saving
            
            // Store class information for filtering available tutors
            currentClassInfo = {
                date: btn.dataset.rawDate,
                day: btn.dataset.day,
                time_slot: btn.dataset.timeSlot
            };
            
            // Clear tutors array and backup tutor first
            tutors = [];
            backupTutor = null;
            
            // Reset search inputs and close dropdowns
            if (addTutorSearch) {
                addTutorSearch.value = "";
                addTutorSearch.placeholder = "Add tutor";
                addTutorSearch.setAttribute('readonly', 'readonly');
            }
            if (backupTutorSearch) {
                backupTutorSearch.value = "";
                backupTutorSearch.placeholder = "Select backup tutor";
                backupTutorSearch.setAttribute('readonly', 'readonly');
            }
            if (addTutorDropdown) {
                addTutorDropdown.classList.add('hidden');
            }
            if (backupTutorDropdown) {
                backupTutorDropdown.classList.add('hidden');
            }
            
            // Reset dropdown states
            isMainDropdownOpen = false;
            isBackupDropdownOpen = false;
            
            console.log('Loading tutors for class ID:', currentClassId);
            
            // Load tutors using the new API that properly separates main and backup tutors
            loadClassTutors(currentClassId);
            
            if (modal) {
                modal.classList.remove("hidden");
                // Prevent background scrolling when modal is open
                document.body.style.overflow = 'hidden';
                console.log('Modal opened');
            } else {
                console.warn('Modal element not found');
            }
        }
    });

    /**
     * Load tutors for a specific class using the API
     */
    function loadClassTutors(classId) {
        console.log('Loading tutors for class:', classId);
        fetch(`/api/class-tutors/${classId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Class tutors response:', data);
            if (data.success) {
                // Only main tutors go into tutors array
                let loadedBackupTutor = null;
                if (data.backup_tutors && data.backup_tutors.length > 0) {
                    const firstBackup = data.backup_tutors[0];
                    loadedBackupTutor = {
                        fullName: firstBackup.full_name,
                        username: firstBackup.username
                    };
                }
                backupTutor = loadedBackupTutor;
                // Defensive: filter out backup tutor from main tutors array if present
                tutors = (data.main_tutors || []).map(tutor => ({
                    fullName: tutor.full_name,
                    username: tutor.username
                })).filter(tutor => {
                    if (!backupTutor) return true;
                    return tutor.username !== backupTutor.username;
                });
                console.log('Loaded main tutors:', tutors);
                console.log('Loaded backup tutor:', backupTutor);
                renderTutors();
                fetchAvailableTutors();
                // Set backup tutor display after a short delay to ensure options are loaded
                if (backupTutor) {
                    setTimeout(() => {
                        if (backupTutorSearch) {
                            backupTutorSearch.value = backupTutor.fullName;
                            console.log('Set backup tutor search field to:', backupTutor.fullName);
                        }
                    }, 500);
                } else if (backupTutorSearch) {
                    backupTutorSearch.value = '';
                }
            } else {
                console.error('Failed to load class tutors:', data.message);
                tutors = [];
                backupTutor = null;
                renderTutors();
                fetchAvailableTutors();
            }
        })
        .catch(error => {
            console.error('Error loading class tutors:', error);
            tutors = [];
            backupTutor = null;
            renderTutors();
            fetchAvailableTutors();
        });
    }

    // Function to close modal and restore scrolling
    function closeModal() {
        if (modal) {
            modal.classList.add("hidden");
            // Restore background scrolling when modal is closed
            document.body.style.overflow = '';
        }
    }

    // Close modal functionality
    if (closeBtn) {
        closeBtn.addEventListener("click", closeModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener("click", closeModal);
    }
    
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) closeModal();
        });
    }

    // Save tutor assignments
    const saveBtn = document.getElementById("saveChanges");
    if (saveBtn) {
        saveBtn.addEventListener("click", () => {
            if (!currentClassId) {
                showNotification('No class selected', 'error');
                return;
            }

            // Show confirmation modal
            showConfirmationModal();
        });
    }

    // Confirmation modal functionality
    function showConfirmationModal() {
        const confirmationModal = document.getElementById("confirmationModal");
        const confirmClass = document.getElementById("confirmClass");
        const confirmDate = document.getElementById("confirmDate");
        const confirmTime = document.getElementById("confirmTime");
        
        // Populate confirmation modal with current class details
        if (confirmClass && confirmDate && confirmTime) {
            confirmClass.textContent = document.getElementById("modalClass")?.textContent || "N/A";
            confirmDate.textContent = document.getElementById("modalDate")?.textContent || "N/A";
            confirmTime.textContent = document.getElementById("modalTime")?.textContent || "N/A";
        }
        
        confirmationModal.classList.remove("hidden");
    }

    function hideConfirmationModal() {
        const confirmationModal = document.getElementById("confirmationModal");
        confirmationModal.classList.add("hidden");
    }

    // Confirmation modal event listeners
    const closeConfirmationModal = document.getElementById("closeConfirmationModal");
    const cancelConfirmation = document.getElementById("cancelConfirmation");
    const confirmSave = document.getElementById("confirmSave");

    if (closeConfirmationModal) {
        closeConfirmationModal.addEventListener("click", hideConfirmationModal);
    }

    if (cancelConfirmation) {
        cancelConfirmation.addEventListener("click", hideConfirmationModal);
    }

    if (confirmSave) {
        confirmSave.addEventListener("click", () => {
            // Show loading state on confirmation button
            const originalText = confirmSave.textContent;
            confirmSave.textContent = 'Saving...';
            confirmSave.disabled = true;
            
            performSave(originalText);
        });
    }

    // Close confirmation modal when clicking outside
    const confirmationModal = document.getElementById("confirmationModal");
    if (confirmationModal) {
        confirmationModal.addEventListener("click", (e) => {
            if (e.target === confirmationModal) {
                hideConfirmationModal();
            }
        });
    }

    function performSave(originalText) {

        // Convert tutor objects to usernames for the API
        const tutorUsernames = tutors.map(tutor => {
            if (typeof tutor === 'object' && tutor.username) {
                return tutor.username;
            }
            return tutor;
        }).filter(username => {
            return username && typeof username === 'string' && username.trim() !== '';
        });

        // Prepare backup tutor for saving (if database field exists)
        const backupTutorData = backupTutor ? {
            username: backupTutor.username,
            full_name: backupTutor.fullName
        } : null;

        fetch('/api/save-tutor-assignments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                class_id: currentClassId,
                tutors: tutorUsernames,
                backup_tutor: backupTutorData
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text);
                }
            });
        })
        .then(data => {
            if (data.success) {
                showNotification('Tutor assignments saved successfully!', 'success');
                closeModal();
                location.reload();
            } else {
                showNotification('Error saving assignments: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showNotification('Error saving assignments: ' + error.message, 'error');
        })
        .finally(() => {
            confirmSave.textContent = originalText;
            confirmSave.disabled = false;
        });
    }
});

// Save as Final confirmation modal functionality
let saveFinalCallback = null;

function showSaveFinalConfirmation(date, callback) {
    const modal = document.getElementById('saveFinalConfirmationModal');
    const messageElement = document.getElementById('saveFinalMessage');
    const dateElement = document.getElementById('saveFinalDate');
    
    if (messageElement) {
        messageElement.textContent = `Are you sure you want to finalize the schedule for ${date}? This will archive it and it cannot be modified.`;
    }
    
    if (dateElement) {
        dateElement.textContent = date;
    }
    
    saveFinalCallback = callback;
    
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function hideSaveFinalConfirmation() {
    const modal = document.getElementById('saveFinalConfirmationModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    // Don't clear the callback immediately - let the callback execute first
    setTimeout(() => {
        saveFinalCallback = null;
    }, 100);
}

// Save as Final modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    const closeSaveFinalModal = document.getElementById('closeSaveFinalModal');
    const cancelSaveFinal = document.getElementById('cancelSaveFinal');
    const confirmSaveFinal = document.getElementById('confirmSaveFinal');
    const saveFinalModal = document.getElementById('saveFinalConfirmationModal');

    if (closeSaveFinalModal) {
        closeSaveFinalModal.addEventListener('click', hideSaveFinalConfirmation);
    }

    if (cancelSaveFinal) {
        cancelSaveFinal.addEventListener('click', hideSaveFinalConfirmation);
    }

    if (confirmSaveFinal) {
        confirmSaveFinal.addEventListener('click', () => {
            if (saveFinalCallback) {
                // Execute callback first, then hide modal
                saveFinalCallback();
                hideSaveFinalConfirmation();
            } else {
                console.error('No save final callback found');
                hideSaveFinalConfirmation();
            }
        });
    } else {
        console.error('confirmSaveFinal button not found');
    }

    // Close modal when clicking outside
    if (saveFinalModal) {
        saveFinalModal.addEventListener('click', (e) => {
            if (e.target === saveFinalModal) {
                hideSaveFinalConfirmation();
            }
        });
    }
});

/**
 * Save schedule as partial or final
 */
function saveScheduleAs(status, date) {
    const statusText = status === 'partial' ? 'Partial' : 'Final';
    
    if (status === 'final') {
        // Show custom confirmation modal for final save
        showSaveFinalConfirmation(date, () => {
            performSaveScheduleAs(status, date);
        });
    } else {
        // For partial saves, use simple confirm for now
        if (!confirm(`Save the schedule for ${date} as ${statusText}?`)) {
            return;
        }
        performSaveScheduleAs(status, date);
    }
}

function performSaveScheduleAs(status, date) {

    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Saving...';
    button.disabled = true;

    fetch('/schedules/save-schedule', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            date: date,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            if (status === 'final') {
                // Redirect to schedule history after finalizing
                setTimeout(() => {
                    window.location.href = '/scheduling?tab=history';
                }, 1200);
            } else {
                // Just reload the current page for partial save
                window.location.reload();
            }
        } else {
            showNotification('Error saving schedule: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving schedule. Please try again.', 'error');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}