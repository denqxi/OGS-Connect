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

    let searchTimeout;

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
                performSearch(query);
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
            performSearch('');
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