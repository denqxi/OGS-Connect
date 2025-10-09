<!-- Page Title -->
<div class="p-4 border-b border-gray-200">
    <h2 class="text-xl font-bold text-[#65DB7F]">Supervisors</h2>
</div>

<!-- Search Filters -->
<div class="p-6 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-700">Search Filters</h3>
    </div>
    
    <form method="GET" action="{{ route('employees.index') }}" id="supervisorFilterForm">
        <input type="hidden" name="tab" value="supervisors">
        
        <div class="flex justify-between items-center space-x-4">
            <!-- Left side -->
            <div class="flex items-center space-x-4 flex-1 max-w-lg">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search full name, email, phone..."
                           id="supervisorSearch"
                           class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md text-sm 
                                  focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                                  focus:ring-0 focus:shadow-xl">
                    <button type="button" id="clearSupervisorSearch" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 {{ request('search') ? '' : 'hidden' }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <select name="status" id="filterSupervisorStatus" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleSupervisorFilterChange('status')">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <select name="account" id="filterSupervisorAccount" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white" onchange="handleSupervisorFilterChange('account')">
                    <option value="">All Accounts</option>
                    <option value="GLS" {{ request('account') == 'GLS' ? 'selected' : '' }}>GLS</option>
                    <option value="Tutlo" {{ request('account') == 'Tutlo' ? 'selected' : '' }}>Tutlo</option>
                    <option value="Babilala" {{ request('account') == 'Babilala' ? 'selected' : '' }}>Babilala</option>
                    <option value="Talk915" {{ request('account') == 'Talk915' ? 'selected' : '' }}>Talk915</option>
                </select>
            </div>

            @if(request()->hasAny(['search', 'status', 'account']))
                <a href="{{ route('employees.index', ['tab' => 'supervisors']) }}" 
                   class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Supervisor Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned Account</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200" id="supervisorTableBody">
            @forelse($supervisors ?? [] as $supervisor)
            <tr class="hover:bg-gray-50 supervisor-row" data-searchable="{{ strtolower(($supervisor->full_name ?? '') . ' ' . ($supervisor->semail ?? '') . ' ' . ($supervisor->sphone ?? '')) }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $supervisor->full_name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 underline">
                    <a href="mailto:{{ $supervisor->semail ?? '' }}">{{ $supervisor->semail ?? 'N/A' }}</a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($supervisor->assigned_account)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $supervisor->assigned_account }}
                        </span>
                    @else
                        <span class="text-gray-400">Unassigned</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                        {{ $supervisor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($supervisor->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button onclick="openEmployeeModal('supervisor', '{{ $supervisor->supID }}')" 
                                class="w-8 h-8 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 inline-flex items-center justify-center transition-colors"
                                title="View Details">
                            <i class="fas fa-search text-xs"></i>
                        </button>
                        @if($supervisor->status === 'active')
                            <button class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition-colors text-xs font-medium"
                                    onclick="toggleSupervisorStatus('{{ $supervisor->supID }}', 'inactive')" title="Deactivate">
                                Deactivate
                            </button>
                        @else
                            <button class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 transition-colors text-xs font-medium"
                                    onclick="toggleSupervisorStatus('{{ $supervisor->supID }}', 'active')" title="Activate">
                                Activate
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr id="noSupervisorResultsRow">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    <i class="fas fa-user-tie text-4xl mb-4 opacity-50"></i>
                    <p class="text-lg font-medium">No supervisors found</p>
                    <p class="text-sm">Try adjusting your search criteria</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if(isset($supervisors))
    @include('emp_management.partials.compact-pagination', ['data' => $supervisors, 'tab' => 'supervisors'])
@else
<div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between h-16 w-full" id="paginationSection">
    <div class="text-sm text-gray-500">
        Showing 0 results
    </div>
    <div class="flex items-center justify-center space-x-2 w-[300px]">
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="w-8 h-8 bg-slate-700 text-white rounded text-sm flex items-center justify-center">1</button>
        <button class="w-8 h-8 border border-gray-300 rounded text-sm text-gray-500 hover:bg-gray-50 flex items-center justify-center" disabled>
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>
@endif

<script>
    window.supervisorTotalResults = @json(isset($supervisors) && method_exists($supervisors, 'total') ? $supervisors->total() : 0);
    
    function handleSupervisorFilterChange(changed) {
        // Submit the form to apply filters
        document.getElementById('supervisorFilterForm').submit();
    }

    // Handle search input - submit form on Enter or when user stops typing
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('supervisorSearch');
        const clearSearchBtn = document.getElementById('clearSupervisorSearch');
        const form = document.getElementById('supervisorFilterForm');
        let searchTimeout;

        if (searchInput) {
            // Handle search input with debounce
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Show/hide clear button
                if (query.length > 0) {
                    if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
                } else {
                    if (clearSearchBtn) clearSearchBtn.classList.add('hidden');
                }

                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Debounce search - auto-submit form after user stops typing for 800ms
                searchTimeout = setTimeout(() => {
                    form.submit();
                }, 800);
            });

            // Handle Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    form.submit();
                }
            });

            // Clear search
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    clearSearchBtn.classList.add('hidden');
                    form.submit();
                });
            }
        }
    });

    // Toggle supervisor status (active/inactive)
    function toggleSupervisorStatus(supervisorId, newStatus) {
        if (!supervisorId) {
            alert('Error: Supervisor ID not found');
            return;
        }

        // Show confirmation dialog
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        if (!confirm(`Are you sure you want to ${action} this supervisor?`)) {
            return;
        }

        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i>';

        // Make AJAX request
        fetch(`/supervisors/${supervisorId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                alert(data.message);
                
                // Reload the page to reflect changes
                window.location.reload();
            } else {
                // Show error message
                alert(data.message || 'Failed to update supervisor status');
                
                // Restore button state
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating supervisor status');
            
            // Restore button state
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
</script>
