@extends('layouts.app')
@section('title', 'OGS Connect/Payroll')

@section('content')
    @include('layouts.header', ['pageTitle' => 'Payroll'])



    <div class="bg-white border-b border-gray-200 px-4 md:px-6 rounded-xl shadow-sm mb-6">


        <nav class="flex overflow-x-auto md:space-x-8 no-scrollbar">
            <a href="{{ route('payroll.index', ['tab' => 'payrolls']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
               {{ request('tab', 'payrolls') == 'payrolls' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
               font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-user-clock"></i>
                <span class="hidden sm:inline">Payroll</span>
            </a>
            
            <a href="{{ route('payroll.index', ['tab' => 'payroll']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
               {{ request('tab') == 'payroll' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
               font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-user-clock"></i>
                <span class="hidden sm:inline">Class Verification</span>
            </a>

            <a href="{{ route('payroll.index', ['tab' => 'history']) }}"
                class="flex-shrink-0 py-3 md:py-4 px-2 md:px-1 
               {{ request('tab') == 'history' || request('tab') == 'payroll-history' ? 'border-b-2 border-[#0E335D] text-[#0E335D]' : 'text-[#0E335D] hover:text-[#0E335D]/70' }} 
               font-medium text-sm md:text-base flex items-center space-x-2">
                <i class="fas fa-history"></i>
                <span class="hidden sm:inline">History</span>
            </a>
        </nav>


    </div>


    <div>
        <div class="max-w-full mx-auto">
            <div class="bg-white rounded-lg shadow-sm p-0 md:p-0">

                @if (request('tab', 'payrolls') == 'payrolls')
                    @include('payroll.partials.payrolls')

                @elseif(request('tab') == 'payroll')
                    @include('payroll.partials.tutor-payroll')

                @elseif(request('tab') == 'history' || request('tab') == 'payroll-history')

                    <!-- Combined History Header + Filters -->
                    <div class="px-3 py-2 border-b border-gray-200">
                        <form method="GET" action="{{ route('payroll.index') }}" class="mb-0 w-full">
                            <input type="hidden" name="tab" id="history_tab_input" value="{{ request('tab', 'history') }}">
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="flex items-center gap-4">
                                    <h3 class="text-sm font-medium text-gray-700 whitespace-nowrap">Type of History</h3>

                                    <div class="relative">
                                        <select
                                            id="historyTypeSelect"
                                            name="tab"
                                            onchange="this.form.submit()"
                                            class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8 text-sm focus:outline-none focus:ring-1 focus:ring-black-500"
                                        >
                                            <option value="history" {{ request('tab', 'history') == 'history' ? 'selected' : '' }}>Approval History</option>
                                            <option value="payroll-history" {{ request('tab') == 'payroll-history' ? 'selected' : '' }}>Payroll History</option>
                                        </select>

                                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Filters (single row) -->
                                <div class="flex items-center space-x-2 ml-auto">
                                    <input type="text" name="tutor_name" id="tutor_name" value="{{ request('tutor_name') }}" placeholder="Search tutor" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">

                                    <label for="date_from" class="text-sm font-medium text-gray-700">From:</label>
                                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">

                                    <label for="date_to" class="text-sm font-medium text-gray-700">To:</label>
                                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" min="{{ request('date_from') }}" class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-600 bg-white">

                                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">Apply</button>

                                    @if(request()->hasAny(['tutor_name', 'date_from', 'date_to']))
                                        <a href="{{ route('payroll.index', ['tab' => request('tab', 'history')]) }}" class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">Clear</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- History Content -->
                    @if(request('tab') == 'history')
                        @include('payroll.partials.history')
                    @elseif(request('tab') == 'payroll-history')
                        @include('payroll.partials.payroll-history')
                    @endif

                @endif

            </div>
        </div>

        <script>
            // Date range validation for shared filters
            document.addEventListener('DOMContentLoaded', function() {
                const fromDate = document.getElementById('date_from');
                const toDate = document.getElementById('date_to');

                if (fromDate && toDate) {
                    fromDate.addEventListener('change', function() {
                        if (this.value) {
                            toDate.min = this.value;
                            if (toDate.value && toDate.value < this.value) {
                                toDate.value = '';
                            }
                        }
                    });

                    toDate.addEventListener('change', function() {
                        if (this.value) {
                            fromDate.max = this.value;
                            if (fromDate.value && fromDate.value > this.value) {
                                fromDate.value = '';
                            }
                        }
                    });

                    // Initialize min/max on page load if values exist
                    if (fromDate.value) {
                        toDate.min = fromDate.value;
                    }
                    if (toDate.value) {
                        fromDate.max = toDate.value;
                    }
                }
            });
            window.printPayslip = function() {
                // Get tutor_id from the modal
                const tutorIdElement = document.querySelector('[data-tutor-id]');
                const tutorId = tutorIdElement ? tutorIdElement.getAttribute('data-tutor-id') : null;

                // Log the PDF/Print action
                if (tutorId) {
                    const payPeriod = new Date().toISOString().split('-').slice(0, 2).join('-');

                    console.log('Logging PDF export with tutor_id:', tutorId);
                    fetch('/payroll/log-pdf', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                tutor_id: parseInt(tutorId),
                                pay_period: payPeriod,
                                submission_type: 'pdf'
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log('PDF logging response:', data);
                            if (!data.success) {
                                console.error('Failed to log PDF:', data.message);
                            }
                        })
                        .catch(err => console.error('Error logging PDF:', err));
                }

                // Open print dialog
                var content = document.getElementById('payslipContent').innerHTML;
                var printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Payslip</title>');
                printWindow.document.write(
                    '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;color:#111}.header{display:flex;justify-content:space-between;align-items:center}.header .title{font-size:18px;font-weight:700}.header .meta{font-size:12px;color:#666}table{width:100%;border-collapse:collapse;margin-top:8px}th,td{border:1px solid #e5e7eb;padding:8px;text-align:left}th{background:#f3f4f6;font-weight:600}.text-right{text-align:right}.totals{font-weight:700}</style>'
                );
                printWindow.document.write('</head><body>');
                printWindow.document.write(content);
                printWindow.document.write('</body></html>');
                printWindow.document.close();
                printWindow.focus();
                setTimeout(function() {
                    printWindow.print();
                }, 250);
            };

            // notification ug nasent ang paylsip, yey!
            function showPayslipModal(message, success = true) {
                var overlay = document.getElementById('payslipModalOverlay');
                var box = document.getElementById('payslipModalBox');
                var msg = document.getElementById('payslipModalMessage');
                var icon = document.getElementById('payslipModalIcon');

                if (!overlay || !box) return alert(message);

                // Set message
                msg.textContent = message || '';

                // Set icon and colors
                if (success) {
                    icon.innerHTML = '✔';
                    box.classList.remove('border-red-400', 'bg-red-50', 'text-red-700');
                    box.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-800');
                } else {
                    icon.innerHTML = '⚠';
                    box.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800');
                    box.classList.add('border-red-400', 'bg-red-50', 'text-red-700');
                }

                box.classList.add('animate-fadeIn', 'scale-105');
                setTimeout(() => box.classList.remove('scale-105'), 300);

                overlay.classList.remove('hidden');

                // Auto-hide after 3 seconds
                clearTimeout(window._payslipModalTimeout);
                window._payslipModalTimeout = setTimeout(function() {
                    overlay.classList.add('hidden');
                }, 3000);
            }


            window.emailPayslip = function(tutorID) {
                // First, log the email submission
                const payPeriod = new Date().toISOString().split('-').slice(0, 2).join('-');

                // Get tutor email from the modal if available
                const tutorEmailElement = document.querySelector('[data-tutor-email]');
                const tutorEmail = tutorEmailElement ? tutorEmailElement.getAttribute('data-tutor-email') : null;

                if (tutorEmail) {
                    console.log('Logging email submission to payroll history');
                    fetch('/payroll/log-email', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                tutor_id: tutorID,
                                pay_period: payPeriod,
                                recipient_email: tutorEmail
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log('Email logging response:', data);
                            if (!data.success) {
                                console.error('Failed to log email:', data.message);
                            }
                        })
                        .catch(err => console.error('Error logging email:', err));
                }

                // Then send the email
                fetch('/payroll/tutor/' + tutorID + '/send-email', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            showPayslipModal(data.message || 'Payslip emailed successfully', true);
                        } else {
                            showPayslipModal('Failed to send payslip: ' + (data.message || 'Unknown error'), false);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showPayslipModal('An error occurred while sending the payslip.', false);
                    });
            };

            // Open Salary History Modal
            window.openSalaryHistory = async function(tutorID) {
                try {
                    // Fetch salary history data
                    const response = await fetch(`/payroll/tutor/${tutorID}/salary-history`);
                    const data = await response.json();

                    if (!data.success) {
                        alert('Failed to fetch salary history: ' + data.message);
                        return;
                    }

                    // Create modal
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
                    modal.id = 'salaryHistoryModal';

                    let tableRows = '';
                    if (data.history && data.history.length > 0) {
                        data.history.forEach(record => {
                            const payPeriod = record.pay_period || 'N/A';
                            const totalAmount = record.total_amount ? '₱' + parseFloat(record.total_amount)
                                .toFixed(2) : 'N/A';
                            const totalHours = record.total_hours ? parseFloat(record.total_hours).toFixed(2) +
                                ' hrs' : '0.00 hrs';
                            const status = record.status || 'N/A';
                            const submittedAt = record.submitted_at ? new Date(record.submitted_at)
                                .toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                }) : 'N/A';

                            const statusColors = {
                                'finalized': 'bg-indigo-100 text-indigo-800',
                                'sent': 'bg-green-100 text-green-800',
                                'pending': 'bg-yellow-100 text-yellow-800',
                                'failed': 'bg-red-100 text-red-800',
                                'draft': 'bg-gray-100 text-gray-800'
                            };
                            const statusClass = statusColors[status] || 'bg-gray-100 text-gray-800';
                            const statusLabel = status === 'finalized' ? 'Finalized' : status.charAt(0)
                                .toUpperCase() + status.slice(1);

                            tableRows += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-700">${payPeriod}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">${totalAmount}</td>
                            <td class="px-6 py-3 text-sm text-gray-700">${totalHours}</td>
                            <td class="px-6 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-medium ${statusClass}">
                                    ${statusLabel}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700">${submittedAt}</td>
                        </tr>
                    `;
                        });
                    } else {
                        tableRows =
                            '<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No salary history found</td></tr>';
                    }

                    modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-96 flex flex-col">
                    <!-- Header -->
                    <div class="border-b border-gray-200 p-6 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">${data.tutor.name}</h2>
                            <p class="text-sm text-gray-600">${data.tutor.account}</p>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Salary History</h3>
                    </div>

                    <!-- Table -->
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200 sticky top-0">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Pay Period</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Total Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Total Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Date Processed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                ${tableRows}
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Info -->
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 flex justify-between items-center">
                        <span class="text-sm text-gray-600">
                            Showing ${data.pagination.from || 0} to ${data.pagination.to || 0} of ${data.pagination.total} records
                        </span>
                        <button onclick="document.getElementById('salaryHistoryModal').remove()" class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            `;

                    document.body.appendChild(modal);

                    // Close on backdrop click
                    modal.onclick = (e) => {
                        if (e.target === modal) modal.remove();
                    };

                } catch (error) {
                    console.error('Error opening salary history:', error);
                    alert('An error occurred while fetching salary history');
                }
            };
        </script>

        <!-- Modal overlay (Tailwind) -->
        <div id="payslipModalOverlay"
            class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 py-6 pointer-events-none">
            <div id="payslipModalBox"
                class="pointer-events-auto w-full max-w-md bg-white border rounded-lg shadow-lg p-4 flex items-start gap-3 border-emerald-500">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center"
                    id="payslipModalIcon">✔</div>
                <div class="flex-1">
                    <div id="payslipModalMessage" class="text-sm text-gray-800">Payslip emailed successfully</div>
                </div>
                <button onclick="document.getElementById('payslipModalOverlay').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 ml-3">✕</button>
            </div>
        </div>

    @endsection
