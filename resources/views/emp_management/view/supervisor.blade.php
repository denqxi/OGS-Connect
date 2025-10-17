@extends('layouts.app')

@section('title', 'Supervisor Details - OGS Connect')

@section('content')
    <!-- Header -->
    @include('layouts.header', ['pageTitle' => 'Supervisor Details'])

    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('employees.index', ['tab' => 'supervisors']) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md hover:bg-gray-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Supervisors
        </a>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold">{{ $supervisor->full_name ?? 'N/A' }}</h1>
                        <p class="text-green-100 mt-2">{{ $supervisor->semail ?? 'N/A' }}</p>
                        <div class="flex items-center mt-4 space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $supervisor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($supervisor->status) }}
                            </span>
                            <span class="text-green-100">Supervisor ID: {{ $supervisor->supID ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold">{{ $supervisor->assigned_account ?? 'Unassigned' }}</div>
                        <div class="text-green-100">Assigned Account</div>
                    </div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Personal Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user mr-2 text-green-600"></i>
                            Personal Information
                        </h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Full Name:</span>
                                <span class="font-medium">{{ $supervisor->full_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium">{{ $supervisor->semail ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phone Number:</span>
                                <span class="font-medium">{{ $supervisor->sconNum ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Birth Date:</span>
                                <span class="font-medium">{{ $supervisor->birth_date ? \Carbon\Carbon::parse($supervisor->birth_date)->format('M j, Y') : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Date Hired:</span>
                                <span class="font-medium">{{ $supervisor->created_at ? $supervisor->created_at->format('M j, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Work Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-briefcase mr-2 text-blue-600"></i>
                            Work Information
                        </h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Assigned Account:</span>
                                <span class="font-medium">
                                    @if($supervisor->assigned_account)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $supervisor->assigned_account }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Unassigned</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Assigned Role:</span>
                                <span class="font-medium">{{ $supervisor->srole ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Shift:</span>
                                <span class="font-medium">{{ $supervisor->sshift ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">MS Teams Account:</span>
                                <span class="font-medium">{{ $supervisor->steams ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-orange-600"></i>
                        Additional Information
                    </h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $supervisor->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($supervisor->status) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="font-medium">{{ $supervisor->updated_at ? $supervisor->updated_at->format('M j, Y g:i A') : 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Created At:</span>
                                    <span class="font-medium">{{ $supervisor->created_at ? $supervisor->created_at->format('M j, Y g:i A') : 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Supervisor ID:</span>
                                    <span class="font-medium">{{ $supervisor->supID ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-cogs mr-2 text-purple-600"></i>
                        Quick Actions
                    </h2>
                    <div class="bg-gray-50 rounded-lg p-6">
                        <div class="flex flex-wrap gap-4">
                            @if($supervisor->status === 'active')
                                <button class="px-4 py-2 bg-red-100 text-red-600 rounded-md hover:bg-red-200 transition-colors text-sm font-medium"
                                        onclick="toggleSupervisorStatus('{{ $supervisor->supID }}', 'inactive')" 
                                        title="Deactivate supervisor - will remove access and permissions">
                                    <i class="fas fa-user-times mr-2"></i>
                                    Deactivate Supervisor
                                </button>
                            @else
                                <button class="px-4 py-2 bg-green-100 text-green-600 rounded-md hover:bg-green-200 transition-colors text-sm font-medium"
                                        onclick="toggleSupervisorStatus('{{ $supervisor->supID }}', 'active')" 
                                        title="Activate supervisor - will restore access and permissions">
                                    <i class="fas fa-user-check mr-2"></i>
                                    Activate Supervisor
                                </button>
                            @endif
                            
                            <a href="mailto:{{ $supervisor->semail }}" 
                               class="px-4 py-2 bg-blue-100 text-blue-600 rounded-md hover:bg-blue-200 transition-colors text-sm font-medium"
                               title="Send email to supervisor">
                                <i class="fas fa-envelope mr-2"></i>
                                Send Email
                            </a>
                            
                            @if($supervisor->sconNum)
                                <a href="tel:{{ $supervisor->sconNum }}" 
                                   class="px-4 py-2 bg-green-100 text-green-600 rounded-md hover:bg-green-200 transition-colors text-sm font-medium"
                                   title="Call supervisor">
                                    <i class="fas fa-phone mr-2"></i>
                                    Call
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
@endsection
