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
            
            // Refresh dropdown to show newly available tutors
            fetchAvailableTutors();
        }
    }

    // Make removeTutor globally available
    window.removeTutor = function(index) {
        tutors.splice(index, 1);
        renderTutors();
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
            }
            this.value = "";
        });
    }

    // Backup tutor selection handler
    if (backupTutorSelect) {
        backupTutorSelect.addEventListener("change", function() {
            const selectedTutor = this.value;
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedTutor) {
                backupTutor = {
                    fullName: selectedTutor,
                    username: selectedOption.dataset.username
                };
                console.log('Backup tutor selected:', backupTutor);
            } else {
                backupTutor = null;
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
            
            // Load currently assigned tutors for this class (full names from data attribute)
            const assignedTutorsString = btn.dataset.assignedTutors;
            if (assignedTutorsString) {
                const fullNames = assignedTutorsString.split(',').filter(t => t.trim() !== '');
                // We'll fetch the tutor mapping to get usernames for existing assignments
                fetchTutorMapping(fullNames);
            } else {
                renderTutors(); // This will also call fetchAvailableTutors()
            }
            
            if (modal) {
                modal.classList.remove("hidden");
                console.log('Modal should be visible now');
            }
        }
    });

    // Fetch tutor mapping to convert full names to username objects
    function fetchTutorMapping(fullNames) {
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
                // Create a mapping from full names to tutor objects
                const tutorMap = {};
                data.tutors.forEach(tutor => {
                    tutorMap[tutor.full_name] = tutor;
                });

                // Convert full names to tutor objects
                tutors = fullNames.map(fullName => {
                    const tutor = tutorMap[fullName];
                    if (tutor) {
                        return {
                            fullName: tutor.full_name,
                            username: tutor.username
                        };
                    }
                    return fullName; // Fallback to string if not found
                }).filter(Boolean);

                renderTutors();
            } else {
                console.error('Failed to fetch tutors for mapping:', data.message);
                // Fallback: just use the full names as strings
                tutors = fullNames;
                renderTutors();
            }
        })
        .catch(error => {
            console.error('Error fetching tutor mapping:', error);
            // Fallback: just use the full names as strings
            tutors = fullNames;
            renderTutors();
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

            // Convert tutor objects to usernames for the API
            const tutorUsernames = tutors.map(tutor => {
                if (typeof tutor === 'object' && tutor.username) {
                    return tutor.username;
                }
                // Fallback for any string entries (shouldn't happen with new structure)
                return tutor;
            }).filter(username => username && username.trim() !== '');

            // Prepare backup tutor for saving (if database field exists)
            const backupTutorData = backupTutor ? {
                username: backupTutor.username,
                full_name: backupTutor.fullName
            } : null;
            
            console.log('Saving assignments - Main tutors:', tutorUsernames, 'Backup tutor:', backupTutorData);

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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Tutor assignments saved successfully!');
                    if (modal) modal.classList.add("hidden");
                    location.reload(); // Refresh to show updated assignments
                } else {
                    alert('Error saving assignments: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving assignments. Please try again.');
            })
            .finally(() => {
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        });
    }
});