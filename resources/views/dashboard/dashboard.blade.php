@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
@include('layouts.header', ['pageTitle' => 'Dashboard'])

<div class="w-full px-4 sm:px-6 lg:px-8 py-6 min-w-0">
    <!-- Enhanced Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <h2 class="text-lg font-semibold text-[#0E335D]">Dashboard Filters</h2>
            
            <div class="flex flex-col sm:flex-row gap-4 lg:flex-1 lg:justify-end">
                <!-- Date Range Filter -->
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-600 mb-1">From Date</label>
                        <input type="date" id="fromDate" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]" value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="flex flex-col">
                        <label class="text-xs text-gray-600 mb-1">To Date</label>
                        <input type="date" id="toDate" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                
                <!-- Quick Date Filters -->
                <div class="flex flex-col">
                    <label class="text-xs text-gray-600 mb-1">Quick Select</label>
                    <select id="quickDateFilter" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-[#0E335D] focus:border-[#0E335D]">
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month" selected>This Month</option>
                        <option value="quarter">This Quarter</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <!-- Apply Filter Button -->
                <div class="flex flex-col justify-end">
                    <button id="applyFilters" class="px-4 py-2 bg-[#0E335D] text-white rounded-md hover:bg-[#1a4971] transition-colors text-sm font-medium"
                            title="Apply selected date filters to update dashboard statistics">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Current Filter Display -->
        <div id="currentFilters" class="mt-4 text-sm text-gray-600">
            Current view: <span id="filterDisplay" class="font-medium text-[#0E335D]">This Month</span>
        </div>
    </div>
    <!-- Row 1: 4 KPI Cards with Improved Alignment -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Applicants This Month -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden hover:shadow-md transition-shadow duration-300 h-full">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-[#0E335D] mb-2">Applicants This Month</p>
                    <div class="text-3xl font-bold text-gray-900" data-stat="applicants_this_month">{{ $stats['applicants_this_month'] }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-[#0E335D]/10 flex-shrink-0">
                    <svg class="w-6 h-6 text-[#0E335D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zM6 20v-1a4 4 0 014-4h4a4 4 0 014 4v1"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-auto">
                <canvas id="sparklineNewApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- For Demo Applicants -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden hover:shadow-md transition-shadow duration-300 h-full">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-[#0E335D] mb-2">For Demo Applicants</p>
                    <div class="text-3xl font-bold text-gray-900" data-stat="demo_applicants">{{ $stats['demo_applicants'] }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-[#E6B800]/10 flex-shrink-0">
                    <svg class="w-6 h-6 text-[#E6B800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-auto">
                <canvas id="sparklineDemoApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- Onboarding Applicants -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden hover:shadow-md transition-shadow duration-300 h-full">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-[#0E335D] mb-2">Onboarding Applicants</p>
                    <div class="text-3xl font-bold text-gray-900" data-stat="onboarding_applicants">{{ $stats['onboarding_applicants'] }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-[#A78BFA]/10 flex-shrink-0">
                    <svg class="w-6 h-6 text-[#A78BFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-auto">
                <canvas id="sparklineOnboardApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- Existing Employees -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative overflow-hidden hover:shadow-md transition-shadow duration-300 h-full">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-[#0E335D] mb-2">Existing Employees</p>
                    <div class="text-3xl font-bold text-gray-900" data-stat="existing_employees">{{ $stats['existing_employees'] }}</div>
                </div>
                <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-[#22C55E]/10 flex-shrink-0">
                    <svg class="w-6 h-6 text-[#22C55E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-auto">
                <canvas id="sparklineEmployees" height="40"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 2: Reports with Enhanced Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        <!-- Hiring & Onboarding Reports -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full" style="overflow: visible;">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-[#0E335D]">Hiring & Onboarding Reports</h2>
                <div class="w-3 h-3 bg-[#0E335D] rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 items-center gap-6 h-full" style="overflow: visible;">
                <!-- Left Column: Status List -->
                <div class="space-y-4">
                    <!-- Status Breakdown -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-[#AA1B1B] rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-700">Not Recommended</span>
                        </div>
                        <span class="font-bold text-[#AA1B1B] text-lg">{{ $stats['hiring_stats']['not_recommended'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-[#FF7515] rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-700">No Answer</span>
                        </div>
                        <span class="font-bold text-[#FF7515] text-lg">{{ $stats['hiring_stats']['no_answer'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-[#E02F2F] rounded-full mr-3"></div>
                            <span class="text-sm font-medium text-gray-700">Declined</span>
                        </div>
                        <span class="font-bold text-[#E02F2F] text-lg">{{ $stats['hiring_stats']['declined'] ?? 0 }}</span>
                    </div>

                    <!-- Mini Line Graph for Status Trends -->
                    <div class="mt-6 p-3 bg-gray-50 rounded-lg">
                        <canvas id="statusLineChart" height="60"></canvas>
                    </div>
                </div>

                <!-- Right Column: Enhanced Doughnut Chart -->
                <div class="flex justify-center items-center h-full">
                    <div class="relative" style="overflow: visible;">
                        <canvas id="hiringOnboardChart" width="160" height="160"></canvas>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-[#0E335D]">{{ array_sum($stats['hiring_stats']) }}</div>
                                <div class="text-xs text-gray-500">Total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GLS Scheduling Reports -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-[#0E335D]">GLS Scheduling Reports</h2>
                <div class="w-3 h-3 bg-[#22C55E] rounded-full"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 font-medium mb-2">Classes Conducted</p>
                    <p class="text-2xl font-bold text-[#0E335D]" data-stat="classes_conducted">{{ $stats['classes_conducted'] }}</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-500 font-medium mb-2">Cancelled Classes</p>
                    <p class="text-2xl font-bold text-red-500" data-stat="cancelled_classes">{{ $stats['cancelled_classes'] }}</p>
                </div>
            </div>

            <div class="flex justify-center mb-6">
                <canvas id="glsSchedulingChart" height="140"></canvas>
            </div>
            
            <!-- Enhanced Class Assignment Stats -->
            <div class="grid grid-cols-3 gap-3 pt-4 border-t border-gray-200">
                <div class="text-center p-3 bg-green-50 rounded-lg">
                    <p class="text-xs text-gray-600 font-medium mb-1">Fully Assigned</p>
                    <p class="text-lg font-bold text-green-600" data-stat="fully_assigned_classes">{{ $stats['fully_assigned_classes'] }}</p>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                    <p class="text-xs text-gray-600 font-medium mb-1">Partially Assigned</p>
                    <p class="text-lg font-bold text-yellow-600" data-stat="partially_assigned_classes">{{ $stats['partially_assigned_classes'] }}</p>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg">
                    <p class="text-xs text-gray-600 font-medium mb-1">Unassigned</p>
                    <p class="text-lg font-bold text-red-600" data-stat="unassigned_classes">{{ $stats['unassigned_classes'] }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Row 3: Additional Statistics with Enhanced Design -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Tutor Statistics -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[#0E335D]">Tutor Statistics</h3>
                <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#0E335D]/10">
                    <svg class="w-4 h-4 text-[#0E335D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197V9a3 3 0 00-6 0v2M6 9h3"></path>
                    </svg>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Active Tutors</span>
                    <span class="font-bold text-[#0E335D] text-lg">{{ $stats['active_tutors'] }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Utilization Rate</span>
                    <span class="font-bold text-[#22C55E] text-lg">{{ $stats['tutor_utilization'] }}%</span>
                </div>
            </div>
        </div>
        
        <!-- Schedule Status Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[#0E335D]">Schedule Status</h3>
                <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#E6B800]/10">
                    <svg class="w-4 h-4 text-[#E6B800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Finalized</span>
                    <span class="font-bold text-[#22C55E] text-lg">{{ $stats['schedule_status_breakdown']['finalized'] }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Tentative</span>
                    <span class="font-bold text-[#E6B800] text-lg">{{ $stats['schedule_status_breakdown']['tentative'] }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">Draft</span>
                    <span class="font-bold text-gray-600 text-lg">{{ $stats['schedule_status_breakdown']['draft'] }}</span>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-[#0E335D]">Recent Activity</h3>
                <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#6366F1]/10">
                    <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="space-y-3 max-h-48 overflow-y-auto">
                @forelse($stats['recent_activity'] as $activity)
                <div class="border-l-4 border-[#6366F1] pl-4 py-2 bg-gray-50 rounded-r-lg">
                    <div class="font-medium text-gray-800 text-sm">{{ ucfirst($activity['action']) }}</div>
                    <div class="text-gray-600 text-xs">{{ $activity['class_name'] }} - {{ $activity['school'] }}</div>
                    <div class="text-gray-500 text-xs">{{ \Carbon\Carbon::parse($activity['performed_at'])->diffForHumans() }}</div>
                </div>
                @empty
                <div class="text-sm text-gray-500 text-center py-4">No recent activity</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Row 4: Tutor Performance Verification Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
        <!-- Reliable Tutors (Always On-Time/Agrees) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-[#0E335D]">Most Reliable Tutors</h2>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-[#22C55E] rounded-full"></div>
                    <span class="text-sm text-gray-600">{{ $stats['tutor_performance']['summary']['reliable_tutors_count'] ?? 0 }} Total</span>
                </div>
            </div>

            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($stats['tutor_performance']['top_reliable_tutors'] ?? [] as $tutor)
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">{{ $tutor['username'] }}</div>
                        <div class="text-sm text-gray-600">
                            {{ $tutor['total_assignments'] }} assignments • 
                            {{ $tutor['completion_rate'] }}% completion rate
                        </div>
                        <div class="text-xs text-green-600 font-medium">
                            {{ $tutor['cancellation_rate'] }}% cancellation rate
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-green-100">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <span class="text-xs text-green-600 font-medium mt-1">
                            {{ ucfirst($tutor['reliability_category'] ?? 'good') }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197V9a3 3 0 00-6 0v2M6 9h3"></path>
                    </svg>
                    <p class="text-sm">No reliable tutor data available</p>
                </div>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <button onclick="showDetailedPerformanceReport('reliable')" 
                        class="w-full px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-sm font-medium">
                    View All Reliable Tutors
                </button>
            </div>
        </div>

        <!-- Unreliable Tutors (Frequent Cancellations) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-[#0E335D]">Tutors with High Cancellation Rates</h2>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-[#EF4444] rounded-full"></div>
                    <span class="text-sm text-gray-600">{{ $stats['tutor_performance']['summary']['unreliable_tutors_count'] ?? 0 }} Total</span>
                </div>
            </div>

            <div class="space-y-3 max-h-64 overflow-y-auto">
                @forelse($stats['tutor_performance']['top_unreliable_tutors'] ?? [] as $tutor)
                <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800">{{ $tutor['username'] }}</div>
                        <div class="text-sm text-gray-600">
                            {{ $tutor['total_assignments'] }} assignments • 
                            {{ $tutor['completion_rate'] }}% completion rate
                        </div>
                        <div class="text-xs text-red-600 font-medium">
                            {{ $tutor['cancellation_rate'] }}% cancellation rate
                            @if($tutor['threshold_status'] === 'excluded')
                                • <span class="bg-red-100 px-2 py-1 rounded">Auto-assignment disabled</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-red-100">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <span class="text-xs text-red-600 font-medium mt-1">Unreliable</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm">All tutors are performing well!</p>
                </div>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <button onclick="showDetailedPerformanceReport('unreliable')" 
                        class="w-full px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                    View All Unreliable Tutors
                </button>
            </div>
        </div>
    </div>

    <!-- Performance Summary Statistics -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-[#0E335D]">Tutor Performance Summary</h2>
            <button onclick="refreshPerformanceData()" 
                    class="px-4 py-2 bg-[#0E335D] text-white rounded-lg hover:bg-[#1a4971] transition-colors text-sm font-medium">
                Refresh Data
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-xs text-gray-500 font-medium mb-2">Total Active Tutors</p>
                <p class="text-2xl font-bold text-[#0E335D]">{{ $stats['tutor_performance']['summary']['total_active_tutors'] ?? 0 }}</p>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-xs text-gray-500 font-medium mb-2">Reliable Tutors</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['tutor_performance']['summary']['reliable_percentage'] ?? 0 }}%</p>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <p class="text-xs text-gray-500 font-medium mb-2">High Cancellation Rate</p>
                <p class="text-2xl font-bold text-red-600">{{ $stats['tutor_performance']['summary']['unreliable_percentage'] ?? 0 }}%</p>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <p class="text-xs text-gray-500 font-medium mb-2">New Tutors</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['tutor_performance']['new_tutors_count'] ?? 0 }}</p>
            </div>
        </div>

        <div class="text-xs text-gray-500 text-center">
            <p>Performance evaluation based on last 3 months • 
            Threshold: {{ $stats['tutor_performance']['threshold_settings']['cancellation_rate_threshold'] ?? 0.3 * 100 }}% max cancellation rate • 
            Last updated: {{ $stats['tutor_performance']['last_updated'] ?? 'Unknown' }}</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ------------------------------
    // Labels & Weekly Date Ranges (from real data)
    // ------------------------------
    const weeklyTrends = @json($stats['weekly_trends']);
    const labels = weeklyTrends.map(week => week.week);
    const weekRanges = weeklyTrends.map(week => week.date_range);

    // Utility: Generate random data
    const randomData = (len, max) =>
        Array.from({
            length: len
        }, () => Math.floor(Math.random() * max));

    // Tooltip Formatter (Shared)
    const tooltipFormat = (labelName) => ({
        callbacks: {
            title: function(tooltipItems) {
                let index = tooltipItems[0].dataIndex;
                return labels[index] + " (" + weekRanges[index] + ")";
            },
            label: function(context) {
                return labelName + ": " + context.raw;
            }
        }
    });

    // ------------------------------
    // Sparklines (Top 4 Cards)
    // ------------------------------
    function createSparkline(id, color, labelName) {
        new Chart(document.getElementById(id), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: labelName,
                    data: randomData(4, 20),
                    borderColor: color,
                    backgroundColor: color + "33",
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: tooltipFormat(labelName)
                },
                elements: {
                    point: {
                        radius: 0
                    }
                },
                scales: {
                    x: {
                        display: false
                    },
                    y: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    createSparkline('sparklineNewApplicants', '#0E335D', 'New Applicants');
    createSparkline('sparklineDemoApplicants', '#E6B800', 'Demo Applicants');
    createSparkline('sparklineOnboardApplicants', '#A78BFA', 'Onboarding Applicants');
    createSparkline('sparklineEmployees', '#9DC9FD', 'Employees');

    // ------------------------------
    // Hiring & Onboarding Doughnut (using real data)
    // ------------------------------
    const hiringStats = @json($stats['hiring_stats']);
    console.log('Hiring Stats Data:', hiringStats);
    
    // Check if canvas element exists
    const canvasElement = document.getElementById('hiringOnboardChart');
    console.log('Canvas element found:', canvasElement);
    
    if (!canvasElement) {
        console.error('Canvas element with id "hiringOnboardChart" not found!');
    } else {
        console.log('Canvas dimensions:', canvasElement.width, 'x', canvasElement.height);
        console.log('Canvas position:', canvasElement.getBoundingClientRect());
        
        // Add basic mouse event listeners to canvas for debugging
        canvasElement.addEventListener('mousemove', function(e) {
            console.log('Canvas mousemove event:', e.offsetX, e.offsetY);
        });
        
        canvasElement.addEventListener('mouseenter', function(e) {
            console.log('Canvas mouseenter event');
        });
        
        canvasElement.addEventListener('mouseleave', function(e) {
            console.log('Canvas mouseleave event');
        });
    }
    
    try {
        const doughnutChart = new Chart(canvasElement, {
            type: 'doughnut',
            data: {
                labels: ['Not Recommended', 'No Answer', 'Declined'],
                datasets: [{
                    data: [hiringStats.not_recommended || 1, hiringStats.no_answer || 1, hiringStats.declined || 1],
                    backgroundColor: ['#AA1B1B', '#FF7515', '#E02F2F'],
                    hoverBackgroundColor: ['#CC2222', '#FF8533', '#F04444'],
                    hoverBorderWidth: 2,
                    hoverBorderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false,
                        external: function(context) {
                            console.log('Tooltip external called:', context);
                            
                            // Get or create tooltip element
                            let tooltipEl = document.getElementById('chartjs-tooltip');
                            
                            if (!tooltipEl) {
                                tooltipEl = document.createElement('div');
                                tooltipEl.id = 'chartjs-tooltip';
                                tooltipEl.style.background = 'rgba(0, 0, 0, 0.8)';
                                tooltipEl.style.color = 'white';
                                tooltipEl.style.borderRadius = '6px';
                                tooltipEl.style.padding = '10px';
                                tooltipEl.style.position = 'absolute';
                                tooltipEl.style.pointerEvents = 'none';
                                tooltipEl.style.transition = 'all .1s ease';
                                tooltipEl.style.zIndex = '1000';
                                tooltipEl.style.fontSize = '12px';
                                tooltipEl.style.fontFamily = 'Arial, sans-serif';
                                document.body.appendChild(tooltipEl);
                            }
                            
                            // Hide if no tooltip
                            if (context.tooltip.opacity === 0) {
                                tooltipEl.style.opacity = 0;
                                return;
                            }
                            
                            // Set text
                            if (context.tooltip.body) {
                                const titleLines = context.tooltip.title || [];
                                const bodyLines = context.tooltip.body.map(b => b.lines);
                                
                                let innerHtml = '';
                                
                                titleLines.forEach(function(title) {
                                    innerHtml += '<div style="font-weight: bold; margin-bottom: 5px;">' + title + '</div>';
                                });
                                
                                bodyLines.forEach(function(body, i) {
                                    const colors = context.tooltip.labelColors[i];
                                    let style = 'background:' + colors.backgroundColor;
                                    style += '; border-color:' + colors.borderColor;
                                    style += '; border-width: 2px';
                                    const span = '<span style="' + style + '; width: 10px; height: 10px; display: inline-block; margin-right: 5px;"></span>';
                                    innerHtml += '<div>' + span + body + '</div>';
                                });
                                
                                tooltipEl.innerHTML = innerHtml;
                            }
                            
                            // Position tooltip
                            const position = context.chart.canvas.getBoundingClientRect();
                            const bodyFont = Chart.helpers.toFont(context.tooltip.options.bodyFont);
                            
                            tooltipEl.style.opacity = 1;
                            tooltipEl.style.left = position.left + window.pageXOffset + context.tooltip.caretX + 'px';
                            tooltipEl.style.top = position.top + window.pageYOffset + context.tooltip.caretY + 'px';
                            tooltipEl.style.font = bodyFont.string;
                            tooltipEl.style.padding = context.tooltip.options.padding + 'px ' + context.tooltip.options.padding + 'px';
                        },
                        callbacks: {
                            label: function(context) {
                                console.log('Tooltip label callback:', context);
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                const result = `${label}: ${value} (${percentage}%)`;
                                console.log('Tooltip result:', result);
                                return result;
                            }
                        }
                    }
                },
                onHover: (event, activeElements) => {
                    console.log('Chart hover event:', event, 'Active elements:', activeElements);
                    if (activeElements.length > 0) {
                        console.log('Hovering over element:', activeElements[0]);
                        event.native.target.style.cursor = 'pointer';
                    } else {
                        console.log('Not hovering over any element');
                        event.native.target.style.cursor = 'default';
                    }
                },
                onClick: (event, activeElements) => {
                    console.log('Chart click event:', event, 'Active elements:', activeElements);
                    if (activeElements.length > 0) {
                        const element = activeElements[0];
                        const datasetIndex = element.datasetIndex;
                        const index = element.index;
                        const label = doughnutChart.data.labels[index];
                        const value = doughnutChart.data.datasets[datasetIndex].data[index];
                        console.log('Clicked on:', label, 'with value:', value);
                        alert(`Clicked on: ${label} - ${value}`);
                    }
                }
            }
        });
        
        console.log('Doughnut chart created successfully:', doughnutChart);
        
        // Test chart data
        console.log('Chart data:', doughnutChart.data);
        console.log('Chart options:', doughnutChart.options);
        
    } catch (error) {
        console.error('Error creating doughnut chart:', error);
    }

    // ------------------------------
    // Status Line Chart
    // ------------------------------
    new Chart(document.getElementById('statusLineChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                    label: 'Not Recommended',
                    data: randomData(4, 15),
                    borderColor: '#AA1B1B',
                    backgroundColor: '#AA1B1B33',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'No Answer',
                    data: randomData(4, 10),
                    borderColor: '#FF7515',
                    backgroundColor: '#FF751533',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Declined',
                    data: randomData(4, 7),
                    borderColor: '#E02F2F',
                    backgroundColor: '#E02F2F33',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        fontSize: 10
                    }
                },
                tooltip: tooltipFormat("Applicants")
            },
            elements: {
                point: {
                    radius: 2
                }
            },
            scales: {
                x: {
                    display: true
                },
                y: {
                    display: false
                }
            }
        }
    });    // ------------------------------
    // GLS Scheduling Bar Chart (using real data)
    // ------------------------------
    new Chart(document.getElementById('glsSchedulingChart'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                    label: 'Conducted',
                    data: weeklyTrends.map(week => week.conducted),
                    backgroundColor: '#9DC9FD'
                },
                {
                    label: 'Cancelled',
                    data: weeklyTrends.map(week => week.cancelled),
                    backgroundColor: '#E57373'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: tooltipFormat("Classes")
            },
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true
                }
            }
        }
    });

    // Dashboard Filtering Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const quickDateFilter = document.getElementById('quickDateFilter');
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');
        const applyFilters = document.getElementById('applyFilters');
        const filterDisplay = document.getElementById('filterDisplay');
        
        // Quick date filter handler
        quickDateFilter.addEventListener('change', function() {
            const value = this.value;
            const today = new Date();
            
            switch(value) {
                case 'today':
                    fromDate.value = today.toISOString().split('T')[0];
                    toDate.value = today.toISOString().split('T')[0];
                    break;
                case 'week':
                    const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
                    const endOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));
                    fromDate.value = startOfWeek.toISOString().split('T')[0];
                    toDate.value = endOfWeek.toISOString().split('T')[0];
                    break;
                case 'month':
                    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    fromDate.value = startOfMonth.toISOString().split('T')[0];
                    toDate.value = endOfMonth.toISOString().split('T')[0];
                    break;
                case 'quarter':
                    const quarter = Math.floor(today.getMonth() / 3);
                    const startOfQuarter = new Date(today.getFullYear(), quarter * 3, 1);
                    const endOfQuarter = new Date(today.getFullYear(), quarter * 3 + 3, 0);
                    fromDate.value = startOfQuarter.toISOString().split('T')[0];
                    toDate.value = endOfQuarter.toISOString().split('T')[0];
                    break;
                case 'year':
                    const startOfYear = new Date(today.getFullYear(), 0, 1);
                    const endOfYear = new Date(today.getFullYear(), 11, 31);
                    fromDate.value = startOfYear.toISOString().split('T')[0];
                    toDate.value = endOfYear.toISOString().split('T')[0];
                    break;
            }
            
            if (value !== 'custom') {
                updateFilterDisplay();
            }
        });
        
        // Apply filters handler
        applyFilters.addEventListener('click', function() {
            const filters = {
                fromDate: fromDate.value,
                toDate: toDate.value,
                dateRange: quickDateFilter.value
            };
            
            // Show loading state
            this.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...';
            this.disabled = true;
            
            // Fetch filtered data
            fetch('/api/dashboard-filtered-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(filters)
            })
            .then(response => response.json())
            .then(data => {
                updateDashboardStats(data);
                updateFilterDisplay();
                
                // Reset button
                this.innerHTML = 'Apply Filters';
                this.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading filtered data. Please try again.');
                
                // Reset button
                this.innerHTML = 'Apply Filters';
                this.disabled = false;
            });
        });
        
        function updateFilterDisplay() {
            const dateRange = quickDateFilter.value;
            const dateText = dateRange.charAt(0).toUpperCase() + dateRange.slice(1);
            filterDisplay.textContent = dateText;
        }
        
        function updateDashboardStats(data) {
            // Update stat boxes
            document.querySelector('[data-stat="applicants_this_month"]').textContent = data.applicants_this_month || 0;
            document.querySelector('[data-stat="demo_applicants"]').textContent = data.demo_applicants || 0;
            document.querySelector('[data-stat="onboarding_applicants"]').textContent = data.onboarding_applicants || 0;
            document.querySelector('[data-stat="existing_employees"]').textContent = data.existing_employees || 0;
            
            // Update GLS scheduling stats
            document.querySelector('[data-stat="classes_conducted"]').textContent = data.classes_conducted || 0;
            document.querySelector('[data-stat="cancelled_classes"]').textContent = data.cancelled_classes || 0;
            document.querySelector('[data-stat="fully_assigned_classes"]').textContent = data.fully_assigned_classes || 0;
            document.querySelector('[data-stat="partially_assigned_classes"]').textContent = data.partially_assigned_classes || 0;
            document.querySelector('[data-stat="unassigned_classes"]').textContent = data.unassigned_classes || 0;
            
            // Update charts if needed
            if (data.chart_data) {
                updateCharts(data.chart_data);
            }
        }
        
        function updateCharts(chartData) {
            // Update existing charts with new data
            // This would update the Chart.js instances with new data
            console.log('Chart data updated:', chartData);
        }
        
        // Date validation to prevent end date being earlier than start date
        function validateDateRange() {
            const fromDateValue = new Date(fromDate.value);
            const toDateValue = new Date(toDate.value);
            
            if (fromDateValue && toDateValue && fromDateValue > toDateValue) {
                // If start date is after end date, adjust end date
                toDate.value = fromDate.value;
                showDateValidationMessage('End date cannot be earlier than start date. End date has been adjusted.');
            }
        }
        
        function showDateValidationMessage(message) {
            // Create or update validation message
            let messageDiv = document.getElementById('dateValidationMessage');
            if (!messageDiv) {
                messageDiv = document.createElement('div');
                messageDiv.id = 'dateValidationMessage';
                messageDiv.className = 'mt-2 p-2 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded text-sm';
                toDate.parentNode.appendChild(messageDiv);
            }
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            
            // Hide message after 3 seconds
            setTimeout(() => {
                messageDiv.style.display = 'none';
            }, 3000);
        }
        
        // Add event listeners for date validation
        fromDate.addEventListener('change', function() {
            // Set minimum date for end date
            toDate.min = this.value;
            validateDateRange();
        });
        
        toDate.addEventListener('change', function() {
            // Set maximum date for start date
            fromDate.max = this.value;
            validateDateRange();
        });
        
        // Initialize date constraints
        if (fromDate.value) {
            toDate.min = fromDate.value;
        }
        if (toDate.value) {
            fromDate.max = toDate.value;
        }
        
        // Initialize filter display
        updateFilterDisplay();
    });

    // Tutor Performance Verification Functions
    function showDetailedPerformanceReport(type) {
        const months = 3; // Can be made configurable
        
        fetch(`/api/tutor-performance-report?months=${months}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPerformanceModal(data.data, type);
                } else {
                    alert('Error loading performance report: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading performance report. Please try again.');
            });
    }

    function refreshPerformanceData() {
        // Show loading state
        document.querySelectorAll('[data-stat]').forEach(el => {
            el.innerHTML = '<div class="animate-pulse bg-gray-200 h-6 w-12 rounded"></div>';
        });

        // Reload the page to refresh all data
        window.location.reload();
    }

    function showPerformanceModal(performanceData, type) {
        const tutors = type === 'reliable' ? performanceData.reliable_tutors : performanceData.unreliable_tutors;
        const title = type === 'reliable' ? 'All Reliable Tutors' : 'All Tutors with High Cancellation Rates';
        const colorClass = type === 'reliable' ? 'green' : 'red';
        
        // Create modal HTML
        const modalHTML = `
            <div id="performanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl p-6 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-[#0E335D]">${title}</h2>
                        <button onclick="closePerformanceModal()" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid gap-4">
                        ${tutors.map(tutor => `
                            <div class="flex items-center justify-between p-4 bg-${colorClass}-50 rounded-lg border border-${colorClass}-200">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800">${tutor.username}</div>
                                    <div class="text-sm text-gray-600">
                                        ${tutor.total_assignments} assignments • 
                                        ${tutor.completed_assignments} completed • 
                                        ${tutor.cancelled_assignments} cancelled
                                    </div>
                                    <div class="text-xs text-${colorClass}-600 font-medium">
                                        ${tutor.cancellation_rate}% cancellation rate • 
                                        ${tutor.completion_rate}% completion rate
                                        ${tutor.threshold_status === 'excluded' ? ' • Auto-assignment disabled' : ''}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-${colorClass}-600">${tutor.reliability_score ? (tutor.reliability_score * 100).toFixed(1) + '%' : 'N/A'}</div>
                                    <div class="text-xs text-gray-500">Reliability Score</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-semibold mb-2">Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div class="text-center">
                                <div class="font-bold text-lg">${tutors.length}</div>
                                <div class="text-gray-600">${type === 'reliable' ? 'Reliable' : 'Unreliable'} Tutors</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg">${performanceData.summary.total_active_tutors}</div>
                                <div class="text-gray-600">Total Active</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg">${performanceData.threshold_settings.cancellation_rate_threshold * 100}%</div>
                                <div class="text-gray-600">Threshold</div>
                            </div>
                            <div class="text-center">
                                <div class="font-bold text-lg">${performanceData.threshold_settings.evaluation_period_months}m</div>
                                <div class="text-gray-600">Evaluation Period</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    function closePerformanceModal() {
        const modal = document.getElementById('performanceModal');
        if (modal) {
            modal.remove();
        }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'performanceModal') {
            closePerformanceModal();
        }
    });
    
    // Browser navigation protection
    window.addEventListener('DOMContentLoaded', function() {
        // Prevent back button caching for authenticated pages
        if (window.history && window.history.pushState) {
            // Replace the history entry to prevent going back to login
            window.history.replaceState({page: 'dashboard'}, 'Dashboard', window.location.href);
            
            window.addEventListener('popstate', function(event) {
                // If user tries to go back, stay on dashboard
                window.history.pushState({page: 'dashboard'}, 'Dashboard', window.location.href);
            });
        }
        
        // Handle page show event (for browser back/forward)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was restored from cache, reload to ensure fresh authentication
                window.location.reload();
            }
        });
        
        // Prevent cache-related issues
        if (window.performance && window.performance.navigation.type === 2) {
            // User came here via back button, refresh the page to ensure auth state
            window.location.reload();
        }
    });

</script>

@endsection