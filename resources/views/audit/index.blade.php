@extends('layouts.app')

@section('title', 'Audit Log - OGS Connect')

@section('content')
@include('layouts.header', ['pageTitle' => 'Audit Log'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-6 min-w-0">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Total Logs</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['total_logs']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Important Logs</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['important_logs']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-orange-100 rounded-lg">
                    <i class="fas fa-shield-alt text-orange-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">High Severity</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['high_severity_logs']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-clock text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-600">Recent (7 days)</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['recent_logs']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('audit.index') }}" class="space-y-4">
            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search description, action, email, or name..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                </div>

                <!-- Event Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                    <select name="event_type" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                        <option value="">All Events</option>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type }}" {{ request('event_type') === $type ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                    <select name="user_type" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                        <option value="">All Users</option>
                        @foreach($userTypes as $type)
                            <option value="{{ $type }}" {{ request('user_type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Severity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                    <select name="severity" class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                        <option value="">All Severities</option>
                        @foreach($severities as $severity)
                            <option value="{{ $severity }}" {{ request('severity') === $severity ? 'selected' : '' }}>
                                {{ ucfirst($severity) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end gap-4">
                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="date_from" id="auditDateFrom" value="{{ request('date_from') }}" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="date_to" id="auditDateTo" value="{{ request('date_to') }}" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                    <div id="auditDateValidationMessage" class="mt-1 p-2 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded text-sm" style="display: none;"></div>
                </div>

                <!-- Important Only -->
                <div class="flex items-center">
                    <input type="checkbox" name="important_only" value="1" {{ request('important_only') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-[#0E335D] focus:ring-[#0E335D]" id="important_only">
                    <label for="important_only" class="ml-2 text-sm text-gray-700">Important only</label>
                </div>

                <!-- Filter Button -->
                <button type="submit" class="px-4 py-2 bg-[#0E335D] text-white rounded-md hover:bg-[#1a4971] transition-colors"
                        title="Apply the selected filters to audit logs">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>

                <!-- Clear Filters -->
                <a href="{{ route('audit.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
                   title="Remove all filters and show all audit logs">
                    Clear
                </a>

                <!-- Export -->
                <a href="{{ route('audit.export', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                   title="Download filtered audit logs as CSV file">
                    <i class="fas fa-download mr-2"></i>Export
                </a>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Audit Logs</h2>
            <p class="text-sm text-gray-600">Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} logs</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 {{ $log->is_important ? 'bg-yellow-50 border-l-4 border-yellow-400' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->created_at->format('M j, Y') }}<br>
                                <span class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s T') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="{{ $log->event_type_icon }} text-gray-600 mr-2"></i>
                                    <span class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $log->event_type)) }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $log->user_name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $log->user_email ?? 'N/A' }}</div>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($log->user_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->action }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="max-w-xs truncate">{{ $log->description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $severityTooltips = [
                                        'low' => 'Low Severity: Routine activities and normal operations',
                                        'medium' => 'Medium Severity: Events requiring attention or monitoring',
                                        'high' => 'High Severity: Serious events that may impact operations',
                                        'critical' => 'Critical Severity: Emergency situations requiring immediate action'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $log->severity_badge_color }}"
                                      title="{{ $severityTooltips[$log->severity] ?? 'Unknown severity level' }}">
                                    {{ ucfirst($log->severity) }}
                                </span>
                                @if($log->is_important)
                                    <i class="fas fa-star text-yellow-500 ml-1" title="Important"></i>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('audit.show', $log) }}" class="text-[#0E335D] hover:text-[#1a4971]"
                                   title="View detailed information about this audit log entry">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                <p>No audit logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const auditDateFrom = document.getElementById('auditDateFrom');
    const auditDateTo = document.getElementById('auditDateTo');
    const messageDiv = document.getElementById('auditDateValidationMessage');
    
    // Date validation for audit logs
    function validateAuditDateRange() {
        const fromDateValue = new Date(auditDateFrom.value);
        const toDateValue = new Date(auditDateTo.value);
        
        if (auditDateFrom.value && auditDateTo.value && fromDateValue > toDateValue) {
            // If start date is after end date, adjust end date
            auditDateTo.value = auditDateFrom.value;
            showAuditDateValidationMessage('End date cannot be earlier than start date. End date has been adjusted.');
        }
    }
    
    function showAuditDateValidationMessage(message) {
        if (messageDiv) {
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            
            // Hide message after 3 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
    }
    
    // Add event listeners for date validation
    if (auditDateFrom) {
        auditDateFrom.addEventListener('change', function() {
            // Set minimum date for end date
            if (auditDateTo) {
                auditDateTo.min = this.value;
            }
            validateAuditDateRange();
        });
    }
    
    if (auditDateTo) {
        auditDateTo.addEventListener('change', function() {
            // Set maximum date for start date
            if (auditDateFrom) {
                auditDateFrom.max = this.value;
            }
            validateAuditDateRange();
        });
    }
    
    // Initialize date constraints
    if (auditDateFrom && auditDateFrom.value && auditDateTo) {
        auditDateTo.min = auditDateFrom.value;
    }
    if (auditDateTo && auditDateTo.value && auditDateFrom) {
        auditDateFrom.max = auditDateTo.value;
    }
});
</script>
@endsection