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

    function fetchAvailableTutors() {
        // Clear current options except the placeholder
        if (addTutorSelect) {
            addTutorSelect.innerHTML = '<option value="">Add tutor</option>';
        }
        if (backupTutorSelect) {
            backupTutorSelect.innerHTML = '<option value="">Select backup tutor</option>';
        }
        
        fetch('/api/available-tutors', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.tutors) {
                // Filter out tutors that are already assigned (by full name)
                const assignedTutorNames = tutors.map(tutor => 
                    typeof tutor === 'object' ? tutor.fullName : tutor
                );
                const availableTutors = data.tutors.filter(tutor => 
                    !assignedTutorNames.includes(tutor.full_name)
                );
                
                // Populate main tutor dropdown
                if (addTutorSelect) {
                    if (availableTutors.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No tutors available';
                        option.disabled = true;
                        addTutorSelect.appendChild(option);
                    } else {
                        availableTutors.forEach(tutor => {
                            const option = document.createElement('option');
                            option.value = tutor.full_name;
                            option.dataset.username = tutor.username;
                            option.textContent = tutor.full_name;
                            addTutorSelect.appendChild(option);
                        });
                    }
                }
                
                // Populate backup tutor dropdown (includes all tutors, even assigned ones)
                if (backupTutorSelect) {
                    data.tutors.forEach(tutor => {
                        const option = document.createElement('option');
                        option.value = tutor.full_name;
                        option.dataset.username = tutor.username;
                        option.textContent = tutor.full_name;
                        // Mark if currently assigned as main tutor
                        if (assignedTutorNames.includes(tutor.full_name)) {
                            option.textContent += ' (Main Tutor)';
                            option.style.fontStyle = 'italic';
                            option.style.color = '#6b7280';
                        }
                        backupTutorSelect.appendChild(option);
                    });
                }
            } else {
                console.error('Failed to fetch tutors:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching tutors:', error);
        });
    }

    function renderTutors() {
        if (tutorGrid) {
            tutorGrid.innerHTML = "";
            if (tutors.length === 0) {
                tutorGrid.innerHTML = '<div class="col-span-2 text-center text-gray-500 py-4">No tutors assigned</div>';
            } else {
                tutors.forEach((tutor, index) => {
                    const div = document.createElement("div");
                    div.className = "flex justify-between items-center border px-3 py-2 rounded";
                    const displayName = typeof tutor === 'object' ? tutor.fullName : tutor;
                    div.innerHTML = `<span>${displayName}</span>
                    <button class="text-red-500 font-bold" onclick="removeTutor(${index})">&times;</button>`;
                    tutorGrid.appendChild(div);
                });
            }
        }
    }

    // Make removeTutor globally available
    window.removeTutor = function(index) {
        tutors.splice(index, 1);
        renderTutors();
        fetchAvailableTutors(); // Update dropdowns when tutor is removed
    }

    if (addTutorSelect) {
        addTutorSelect.addEventListener("change", function() {
            const selectedTutor = this.value;
            const selectedOption = this.options[this.selectedIndex];
            const requiredTutors = parseInt(modalRequired.textContent) || 0;
            
            if (selectedTutor && !tutors.some(tutor => tutor.fullName === selectedTutor)) {
                // Check if we've reached the required tutor limit
                if (tutors.length >= requiredTutors) {
                    alert(`Cannot add more tutors. Maximum of ${requiredTutors} tutors allowed for this class.`);
                    this.value = "";
                    return;
                }
                
                // Store both full name and username for saving
                tutors.push({
                    fullName: selectedTutor,
                    username: selectedOption.dataset.username
                });
                renderTutors();
                fetchAvailableTutors(); // Update dropdowns when tutor is added
            }
            this.value = "";
        });
    }

    // Backup tutor selection handler
    if (backupTutorSelect) {
        backupTutorSelect.addEventListener("change", function() {
            const selectedFullName = this.value; // This is the full name
            const selectedOption = this.options[this.selectedIndex];
            
            console.log('Backup tutor selection details:');
            console.log('- Selected value:', selectedFullName);
            console.log('- Selected option:', selectedOption);
            console.log('- Option dataset:', selectedOption.dataset);
            console.log('- Username from dataset:', selectedOption.dataset.username);
            
            if (selectedFullName) {
                backupTutor = {
                    fullName: selectedFullName,
                    username: selectedOption.dataset.username // This is the actual username
                };
                console.log('Backup tutor selected:', backupTutor);
            } else {
                backupTutor = null;
                console.log('Backup tutor cleared');
            }
        });
    }

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
            if (backupTutorSelect) {
                backupTutorSelect.value = "";
            }
            
            console.log('Loading tutors for class ID:', currentClassId);
            
            // Load tutors using the new API that properly separates main and backup tutors
            loadClassTutors(currentClassId);
            
            if (modal) {
                modal.classList.remove("hidden");
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
                // Load main tutors
                tutors = data.main_tutors.map(tutor => ({
                    fullName: tutor.full_name,
                    username: tutor.username
                }));
                
                // Load backup tutor (if any)
                if (data.backup_tutors && data.backup_tutors.length > 0) {
                    const firstBackup = data.backup_tutors[0];
                    backupTutor = {
                        fullName: firstBackup.full_name,
                        username: firstBackup.username
                    };
                    console.log('Loaded backup tutor:', backupTutor);
                }
                
                console.log('Loaded main tutors:', tutors);
                console.log('Loaded backup tutor:', backupTutor);
                
                // Render the tutors and fetch available tutors for dropdowns
                renderTutors();
                fetchAvailableTutors();
                
                // Set backup tutor dropdown after a short delay to ensure options are loaded
                if (backupTutor) {
                    setTimeout(() => {
                        if (backupTutorSelect) {
                            const backupOptions = Array.from(backupTutorSelect.options);
                            const matchingOption = backupOptions.find(option => 
                                option.value === backupTutor.fullName ||
                                option.dataset.username === backupTutor.username
                            );
                            if (matchingOption) {
                                backupTutorSelect.value = matchingOption.value;
                                console.log('Set backup tutor dropdown to:', matchingOption.value);
                            }
                        }
                    }, 200);
                }
            } else {
                console.error('Failed to load class tutors:', data.message);
                // Fallback to empty state
                tutors = [];
                backupTutor = null;
                renderTutors();
                fetchAvailableTutors();
            }
        })
        .catch(error => {
            console.error('Error loading class tutors:', error);
            // Fallback to empty state
            tutors = [];
            backupTutor = null;
            renderTutors();
            fetchAvailableTutors();
        });
    }

    // Close modal functionality
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            if (modal) modal.classList.add("hidden");
        });
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
            if (modal) modal.classList.add("hidden");
        });
    }
    
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) modal.classList.add("hidden");
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

            console.log('Debug - Original tutors array:', tutors);
            
            // Convert tutor objects to usernames for the API
            const tutorUsernames = tutors.map(tutor => {
                if (typeof tutor === 'object' && tutor.username) {
                    return tutor.username;
                }
                // Fallback for any string entries (shouldn't happen with new structure)
                return tutor;
            }).filter(username => {
                // Make sure it's a string and not empty
                return username && typeof username === 'string' && username.trim() !== '';
            });

            // Prepare backup tutor for saving (if database field exists)
            const backupTutorData = backupTutor ? {
                username: backupTutor.username,
                full_name: backupTutor.fullName
            } : null;
            
            console.log('Saving assignments - Main tutors:', tutorUsernames, 'Backup tutor:', backupTutorData);
            console.log('Class ID:', currentClassId);

            // Send tutor assignments to server
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
                    backup_tutor: backupTutorData // Include backup tutor in the save request
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    alert('Tutor assignments saved successfully!');
                    if (modal) modal.classList.add("hidden");
                    location.reload(); // Refresh to show updated assignments
                } else {
                    alert('Error saving assignments: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Full error:', error);
                alert('Error saving assignments: ' + error.message);
            })
            .finally(() => {
                console.log('Save operation completed');
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