@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
@include('layouts.header', ['pageTitle' => 'Dashboard'])

<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Row 1: 4 Stat Boxes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Applicants This Month -->
        <div class="bg-white rounded-lg shadow-md p-4 relative">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#0E335D20]">
                <svg class="w-5 h-5 text-[#0E335D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zM6 20v-1a4 4 0 014-4h4a4 4 0 014 4v1"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Applicants This Month</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['applicants_this_month'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineNewApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- For Demo Applicants -->
        <div class="bg-white rounded-lg shadow-md p-4 relative">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#E6B80022]">
                <svg class="w-5 h-5 text-[#E6B800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v6a3 3 0 003 3h12a3 3 0 003-3V7"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">For Demo Applicants</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['demo_applicants'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineDemoApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- Onboarding Applicants -->
        <div class="bg-white rounded-lg shadow-md p-4 relative">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#A78BFA22]">
                <svg class="w-5 h-5 text-[#A78BFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Onboarding Applicants</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['onboarding_applicants'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineOnboardApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- Existing Employees -->
        <div class="bg-white rounded-lg shadow-md p-4 relative">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#9DC9FD22]">
                <svg class="w-5 h-5 text-[#9DC9FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 21h18"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Existing Employees</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['existing_employees'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineEmployees" height="40"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 2: Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Hiring & Onboarding Reports -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col">
            <h2 class="text-lg font-semibold text-[#0E335D] mb-4">Hiring &amp; Onboarding Reports</h2>

            <div class="grid grid-cols-2 items-center gap-4">
                <!-- Left Column: Status List -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Not Recommended</span>
                        <span class="font-semibold text-[#AA1B1B]">{{ $stats['hiring_stats']['not_recommended'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">No Answer</span>
                        <span class="font-semibold text-[#FF7515]">{{ $stats['hiring_stats']['no_answer'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Declined</span>
                        <span class="font-semibold text-[#E02F2F]">{{ $stats['hiring_stats']['declined'] }}</span>
                    </div>

                    <!-- Mini Line Graph for Status Trends -->
                    <div class="mt-4">
                        <canvas id="statusLineChart" height="60"></canvas>
                    </div>
                </div>

                <!-- Right Column: Thicker Doughnut Chart -->
                <div class="flex justify-center">
                    <canvas id="hiringOnboardChart" width="140" height="140"></canvas>
                </div>
            </div>
        </div>

        <!-- GLS Scheduling Reports -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col">
            <h2 class="text-lg font-semibold text-[#0E335D] mb-4">GLS Scheduling Reports</h2>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Classes Conducted</p>
                    <p class="text-lg font-semibold text-[#0E335D]">{{ $stats['classes_conducted'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Cancelled Classes</p>
                    <p class="text-lg font-semibold text-red-500">{{ $stats['cancelled_classes'] }}</p>
                </div>
            </div>

            <div class="flex justify-center">
                <canvas id="glsSchedulingChart" height="160"></canvas>
            </div>
            
            <!-- Additional Class Assignment Stats -->
            <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Fully Assigned</p>
                    <p class="text-sm font-semibold text-green-600">{{ $stats['fully_assigned_classes'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Partially Assigned</p>
                    <p class="text-sm font-semibold text-yellow-600">{{ $stats['partially_assigned_classes'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Unassigned</p>
                    <p class="text-sm font-semibold text-red-600">{{ $stats['unassigned_classes'] }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Row 3: Additional Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <!-- Tutor Statistics -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-[#0E335D] mb-4">Tutor Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Active Tutors</span>
                    <span class="font-semibold text-[#0E335D]">{{ $stats['active_tutors'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Utilization Rate</span>
                    <span class="font-semibold text-[#0E335D]">{{ $stats['tutor_utilization'] }}%</span>
                </div>
            </div>
        </div>
        
        <!-- Schedule Status Breakdown -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-[#0E335D] mb-4">Schedule Status</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Finalized</span>
                    <span class="font-semibold text-green-600">{{ $stats['schedule_status_breakdown']['finalized'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Tentative</span>
                    <span class="font-semibold text-yellow-600">{{ $stats['schedule_status_breakdown']['tentative'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">Draft</span>
                    <span class="font-semibold text-gray-600">{{ $stats['schedule_status_breakdown']['draft'] }}</span>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-[#0E335D] mb-4">Recent Activity</h3>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                @forelse($stats['recent_activity'] as $activity)
                <div class="text-xs border-l-2 border-blue-200 pl-2">
                    <div class="font-medium text-gray-800">{{ ucfirst($activity['action']) }}</div>
                    <div class="text-gray-600">{{ $activity['class_name'] }} - {{ $activity['school'] }}</div>
                    <div class="text-gray-500">{{ \Carbon\Carbon::parse($activity['performed_at'])->diffForHumans() }}</div>
                </div>
                @empty
                <div class="text-sm text-gray-500">No recent activity</div>
                @endforelse
            </div>
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
    new Chart(document.getElementById('hiringOnboardChart'), {
        type: 'doughnut',
        data: {
            labels: ['Not Recommended', 'No Answer', 'Declined'],
            datasets: [{
                data: [hiringStats.not_recommended, hiringStats.no_answer, hiringStats.declined],
                backgroundColor: ['#AA1B1B', '#FF7515', '#E02F2F']
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
                    callbacks: {
                        label: function(context) {
                            return context.label + ": " + context.raw;
                        }
                    }
                }
            }
        }
    });

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
                    position: 'bottom'
                },
                tooltip: tooltipFormat("Applicants")
            },
            elements: {
                point: {
                    radius: 3
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
    });

    // ------------------------------
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
</script>

@endsection