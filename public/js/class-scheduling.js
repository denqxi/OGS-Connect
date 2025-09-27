/**
 * Class Scheduling JavaScript
 * Handles search, filter, and table functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeClassScheduling();
});

function initializeClassScheduling() {
    // Get DOM elements
    const searchInput = document.getElementById('realTimeSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const tableRows = document.querySelectorAll('.table-row');
    const noResultsMessage = document.getElementById('noSearchResults');
    const noResultsRow = document.getElementById('noResultsRow');
    const resultCount = document.getElementById('resultCount');
    const paginationSection = document.getElementById('paginationSection');
    const filterType = document.getElementById('filterType');
    const dateFilter = document.getElementById('dateFilter');
    const dayFilter = document.getElementById('dayFilter');

    let searchTimeout;

    // Initialize filter functionality
    if (filterType && dateFilter && dayFilter) {
        filterType.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Hide both filters first
            dateFilter.classList.add('hidden');
            dayFilter.classList.add('hidden');
            
            // Clear values
            dateFilter.value = '';
            dayFilter.value = '';
            
            // Show the selected filter
            if (selectedType === 'date') {
                dateFilter.classList.remove('hidden');
            } else if (selectedType === 'day') {
                dayFilter.classList.remove('hidden');
            }
        });
    }

    // Initialize search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            // Show/hide clear button
            if (query.length > 0) {
                clearSearchBtn.classList.remove('hidden');
            } else {
                clearSearchBtn.classList.add('hidden');
            }

            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Show spinner
            searchSpinner.classList.remove('hidden');
            clearSearchBtn.classList.add('hidden');

            // Debounce search
            searchTimeout = setTimeout(() => {
                performSearch(query);
                searchSpinner.classList.add('hidden');
                if (query.length > 0) {
                    clearSearchBtn.classList.remove('hidden');
                }
            }, 300);
        });

        // Clear search functionality
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearSearchBtn.classList.add('hidden');
                performSearch('');
            });
        }
    }

    // Search function
    function performSearch(query) {
        let visibleCount = 0;
        let hasOriginalData = false;

        tableRows.forEach(row => {
            const searchableText = row.dataset.searchable || '';
            
            if (searchableText) {
                hasOriginalData = true;
                
                const isVisible = query === '' || searchableText.includes(query);
                
                if (isVisible) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // Update result count
        if (resultCount) {
            resultCount.textContent = visibleCount;
        }

        // Update pagination display
        if (query === '') {
            // Show pagination when no search
            if (paginationSection) {
                paginationSection.style.display = '';
            }
        } else {
            // Hide pagination during search and show search results
            if (paginationSection) {
                const searchResultsDiv = paginationSection.querySelector('.flex') ? paginationSection : null;
                if (searchResultsDiv) {
                    searchResultsDiv.innerHTML = `
                        <div class="text-sm text-gray-500">
                            Found <span id="searchResultCount">${visibleCount}</span> results for "${query}"
                        </div>
                    `;
                }
            }
        }

        // Show/hide no results message
        if (hasOriginalData) {
            if (visibleCount === 0) {
                if (noResultsMessage) noResultsMessage.classList.remove('hidden');
                if (noResultsRow) noResultsRow.style.display = 'none';
            } else {
                if (noResultsMessage) noResultsMessage.classList.add('hidden');
                if (noResultsRow) noResultsRow.style.display = 'none';
            }
        } else {
            if (noResultsMessage) noResultsMessage.classList.add('hidden');
            if (noResultsRow) noResultsRow.style.display = '';
        }

        // Add search highlighting
        highlightSearchResults(query);
    }

    // Highlight search results
    function highlightSearchResults(query) {
        // Remove existing highlights
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                if (!cell.querySelector('a')) {
                    const highlights = cell.querySelectorAll('.search-highlight');
                    highlights.forEach(highlight => {
                        const parent = highlight.parentNode;
                        parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
                        parent.normalize();
                    });
                }
            });
        });

        if (!query) return;

        // Add new highlights for visible rows
        tableRows.forEach(row => {
            if (row.style.display !== 'none') {
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    if (!cell.querySelector('a')) {
                        highlightTextInCell(cell, query);
                    }
                });
            }
        });
    }

    // Highlight text in cell
    function highlightTextInCell(cell, query) {
        const walker = document.createTreeWalker(
            cell,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        const textNodes = [];
        let node;
        
        while (node = walker.nextNode()) {
            textNodes.push(node);
        }

        textNodes.forEach(textNode => {
            const text = textNode.textContent;
            const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
            
            if (regex.test(text)) {
                const highlightedHTML = text.replace(regex, '<span class="search-highlight bg-yellow-200 font-semibold rounded px-1">$1</span>');
                
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = highlightedHTML;
                
                const parent = textNode.parentNode;
                while (tempDiv.firstChild) {
                    parent.insertBefore(tempDiv.firstChild, textNode);
                }
                parent.removeChild(textNode);
            }
        });
    }

    // Escape regex special characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Initialize search from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('search');
    if (initialSearch && searchInput) {
        searchInput.value = initialSearch;
        performSearch(initialSearch.toLowerCase());
        if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
    }
}

// Add these functions to your class-scheduling.js file

function autoAssignSpecificSchedule(date, day) {
    const dayName = getDayName(day);
    
    if (!confirm(`This will automatically assign tutors for ${dayName}, ${date}. Continue?`)) {
        return;
    }

    // Show loading state
    const button = event.target.closest('button');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';
    button.disabled = true;

    // Make the request
    fetch(`/schedules/auto-assign/${date}/${day}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show detailed success message
            let message = data.message;
            if (data.data && data.data.summary) {
                message += `\n\nSummary:`;
                message += `\n• Classes processed: ${data.data.summary.total_classes_processed}`;
                message += `\n• Tutors assigned: ${data.data.summary.total_tutors_assigned}`;
                message += `\n• Classes with new assignments: ${data.data.summary.classes_with_assignments}`;
            }
            
            alert(message);
            location.reload(); // Refresh to show new assignments
        } else {
            alert('Auto-assignment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Auto-assignment failed. Please try again.');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}

function getDayName(day) {
    const dayMap = {
        'mon': 'Monday',
        'tue': 'Tuesday', 
        'wed': 'Wednesday',
        'thur': 'Thursday',
        'fri': 'Friday',
        'sat': 'Saturday',
        'sun': 'Sunday'
    };
    return dayMap[day] || day;
}

// Keep your existing autoAssignTutors function for bulk assignment
function autoAssignTutors() {
    if (!confirm('This will automatically assign tutors to all unassigned/partially assigned schedules. Continue?')) {
        return;
    }

    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Assigning...</span>';
    button.disabled = true;

    // Get current filter values
    const date = document.querySelector('select[name="date"]').value;
    const day = document.querySelector('select[name="day"]').value;

    fetch('/schedules/auto-assign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ date, day })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload(); // Refresh to show new assignments
        } else {
            alert('Auto-assignment failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Auto-assignment failed. Please try again.');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

/**
 * Auto-assign tutors for a specific day
 */
function autoAssignForThisDay(date) {
    if (!confirm('Are you sure you want to auto-assign tutors for all classes on ' + date + '?')) {
        return;
    }

    const button = event.target;
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Assigning...';
    button.disabled = true;

    // Make AJAX request to auto-assign for this specific date
    fetch(`/schedules/auto-assign/${date}`, {
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
            alert(`Auto-assignment completed! ${data.assigned} tutors assigned to classes.`);
            location.reload(); // Refresh to show new assignments
        } else {
            alert('Auto-assignment failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Auto-assignment failed. Please try again.');
    })
    .finally(() => {
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}