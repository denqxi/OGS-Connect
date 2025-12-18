<div class="px-4 md:px-6 py-4 border-b border-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-medium text-gray-500">Search Filters</h3>
    </div>
    <form method="GET" action="{{ route('hiring_onboarding.index') }}" id="searchForm" class="flex justify-between items-center space-x-4">
        <input type="hidden" name="tab" value="logs">
        <!-- Left side -->
        <div class="flex items-center space-x-4 flex-1 max-w-lg">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" id="searchIcon"></i>
                <i class="fas fa-spinner fa-spin absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hidden" id="loadingIcon"></i>
                <input type="text" name="search" id="searchInput" placeholder="search..." value="{{ request('search') }}"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md text-sm 
                focus:outline-none focus:border-[0.5px] focus:border-[#2A5382] 
                focus:ring-0 focus:shadow-xl">
            </div>
            <select name="status" id="statusSelect" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">
                <option value="">Select Status</option>
                @php
                    $statusOptions = collect($logs->items())->pluck('status')->filter()->unique()->sort();
                @endphp
                @foreach($statusOptions as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4 text-sm text-gray-500">{{ optional($log['timestamp'])->format('M d, Y h:i A') ?? 'N/A' }}</td>
                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $log['name'] }}</td>
                    <td class="px-4 py-4 text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $log['status']) }}</td>
                    <td class="px-4 py-4 text-sm text-gray-600">{{ $log['source'] }}</td>
                    <td class="px-4 py-4 text-sm">
                        <button onclick="openLogDetailModal({{ json_encode($log) }})" 
                                class="p-2 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No log entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-4 py-3 bg-white border-t border-gray-200">
    {{ $logs->appends(request()->except('page'))->links() }}
</div>

<!-- Log Detail Modal -->
<div id="logDetailModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Log Details</h3>
            <button onclick="closeLogDetailModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mt-4 space-y-4 max-h-96 overflow-y-auto">
            <!-- Basic Info -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Name</label>
                    <p id="modalName" class="mt-1 text-sm text-gray-900"></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Source</label>
                    <p id="modalSource" class="mt-1 text-sm text-gray-900"></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Status</label>
                    <p id="modalStatus" class="mt-1 text-sm text-gray-900"></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Date</label>
                    <p id="modalDate" class="mt-1 text-sm text-gray-900"></p>
                </div>
            </div>

            <!-- Additional Details Container -->
            <div id="additionalDetails" class="border-t pt-4"></div>
        </div>

        <div class="flex justify-end mt-6 pt-4 border-t">
            <button onclick="closeLogDetailModal()" 
                    class="px-4 py-2 bg-gray-500 text-white text-sm rounded hover:bg-gray-600">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openLogDetailModal(log) {
    const modal = document.getElementById('logDetailModal');
    const data = log.data;
    
    // Set basic info
    document.getElementById('modalName').textContent = log.name;
    document.getElementById('modalSource').textContent = log.source;
    document.getElementById('modalStatus').textContent = log.status.replace(/_/g, ' ');
    document.getElementById('modalDate').textContent = log.timestamp ? new Date(log.timestamp).toLocaleString() : 'N/A';
    
    // Build additional details based on source
    const detailsContainer = document.getElementById('additionalDetails');
    let detailsHtml = '<div class="grid grid-cols-2 gap-4">';
    
    if (data && data.applicant) {
        const applicant = data.applicant;
        
        // Contact Info
        if (applicant.email) {
            detailsHtml += `
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Email</label>
                    <p class="mt-1 text-sm text-gray-900">${applicant.email}</p>
                </div>`;
        }
        if (applicant.contact_number) {
            detailsHtml += `
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Contact Number</label>
                    <p class="mt-1 text-sm text-gray-900">${applicant.contact_number}</p>
                </div>`;
        }
        if (applicant.address) {
            detailsHtml += `
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-500 uppercase">Address</label>
                    <p class="mt-1 text-sm text-gray-900">${applicant.address}</p>
                </div>`;
        }
        
        // Qualification
        if (applicant.qualification) {
            if (applicant.qualification.education) {
                detailsHtml += `
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Education</label>
                        <p class="mt-1 text-sm text-gray-900">${applicant.qualification.education.replace(/_/g, ' ').toUpperCase()}</p>
                    </div>`;
            }
            if (applicant.qualification.esl_experience) {
                detailsHtml += `
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">ESL Experience</label>
                        <p class="mt-1 text-sm text-gray-900">${applicant.qualification.esl_experience.replace(/_/g, ' ')}</p>
                    </div>`;
            }
        }
        
        // Work Preference
        if (applicant.work_preference) {
            const wp = applicant.work_preference;
            if (wp.start_time && wp.end_time) {
                detailsHtml += `
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Availability</label>
                        <p class="mt-1 text-sm text-gray-900">${wp.start_time} - ${wp.end_time}</p>
                    </div>`;
            }
            if (wp.days_available) {
                const days = typeof wp.days_available === 'string' ? JSON.parse(wp.days_available) : wp.days_available;
                detailsHtml += `
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase">Days Available</label>
                        <p class="mt-1 text-sm text-gray-900">${Array.isArray(days) ? days.join(', ') : days}</p>
                    </div>`;
            }
        }
        
        // Interview Time
        if (applicant.interview_time) {
            detailsHtml += `
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase">Interview Time</label>
                    <p class="mt-1 text-sm text-gray-900">${new Date(applicant.interview_time).toLocaleString()}</p>
                </div>`;
        }
    }
    
    // Account info for Demo/Onboarding
    if (data && data.account && data.account.account_name) {
        detailsHtml += `
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Assigned Account</label>
                <p class="mt-1 text-sm text-gray-900">${data.account.account_name}</p>
            </div>`;
    }
    
    // Screening/Onboarding Date
    if (data && data.screening_date_time) {
        detailsHtml += `
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Screening Date</label>
                <p class="mt-1 text-sm text-gray-900">${new Date(data.screening_date_time).toLocaleString()}</p>
            </div>`;
    }
    if (data && data.onboarding_date_time) {
        detailsHtml += `
            <div>
                <label class="block text-xs font-medium text-gray-500 uppercase">Onboarding Date</label>
                <p class="mt-1 text-sm text-gray-900">${new Date(data.onboarding_date_time).toLocaleString()}</p>
            </div>`;
    }
    
    // Notes
    if (data && data.notes) {
        detailsHtml += `
            <div class="col-span-2">
                <label class="block text-xs font-medium text-gray-500 uppercase">Notes</label>
                <p class="mt-1 text-sm text-gray-900">${data.notes}</p>
            </div>`;
    }
    
    detailsHtml += '</div>';
    detailsContainer.innerHTML = detailsHtml;
    
    modal.classList.remove('hidden');
}

function closeLogDetailModal() {
    document.getElementById('logDetailModal').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLogDetailModal();
    }
});
</script>
