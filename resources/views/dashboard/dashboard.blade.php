@extends('layouts.app')

@section('title', 'OGS Connect')

@section('content')
@include('layouts.header', ['pageTitle' => 'Dashboard'])

<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <h3 class="text-lg font-semibold text-[#0E335D]">Dashboard Filters</h3>
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Date Range Filter -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">From:</label>
                    <input type="date" id="filterFromDate" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">To:</label>
                    <input type="date" id="filterToDate" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                </div>
                
                <!-- Month Filter -->
                <select id="filterMonth" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">All Months</option>
                    @for($i = 0; $i < 12; $i++)
                        @php
                            $date = \Carbon\Carbon::now()->subMonths($i);
                            $value = $date->format('Y-m');
                            $label = $date->format('F Y');
                        @endphp
                        <option value="{{ $value }}" {{ $i === 0 ? 'selected' : '' }}>{{ $label }}</option>
                    @endfor
                </select>
                
                <!-- Account Filter -->
                <select id="filterAccount" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                    <option value="">All Accounts</option>
                    <option value="GLS">GLS</option>
                    <option value="talk915">Talk915</option>
                    <option value="babilala">Babilala</option>
                    <option value="tutlo">Tutlo</option>
                </select>
                
                <!-- Apply & Reset Buttons -->
                <button onclick="applyFilters()" class="bg-[#0E335D] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-[#184679]">
                    Apply Filters
                </button>
                <button onclick="resetFilters()" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Row 1: 4 Stat Boxes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Applicants This Month -->
        <div class="bg-white rounded-lg shadow-md p-4 relative cursor-pointer hover:shadow-lg transition-shadow" onclick="openDetailsModal('applicants')">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#0E335D20]">
                <svg class="w-5 h-5 text-[#0E335D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zM6 20v-1a4 4 0 014-4h4a4 4 0 014 4v1"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Applicants This Month</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" id="stat-applicants">{{ $stats['applicants_this_month'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineNewApplicants" height="40"></canvas>
            </div>
            <p class="text-xs text-gray-500 mt-2">Click to view details</p>
        </div>

        <!-- For Demo Applicants -->
        <div class="bg-white rounded-lg shadow-md p-4 relative cursor-pointer hover:shadow-lg transition-shadow" onclick="openDetailsModal('demo')">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#E6B80022]">
                <svg class="w-5 h-5 text-[#E6B800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v6a3 3 0 003 3h12a3 3 0 003-3V7"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">For Demo Applicants</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" id="stat-demo">{{ $stats['demo_applicants'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineDemoApplicants" height="40"></canvas>
            </div>
            <p class="text-xs text-gray-500 mt-2">Click to view details</p>
        </div>

        <!-- Onboarding Applicants -->
        <div class="bg-white rounded-lg shadow-md p-4 relative cursor-pointer hover:shadow-lg transition-shadow" onclick="openDetailsModal('onboarding')">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#A78BFA22]">
                <svg class="w-5 h-5 text-[#A78BFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Onboarding Applicants</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" id="stat-onboarding">{{ $stats['onboarding_applicants'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineOnboardApplicants" height="40"></canvas>
            </div>
            <p class="text-xs text-gray-500 mt-2">Click to view details</p>
        </div>

        <!-- Existing Employees -->
        <div class="bg-white rounded-lg shadow-md p-4 relative cursor-pointer hover:shadow-lg transition-shadow" onclick="openDetailsModal('employees')">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#9DC9FD22]">
                <svg class="w-5 h-5 text-[#9DC9FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 21h18"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Existing Employees</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" id="stat-employees">{{ $stats['existing_employees'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineEmployees" height="40"></canvas>
            </div>
            <p class="text-xs text-gray-500 mt-2">Click to view details</p>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-[85vh] overflow-hidden flex flex-col">
            <div class="bg-gradient-to-r from-[#0E335D] to-[#184679] p-5 text-white flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="text-xl font-bold" id="modalTitle">Details</h3>
                    <p class="text-sm text-white/80 mt-1" id="modalSubtitle">0 records found</p>
                </div>
                <button onclick="closeDetailsModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <div class="p-5 overflow-y-auto flex-grow">
                <div id="modalContent" class="overflow-x-auto">
                    <!-- Content will be loaded here -->
                </div>
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
            <h2 class="text-lg font-semibold text-[#0E335D] mb-4">OGS Scheduling Reports</h2>

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
<script src="{{ asset('js/modal-utils.js') }}"></script>
<script>
    // ------------------------------
    // Labels & Weekly Date Ranges (from real data)
    // ------------------------------
    const weeklyTrends = @json($stats['weekly_trends']);
    const labels = weeklyTrends.map(week => week.week);
    const weekRanges = weeklyTrends.map(week => week.date_range);
    
    let sparklineCharts = {};
    let statusLineChartInstance = null;
    let schedulingChartInstance = null;

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
    // Sparklines (Top 4 Cards) - Load real data
    // ------------------------------
    function createSparkline(id, color, labelName, type) {
        fetch(`/api/dashboard-sparkline?type=${type}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(result => {
                sparklineCharts[id] = new Chart(document.getElementById(id), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: labelName,
                            data: result.data,
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
            })
            .catch(error => {
                console.error('Error loading sparkline:', error);
            });
    }

    createSparkline('sparklineNewApplicants', '#0E335D', 'New Applicants', 'applicants');
    createSparkline('sparklineDemoApplicants', '#E6B800', 'Demo Applicants', 'demo');
    createSparkline('sparklineOnboardApplicants', '#A78BFA', 'Onboarding Applicants', 'onboarding');
    createSparkline('sparklineEmployees', '#9DC9FD', 'Employees', 'employees');

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
    // Status Line Chart - Load real data
    // ------------------------------
    fetch('/api/dashboard-hiring-trends')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(result => {
            const trendsData = result.data;
            statusLineChartInstance = new Chart(document.getElementById('statusLineChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                            label: 'Not Recommended',
                            data: trendsData.map(week => week.not_recommended),
                            borderColor: '#AA1B1B',
                            backgroundColor: '#AA1B1B33',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'No Answer',
                            data: trendsData.map(week => week.no_answer),
                            borderColor: '#FF7515',
                            backgroundColor: '#FF751533',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: 'Declined',
                            data: trendsData.map(week => week.declined),
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
                                boxWidth: 12,
                                padding: 8,
                                font: {
                                    size: 10
                                }
                            }
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
                            display: true,
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            display: false
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading hiring trends:', error);
            // Show user-friendly error
            document.getElementById('statusLineChart').parentElement.innerHTML = 
                '<div class="text-center text-red-500 py-4">Failed to load chart data</div>';
        });

    // ------------------------------
    // GLS Scheduling Bar Chart (using real data)
    // ------------------------------
    schedulingChartInstance = new Chart(document.getElementById('glsSchedulingChart'), {
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
                tooltip: tooltipFormat("Classes"),
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 8,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                },
                y: {
                    stacked: true,
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

    // ------------------------------
    // Modal Functions
    // ------------------------------
    function openDetailsModal(type) {
        const month = document.getElementById('filterMonth').value || '{{ \Carbon\Carbon::now()->format("Y-m") }}';
        const modalTitles = {
            'applicants': 'Applicants This Month',
            'demo': 'For Demo Applicants',
            'onboarding': 'Onboarding Applicants',
            'employees': 'Existing Employees'
        };
        
        document.getElementById('modalTitle').textContent = modalTitles[type];
        document.getElementById('modalContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-[#0E335D]"></i><p class="mt-2 text-gray-600">Loading...</p></div>';
        document.getElementById('detailsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        fetch(`/api/dashboard-applicants-details?type=${type}&month=${month}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(result => {
                if (result.error) {
                    throw new Error(result.error);
                }
                
                document.getElementById('modalSubtitle').textContent = `${result.count} records found for ${result.month}`;
                
                if (result.data.length === 0) {
                    document.getElementById('modalContent').innerHTML = '<p class="text-gray-500 text-center py-12">No data available</p>';
                    return;
                }
                
                let tableHTML = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr>';
                
                // Different headers based on type
                if (type === 'applicants') {
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>';
                } else if (type === 'employees') {
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>';
                } else {
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phase</th>';
                    tableHTML += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>';
                }
                
                tableHTML += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                
                result.data.forEach(item => {
                    tableHTML += '<tr class="hover:bg-gray-50">';
                    tableHTML += `<td class="px-4 py-3 text-sm font-medium text-gray-900">${item.name}</td>`;
                    tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.email}</td>`;
                    
                    if (type === 'employees') {
                        tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.username}</td>`;
                        tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.account}</td>`;
                    } else if (type === 'applicants') {
                        tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.phone}</td>`;
                        tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.date}</td>`;
                    } else {
                        tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.phone}</td>`;
                        tableHTML += `<td class="px-4 py-3 text-sm text-gray-600">${item.phase}</td>`;
                    }
                    
                    tableHTML += `<td class="px-4 py-3 text-sm"><span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">${item.status}</span></td>`;
                    tableHTML += '</tr>';
                });
                
                tableHTML += '</tbody></table>';
                document.getElementById('modalContent').innerHTML = tableHTML;
            })
            .catch(error => {
                console.error('Error loading details:', error);
                document.getElementById('modalContent').innerHTML = 
                    '<div class="text-center py-12"><i class="fas fa-exclamation-circle text-4xl text-red-500 mb-3"></i><p class="text-red-600 font-medium">Failed to load data</p><p class="text-gray-500 text-sm mt-2">' + error.message + '</p></div>';
            });
    }
    
    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    // ------------------------------
    // Filter Functions
    // ------------------------------
    function applyFilters() {
        const month = document.getElementById('filterMonth').value;
        const fromDate = document.getElementById('filterFromDate').value;
        const toDate = document.getElementById('filterToDate').value;
        const account = document.getElementById('filterAccount').value;
        
        // Reload page with filters
        const params = new URLSearchParams();
        if (month) params.append('month', month);
        if (fromDate) params.append('from_date', fromDate);
        if (toDate) params.append('to_date', toDate);
        if (account) params.append('account', account);
        
        window.location.href = '/dashboard?' + params.toString();
    }
    
    function resetFilters() {
        document.getElementById('filterMonth').value = '{{ \Carbon\Carbon::now()->format("Y-m") }}';
        document.getElementById('filterFromDate').value = '';
        document.getElementById('filterToDate').value = '';
        document.getElementById('filterAccount').value = '';
        window.location.href = '/dashboard';
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailsModal();
        }
    });
    
    // Close modal on background click
    document.getElementById('detailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailsModal();
        }
    });
</script>

@endsection