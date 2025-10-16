@extends('layouts.app')

@section('title', 'Audit Log Details - OGS Connect')

@section('content')
@include('layouts.header', ['pageTitle' => 'Audit Log Details'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-6 min-w-0">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('audit.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors"
           title="Return to the audit logs list">
            <i class="fas fa-arrow-left mr-2"></i>Back to Audit Logs
        </a>
    </div>

    <!-- Audit Log Details -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 {{ $auditLog->is_important ? 'bg-yellow-50 border-yellow-200' : '' }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="{{ $auditLog->event_type_icon }} text-2xl text-gray-600 mr-3"></i>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">{{ ucwords(str_replace('_', ' ', $auditLog->event_type)) }}</h1>
                        <p class="text-sm text-gray-600">{{ $auditLog->created_at->format('F j, Y \a\t H:i:s T') }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $auditLog->severity_badge_color }}">
                        {{ ucfirst($auditLog->severity) }}
                    </span>
                    @if($auditLog->is_important)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fas fa-star mr-1"></i>Important
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Event Information -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Information</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Event Type</dt>
                            <dd class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $auditLog->event_type)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Action</dt>
                            <dd class="text-sm text-gray-900">{{ $auditLog->action }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="text-sm text-gray-900">{{ $auditLog->description }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Severity</dt>
                            <dd class="text-sm text-gray-900">
                                @php
                                    $severityTooltips = [
                                        'low' => 'Low Severity: Routine activities and normal operations',
                                        'medium' => 'Medium Severity: Events requiring attention or monitoring',
                                        'high' => 'High Severity: Serious events that may impact operations',
                                        'critical' => 'Critical Severity: Emergency situations requiring immediate action'
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $auditLog->severity_badge_color }}"
                                      title="{{ $severityTooltips[$auditLog->severity] ?? 'Unknown severity level' }}">
                                    {{ ucfirst($auditLog->severity) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
                            <dd class="text-sm text-gray-900">
                                {{ $auditLog->created_at->format('F j, Y \a\t H:i:s T') }}
                                <span class="text-gray-500">({{ $auditLog->created_at->diffForHumans() }})</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- User Information -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">User Information</h2>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User Type</dt>
                            <dd class="text-sm text-gray-900">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($auditLog->user_type) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">User ID</dt>
                            <dd class="text-sm text-gray-900">{{ $auditLog->user_id ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="text-sm text-gray-900">{{ $auditLog->user_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ $auditLog->user_email ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Related Audit Logs -->
            <div class="mt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Related Logs</h2>
                @php
                    $relatedLogs = \App\Models\AuditLog::where('user_id', $auditLog->user_id)
                        ->where('id', '!=', $auditLog->id)
                        ->where('created_at', '>=', $auditLog->created_at->subHours(1))
                        ->where('created_at', '<=', $auditLog->created_at->addHours(1))
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp

                @if($relatedLogs->count() > 0)
                    <div class="space-y-2">
                        @foreach($relatedLogs as $relatedLog)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <i class="{{ $relatedLog->event_type_icon }} text-gray-600 mr-2"></i>
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $relatedLog->event_type)) }}</span>
                                        <span class="text-sm text-gray-500 ml-2">{{ $relatedLog->created_at->format('H:i:s') }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('audit.show', $relatedLog) }}" class="text-[#0E335D] hover:text-[#1a4971] text-sm">
                                    View <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No related logs found within 1 hour of this event.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection