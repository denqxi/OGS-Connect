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
        if (currentClassId) {
            url += `?class_id=${currentClassId}`;
        }
        
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
                // Main tutor dropdown: exclude already assigned main tutors (but NOT backup tutor)
                const availableMainTutors = data.tutors.filter(tutor => {
                    const isAssignedMain = assignedMainTutorUsernames.some(username => 
                        username && tutor.username && username.trim().toLowerCase() === tutor.username.trim().toLowerCase()
                    );
                    return !isAssignedMain;
                });
                // Backup tutor dropdown: exclude already assigned main tutors and exclude if already selected as main
                const availableBackupTutors = data.tutors.filter(tutor => {
                    const isAssignedMain = assignedMainTutorUsernames.some(username => 
                        username && tutor.username && username.trim().toLowerCase() === tutor.username.trim().toLowerCase()
                    );
                    // If this tutor is already selected as main, don't show in backup
                    return !isAssignedMain && (!backupTutor || tutor.username !== backupTutor.username);
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
                
                // Store original options for search functionality
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
            alert(`Cannot add more tutors. Maximum of ${requiredTutors} tutors allowed for this class.`);
            closeDropdown('main');
            return;
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
        if (tutor.username && currentClassId) {
            fetch('/api/check-tutor-time-conflict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tutor_username: tutor.username,
                    class_id: currentClassId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && !data.has_conflict) {
                    backupTutor = {
                        fullName: tutor.full_name,
                        username: tutor.username
                    };
                    fetchAvailableTutors();
                } else if (data.has_conflict) {
                    const proceed = confirm(`BACKUP TUTOR TIME CONFLICT WARNING: ${data.message}\n\nDo you still want to assign this backup tutor?`);
                    if (proceed) {
                        backupTutor = {
                            fullName: tutor.full_name,
                            username: tutor.username
                        };
                        fetchAvailableTutors();
                    }
                } else {
                    alert('Error checking time conflicts: ' + (data.message || 'Unknown error'));
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
                class_id: currentClassId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && !data.has_conflict) {
                // No conflict
                if (isBackup) {
                    backupTutor = {
                        fullName: tutor.full_name,
                        username: tutor.username
                    };
                } else {
                    tutors.push({
                        fullName: tutor.full_name,
                        username: tutor.username
                    });
                    renderTutors();
                }
                fetchAvailableTutors();
            } else if (data.has_conflict) {
                // Conflict detected
                const message = isBackup ? 
                    `BACKUP TUTOR TIME CONFLICT WARNING: ${data.message}\n\nDo you still want to assign this backup tutor?` :
                    `TIME CONFLICT WARNING: ${data.message}\n\nDo you still want to assign this tutor?`;
                
                const proceed = confirm(message);
                if (proceed) {
                    if (isBackup) {
                        backupTutor = {
                            fullName: tutor.full_name,
                            username: tutor.username
                        };
                    } else {
                        tutors.push({
                            fullName: tutor.full_name,
                            username: tutor.username
                        });
                        renderTutors();
                    }
                    fetchAvailableTutors();
                }
            } else {
                alert('Error checking time conflicts: ' + (data.message || 'Unknown error'));
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
        console.log('Click detected:', e.target);
        
        if (e.target.closest(".editBtn")) {
            console.log('Edit button clicked!');
            const btn = e.target.closest(".editBtn");
            
            if (modalClass) modalClass.textContent = btn.dataset.class;
            if (modalSchool) modalSchool.textContent = btn.dataset.school;
            if (modalTime) modalTime.textContent = btn.dataset.time;
            if (modalDate) modalDate.textContent = btn.dataset.date;
            if (modalRequired) modalRequired.textContent = btn.dataset.required;
            
            currentClassId = btn.dataset.classId; // Store class ID for saving
            
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
                alert('No class selected');
                return;
            }

            // Show loading state
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Saving...';
            saveBtn.disabled = true;

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
                    alert('Tutor assignments saved successfully!');
                    closeModal();
                    location.reload();
                } else {
                    alert('Error saving assignments: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error saving assignments: ' + error.message);
            })
            .finally(() => {
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        });
    }
});

/**
 * Save schedule as partial or final
 */
function saveScheduleAs(status, date) {
    const statusText = status === 'partial' ? 'Partial' : 'Final';
    const confirmMessage = status === 'final' 
        ? `Are you sure you want to finalize the schedule for ${date}? This will archive it and it cannot be modified.`
        : `Save the schedule for ${date} as ${statusText}?`;
    
    if (!confirm(confirmMessage)) {
        return;
    }

    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Saving...';
    button.disabled = true;

    fetch(`/schedules/save-as-${status}/${date}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            if (status === 'final') {
                // Redirect back to class scheduling after finalizing
                window.location.href = '/scheduling?tab=class';
            } else {
                // Just reload the current page for partial save
                window.location.reload();
            }
        } else {
            alert('Error saving schedule: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving schedule. Please try again.');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}