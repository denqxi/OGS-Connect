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
                    <button id="applyFilters" class="px-4 py-2 bg-[#0E335D] text-white rounded-md hover:bg-[#1a4971] transition-colors text-sm font-medium">
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
    <!-- Row 1: 4 Stat Boxes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 max-w-full">
        <!-- Applicants This Month -->
        <div class="bg-white rounded-lg shadow-md p-4 relative min-w-0 overflow-hidden">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#0E335D20]">
                <svg class="w-5 h-5 text-[#0E335D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zM6 20v-1a4 4 0 014-4h4a4 4 0 014 4v1"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Applicants This Month</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" data-stat="applicants_this_month">{{ $stats['applicants_this_month'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineNewApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- For Demo Applicants -->
        <div class="bg-white rounded-lg shadow-md p-4 relative min-w-0 overflow-hidden">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#E6B80022]">
                <svg class="w-5 h-5 text-[#E6B800]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v6a3 3 0 003 3h12a3 3 0 003-3V7"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">For Demo Applicants</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" data-stat="demo_applicants">{{ $stats['demo_applicants'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineDemoApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- Onboarding Applicants -->
        <div class="bg-white rounded-lg shadow-md p-4 relative min-w-0 overflow-hidden">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#A78BFA22]">
                <svg class="w-5 h-5 text-[#A78BFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Onboarding Applicants</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" data-stat="onboarding_applicants">{{ $stats['onboarding_applicants'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineOnboardApplicants" height="40"></canvas>
            </div>
        </div>

        <!-- Existing Employees -->
        <div class="bg-white rounded-lg shadow-md p-4 relative min-w-0 overflow-hidden">
            <div class="absolute top-3 right-3 w-10 h-10 flex items-center justify-center rounded-full bg-[#9DC9FD22]">
                <svg class="w-5 h-5 text-[#9DC9FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 21h18"></path>
                </svg>
            </div>
            <p class="text-sm font-medium text-[#0E335D]">Existing Employees</p>
            <div class="mt-1 text-2xl font-semibold text-gray-900" data-stat="existing_employees">{{ $stats['existing_employees'] }}</div>
            <div class="mt-2">
                <canvas id="sparklineEmployees" height="40"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 2: Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Hiring & Onboarding Reports -->
        <div class="bg-white rounded-lg shadow-md p-6 flex flex-col min-w-0">
            <h2 class="text-lg font-semibold text-[#0E335D] mb-4">Hiring &amp; Onboarding Reports</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 items-center gap-4 min-w-0">
                <!-- Left Column: Status List -->
                <div class="space-y-4 min-w-0">
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-sm text-gray-600 truncate">Not Recommended</span>
                        <span class="font-semibold text-[#AA1B1B] ml-2">{{ $stats['hiring_stats']['not_recommended'] }}</span>
                    </div>
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-sm text-gray-600 truncate">No Answer</span>
                        <span class="font-semibold text-[#FF7515] ml-2">{{ $stats['hiring_stats']['no_answer'] }}</span>
                    </div>
                    <div class="flex items-center justify-between min-w-0">
                        <span class="text-sm text-gray-600 truncate">Declined</span>
                        <span class="font-semibold text-[#E02F2F] ml-2">{{ $stats['hiring_stats']['declined'] }}</span>
                    </div>

                    <!-- Mini Line Graph for Status Trends -->
                    <div class="mt-4">
                        <canvas id="statusLineChart" height="60"></canvas>
                    </div>
                </div>

                <!-- Right Column: Thicker Doughnut Chart -->
                <div class="flex justify-center min-w-0">
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
                    <p class="text-lg font-semibold text-[#0E335D]" data-stat="classes_conducted">{{ $stats['classes_conducted'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Cancelled Classes</p>
                    <p class="text-lg font-semibold text-red-500" data-stat="cancelled_classes">{{ $stats['cancelled_classes'] }}</p>
                </div>
            </div>

            <div class="flex justify-center">
                <canvas id="glsSchedulingChart" height="160"></canvas>
            </div>
            
            <!-- Additional Class Assignment Stats -->
            <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200">
                <div class="text-center">
                    <p class="text-xs text-gray-500">Fully Assigned</p>
                    <p class="text-sm font-semibold text-green-600" data-stat="fully_assigned_classes">{{ $stats['fully_assigned_classes'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Partially Assigned</p>
                    <p class="text-sm font-semibold text-yellow-600" data-stat="partially_assigned_classes">{{ $stats['partially_assigned_classes'] }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500">Unassigned</p>
                    <p class="text-sm font-semibold text-red-600" data-stat="unassigned_classes">{{ $stats['unassigned_classes'] }}</p>
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
        
        // Initialize filter display
        updateFilterDisplay();
    });

</script>

@endsection