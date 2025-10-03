// Class Scheduling Real-time Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Set upload route and CSRF token for JavaScript
    window.uploadRoute = window.uploadRoute || '';
    window.csrfToken = window.csrfToken || '';
    
    // Real-time search functionality
    let searchTimeout;
    let isSearchActive = false; // Track if we're in search mode
    
    const searchInput = document.getElementById('realTimeSearch');
    const searchSpinner = document.getElementById('searchSpinner');
    const clearButton = document.getElementById('clearSearch');
    const tableBody = document.getElementById('tableBody');
    function getPaginationSection() {
        return document.getElementById('paginationContainer');
    }
    
    // Check if we started with a search query
    if (searchInput && searchInput.value.trim()) {
        isSearchActive = true;
    }
    
    // Also check for any other active filters
    const form = document.getElementById('filterForm');
    if (form) {
        const formData = new FormData(form);
        for (const [key, value] of formData.entries()) {
            if (key !== 'tab' && value && value.trim() !== '') {
                isSearchActive = true;
                break;
            }
        }
    }
    
    // Disable AJAX pagination to use normal form submission
    window.alwaysUseAjaxPagination = false;
    
    if (searchInput) {
        // Real-time search on input
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            // Show/hide clear button
            if (query.length > 0) {
                clearButton.classList.remove('hidden');
                isSearchActive = true;
            } else {
                clearButton.classList.add('hidden');
                isSearchActive = false;
            }
            
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Debounce search - use normal form submission instead of AJAX
            searchTimeout = setTimeout(() => {
                // Submit the form normally instead of using AJAX
                const form = document.getElementById('filterForm');
                if (form) {
                    form.submit();
                }
            }, 500);
        });
        
        // Clear search functionality
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                searchInput.value = '';
                clearButton.classList.add('hidden');
                isSearchActive = false;
                
                // Hide the no search results message when clearing search
                const noResultsMessage = document.getElementById('noSearchResults');
                if (noResultsMessage) {
                    noResultsMessage.classList.add('hidden');
                }
                
                // Submit the form normally instead of using AJAX
                const form = document.getElementById('filterForm');
                if (form) {
                    form.submit();
                }
            });
        }
    }
    
    // Handle filter dropdown changes to maintain search state
    if (form) {
        const filterSelects = form.querySelectorAll('select');
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // If we have any search or filters active, maintain search mode
                const hasFilters = Array.from(filterSelects).some(s => s.value) || 
                                 (searchInput && searchInput.value.trim());
                isSearchActive = hasFilters;
            });
        });
    }
    
    // Disable AJAX pagination - let normal links work
    // document.addEventListener('click', function(e) {
    //     // Check if this is any link that contains 'page=' parameter
    //     let targetLink = e.target;
    //     
    //     // Walk up the DOM tree to find a link
    //     while (targetLink && targetLink !== document) {
    //         if (targetLink.tagName === 'A' && targetLink.href && targetLink.href.includes('page=')) {
    //             break;
    //         }
    //         targetLink = targetLink.parentNode;
    //     }
    //     
    //     // If we found a pagination link, always handle it with AJAX
    //     if (targetLink && targetLink.href && targetLink.href.includes('page=')) {
    //         e.preventDefault();
    //         e.stopPropagation();
    //         
    //         const url = new URL(targetLink.href);
    //         const page = url.searchParams.get('page') || 1;
    //         
    //         // Perform search with the current search parameters and the new page
    //         const currentSearch = searchInput ? searchInput.value.trim() : '';
    //         window.performRealTimeSearch(currentSearch, page);
    //         return false;
    //     }
    // }, true); // Use capture phase to catch the event early
    
    // Disable AJAX pagination container event listener
    // const paginationContainer = document.getElementById('paginationContainer');
    // if (paginationContainer) {
    //     paginationContainer.addEventListener('click', function(e) {
    //         if (e.target.tagName === 'A' && e.target.href && e.target.href.includes('page=')) {
    //             e.preventDefault();
    //             e.stopPropagation();
    //             
    //             const url = new URL(e.target.href);
    //             const page = url.searchParams.get('page') || 1;
    //             
    //             // Perform search with the current search parameters and the new page
    //             const currentSearch = searchInput ? searchInput.value.trim() : '';
    //             window.performRealTimeSearch(currentSearch, page);
    //             return false;
    //         }
    //     });
    // } else {
    //     console.log('Pagination container not found during initialization');
    // }
    
    // Disable AJAX pagination event delegation
    // document.addEventListener('click', function(e) {
    //     // Check if the clicked element is inside the pagination container
    //     const paginationContainer = document.getElementById('paginationContainer');
    //     if (paginationContainer && paginationContainer.contains(e.target)) {
    //         if (e.target.tagName === 'A' && e.target.href && e.target.href.includes('page=')) {
    //             e.preventDefault();
    //             e.stopPropagation();
    //             
    //             const url = new URL(e.target.href);
    //             const page = url.searchParams.get('page') || 1;
    //             
    //             // Perform search with the current search parameters and the new page
    //             const currentSearch = searchInput ? searchInput.value.trim() : '';
    //             window.performRealTimeSearch(currentSearch, page);
    //             return false;
    //         }
    //     }
    // });
    
    window.performRealTimeSearch = function(query, page = 1) {
        const searchRoute = window.searchSchedulesRoute || '/api/search-schedules';
        const url = new URL(searchRoute, window.location.origin);
        
        // Add search parameter
        if (query) {
            url.searchParams.set('search', query);
        }
        
        // Add page parameter
        if (page > 1) {
            url.searchParams.set('page', page);
        }
        
        // Add other filters from the form
        const form = document.getElementById('filterForm');
        if (form) {
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (key !== 'search' && value) {
                    url.searchParams.set(key, value);
                }
            }
        }
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Check if there are no results
                const noResultsMessage = document.getElementById('noSearchResults');
                const hasResults = data.total > 0;
                
                if (hasResults) {
                    // Update table body with results
                    if (tableBody) {
                        tableBody.innerHTML = data.html;
                    }
                    
                    // Hide the "no search results" message
                    if (noResultsMessage) {
                        noResultsMessage.classList.add('hidden');
                    }
                    
                    // Update pagination if exists
                    const pagSection = getPaginationSection();
                    if (pagSection && data.pagination) {
                        pagSection.innerHTML = data.pagination;
                    }
                } else {
                    // Clear table body and show "no search results" message
                    if (tableBody) {
                        tableBody.innerHTML = '';
                    }
                    
                    // Show the "no search results" message
                    if (noResultsMessage) {
                        noResultsMessage.classList.remove('hidden');
                    }
                    
                    // Clear pagination
                    const pagSection = getPaginationSection();
                    if (pagSection) {
                        pagSection.innerHTML = '<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between"><div class="text-sm text-gray-500">No results found</div></div>';
                    }
                }
                
                // Update URL without page reload
                const newUrl = new URL(window.location);
                if (query) {
                    newUrl.searchParams.set('search', query);
                } else {
                    newUrl.searchParams.delete('search');
                }
                
                // Add page to URL if not page 1
                if (page > 1 && hasResults) {
                    newUrl.searchParams.set('page', page);
                } else {
                    newUrl.searchParams.delete('page');
                }
                
                window.history.pushState({}, '', newUrl);
            } else {
                console.error('Search failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        })
        .finally(() => {
            // Show clear button if there's text
            if (query.length > 0 && clearButton) {
                clearButton.classList.remove('hidden');
            }
        });
    }
});