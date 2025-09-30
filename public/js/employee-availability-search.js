// Employee Availability Real-time Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Constants from server
    const totalResults = window.tutorTotalResults || 0;
    
    // Real-time search functionality
    const searchInput = document.getElementById('tutorSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const tutorRows = document.querySelectorAll('.tutor-row');
    const noResultsMessage = document.getElementById('noSearchResults');
    const noResultsRow = document.getElementById('noResultsRow');
    const resultCount = document.getElementById('resultCount');
    const searchResults = document.getElementById('searchResults');
    const tutorTableBody = document.getElementById('tutorTableBody');
    const paginationSection = document.getElementById('paginationSection');

    let searchTimeout;
    let isSearchActive = false;

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
            if (searchSpinner) {
                searchSpinner.classList.remove('hidden');
                clearSearchBtn.classList.add('hidden');
            }

            // Debounce search
            searchTimeout = setTimeout(() => {
                // Always use AJAX search to ensure we get all tutors from server
                performTutorSearch();
                
                if (searchSpinner) {
                    searchSpinner.classList.add('hidden');
                }
                if (query.length > 0) {
                    clearSearchBtn.classList.remove('hidden');
                }
            }, 300);
        });

        // Clear search
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.classList.add('hidden');
            // Always use AJAX search when clearing to show all tutors
            performTutorSearch();
        });
    }

    // Global AJAX search function
    window.performTutorSearch = function() {
        const searchValue = searchInput ? searchInput.value.trim() : '';
        const statusValue = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : '';
        const timeSlotValue = document.getElementById('filterTimeSlot') ? document.getElementById('filterTimeSlot').value : '';
        const dayValue = document.getElementById('filterDay') ? document.getElementById('filterDay').value : '';
        
        // Build query parameters
        const params = new URLSearchParams();
        // Always include search parameter, even when empty, to ensure server knows to clear search
        params.append('search', searchValue);
        if (statusValue) params.append('status', statusValue);
        if (timeSlotValue) params.append('time_slot', timeSlotValue);
        if (dayValue) params.append('day', dayValue);
        
        // Reset to page 1 when filtering (unless it's a pagination request)
        const currentUrl = new URL(window.location);
        const currentPage = currentUrl.searchParams.get('page');
        if (currentPage && currentPage !== '1') {
            // If we're not on page 1, reset to page 1 for new filter results
            params.set('page', '1');
        }
        
        // Show loading state
        if (searchSpinner) {
            searchSpinner.classList.remove('hidden');
        }
        
        fetch(`/api/search-tutors?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table body
                if (tutorTableBody) {
                    tutorTableBody.innerHTML = data.html;
                }
                
                // Update pagination
                if (paginationSection) {
                    paginationSection.innerHTML = data.pagination;
                }
                
                // Update result count
                if (resultCount) {
                    resultCount.textContent = data.total;
                }
                
                
                // Update URL to reflect current filters and page
                const newUrl = new URL(window.location);
                newUrl.search = params.toString();
                window.history.pushState({}, '', newUrl);
                
                // Handle pagination events
                handlePaginationEvents();
                
                isSearchActive = true;
            } else {
                console.error('Search failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        })
        .finally(() => {
            if (searchSpinner) {
                searchSpinner.classList.add('hidden');
            }
        });
    };
    
    // Handle pagination events
    function handlePaginationEvents() {
        const paginationLinks = document.querySelectorAll('#paginationSection a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = this.getAttribute('data-page');
                
                // Update URL without reloading
                const currentUrl = new URL(window.location);
                if (page) {
                    currentUrl.searchParams.set('page', page);
                } else {
                    currentUrl.searchParams.delete('page');
                }
                window.history.pushState({}, '', currentUrl);
                
                // Perform search with new page
                performTutorSearchWithPage(page);
            });
        });
    }
    
    // Perform search with specific page
    function performTutorSearchWithPage(page) {
        const searchValue = searchInput ? searchInput.value.trim() : '';
        const statusValue = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : '';
        const timeSlotValue = document.getElementById('filterTimeSlot') ? document.getElementById('filterTimeSlot').value : '';
        const dayValue = document.getElementById('filterDay') ? document.getElementById('filterDay').value : '';
        
        // Build query parameters
        const params = new URLSearchParams();
        // Always include search parameter, even when empty, to ensure server knows to clear search
        params.append('search', searchValue);
        if (statusValue) params.append('status', statusValue);
        if (timeSlotValue) params.append('time_slot', timeSlotValue);
        if (dayValue) params.append('day', dayValue);
        if (page) params.append('page', page);
        
        // Show loading state
        if (searchSpinner) {
            searchSpinner.classList.remove('hidden');
        }
        
        fetch(`/api/search-tutors?${params.toString()}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update table body
                if (tutorTableBody) {
                    tutorTableBody.innerHTML = data.html;
                }
                
                // Update pagination
                if (paginationSection) {
                    paginationSection.innerHTML = data.pagination;
                }
                
                // Update result count
                if (resultCount) {
                    resultCount.textContent = data.total;
                }
                
                
                // Handle pagination events
                handlePaginationEvents();
                
                isSearchActive = true;
            } else {
                console.error('Search failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        })
        .finally(() => {
            if (searchSpinner) {
                searchSpinner.classList.add('hidden');
            }
        });
    }

    function performSearch(query) {
        let visibleCount = 0;
        let hasOriginalData = tutorRows.length > 0;
        const paginationSection = document.getElementById('paginationSection');

        tutorRows.forEach(row => {
            const searchableText = row.dataset.searchable || '';
            
            if (query === '' || searchableText.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update result count
        if (resultCount) {
            resultCount.textContent = visibleCount;
        }

        if (searchResults) {
            if (query === '') {
                searchResults.textContent = `Showing ${totalResults} results`;
            } else {
                searchResults.textContent = `Found ${visibleCount} results for "${query}"`;
            }
        }

        // Handle pagination visibility
        if (paginationSection) {
            if (query === '') {
                // Show original pagination when not searching
                paginationSection.style.display = '';
            } else {
                // Hide pagination when searching (since we're filtering client-side)
                paginationSection.style.display = 'none';
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
            // No original data, show the original "no data" message
            if (noResultsMessage) noResultsMessage.classList.add('hidden');
            if (noResultsRow) noResultsRow.style.display = '';
        }

        // Add highlighting
        highlightSearchResults(query);
    }

    // Highlight search results
    function highlightSearchResults(query) {
        // Remove existing highlights
        tutorRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach(cell => {
                if (!cell.querySelector('button')) { // Skip action column
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
        tutorRows.forEach(row => {
            if (row.style.display !== 'none') {
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    if (!cell.querySelector('button')) { // Skip action column
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
                });
            }
        });
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
});

// Tutor action functions
function editTutor(tutorId) {
    // You can implement edit functionality here
}

function toggleTutorStatus(tutorId, newStatus) {
    if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this tutor?`)) {
        // Make AJAX request to toggle status
        fetch(`/tutors/${tutorId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating tutor status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating tutor status');
        });
    }
}