<div class="p-6">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-lg font-semibold" data-tutor-name="{{ $tutor->full_name ?? $tutor->tusername }}">
                Payslip for {{ $tutor->full_name ?? $tutor->tusername }}
            </h3>
            <div class="text-sm text-gray-600 mt-1" data-tutor-email="{{ $tutor->email ?? ($tutor->account?->email ?? '') }}">
                ID: {{ $tutor->tutorID }}
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="finalizePayroll('{{ $tutor->tutorID }}', '{{ $period_start }}', '{{ $period_end }}')"
                class="px-3 py-1 bg-orange-600 text-white rounded text-xs hover:bg-orange-700">Finalize & Lock Payroll</button>
            <button type="button" onclick="printPayslip()" class="px-3 py-1 bg-slate-700 text-white rounded text-xs">Print
                / Save PDF</button>
            <button type="button" onclick="emailPayslip({{ $tutor->tutor_id }})"
                class="px-3 py-1 bg-emerald-600 text-white rounded text-xs">Email Payslip</button>
            <button type="button" onclick="closeTutorSummary()" class="text-gray-500 text-2xl">&times;</button>
        </div>
    </div>

        <div id="payslipContent" class="mt-4 bg-white shadow-sm rounded p-6 text-sm text-gray-800"
            data-tutor-id="{{ $tutor->tutor_id }}"
            data-tutor-email="{{ $tutor->email ?? ($tutor->account?->email ?? '') }}"
            data-tutor-name="{{ $tutor->full_name ?? $tutor->tusername }}">
        <div class="header flex items-center justify-between mb-4">
            <div>
                <div class="text-2xl font-bold">OGS Connect</div>
                <div class="text-xs text-gray-500">Payroll & Tutor Services</div>
            </div>
            <div class="text-right text-xs text-gray-500">
                <div>Payslip Date: {{ now()->format('Y-m-d') }}</div>
                <div>Generated: {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>
        {{--                 <div class="mt-2 text-xs text-gray-500">Approved work: <span
                        class="font-medium">{{ $total_items }}</span></div> --}}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <div class="text-xs text-gray-500">Tutor</div>
        <div class="font-medium">{{ $tutor->full_name ?? $tutor->tusername }}</div>
        <div class="text-xs text-gray-500">ID: {{ $tutor->tutorID }}</div>
        <div class="text-xs text-gray-500">Email: {{ $tutor->email ?? ($tutor->account?->email ?? 'N/A') }}</div>
        @php
            $tenureYears = null;
            $tenureAmount = 0;
            if (!empty($tutor->hired_date_time)) {
                $hired = \Carbon\Carbon::parse($tutor->hired_date_time);
                $tenureYears = round($hired->diffInYears(\Carbon\Carbon::now()));
                $tenureAmount = $tenureYears * 10; // ₱10 per year
            }
        @endphp
        <div class="text-xs text-gray-500">Tenure: <span class="font-medium">
            @if(!is_null($tenureYears))
                {{ $tenureYears }} year(s)
            @else
                —
            @endif
        </span></div>
    </div>

    <div class="text-right">
        @php
            $totalMinutes = 0;
            foreach ($details as $d) {
                if ($d->start_time && $d->end_time) {
                    $start = \Carbon\Carbon::parse($d->start_time);
                    $end   = \Carbon\Carbon::parse($d->end_time);
                    if ($end->lessThan($start)) $end->addDay();
                    $totalMinutes += $start->diffInMinutes($end);
                }
            }
            $totalHours = floor($totalMinutes / 60);
            $totalMins  = $totalMinutes % 60;
            $totalHoursFormatted = sprintf('%02d:%02d', $totalHours, $totalMins);
        @endphp

        <div class="text-xs text-gray-500">Pay Period</div>
        <div class="font-medium">{{ $period_start ?? '—' }} to {{ $period_end ?? '—' }}</div>

        <div class="mt-2 text-xs text-gray-500">Approved Work: <span class="font-medium">{{ $total_items }}</span></div>

        <div class="text-xs text-gray-500">Total Hours: <span class="font-medium">{{ $totalHoursFormatted }}</span></div>
    </div>
</div>


        <div class="mb-4">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-3 py-2">Date</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2">Duration</th>
                        <th class="px-3 py-2 text-right">Amount (₱)</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($details as $d)
                        @php
                            // Individual duration
                            if ($d->start_time && $d->end_time) {
                                $start = \Carbon\Carbon::parse($d->start_time);
                                $end = \Carbon\Carbon::parse($d->end_time);

                                if ($end->lessThan($start)) {
                                    $end->addDay();
                                }

                                $minutes = $start->diffInMinutes($end);
                                $hours = floor($minutes / 60);
                                $mins = $minutes % 60;

                                $durationFormatted = sprintf('%02d:%02d', $hours, $mins);
                            } else {
                                $durationFormatted = '—';
                                $minutes = 0;
                            }

                            // Amount
                            $amount =
                                $d->work_type === 'hourly'
                                    ? round(($minutes / 60) * ($d->rate_per_hour ?? 0), 2)
                                    : $d->rate_per_class ?? 0;
                        @endphp

                        <tr>
                            <td class="px-3 py-2">{{ $d->day ?? $d->created_at?->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">{{ ucfirst($d->work_type) }}</td>
                            <td class="px-3 py-2">{{ $durationFormatted }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($amount, 2) }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                No approved work details found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div></div>
            <div class="p-4 bg-gray-50 rounded">
                <div class="flex justify-between text-sm">
                    <div>Gross Pay</div>
                    <div class="font-medium" data-gross-pay="{{ number_format($total_earnings, 2) }}">
                        ₱{{ number_format($total_earnings, 2) }}
                    </div>
                </div>
                <div class="flex justify-between text-sm">
                    <div>Deductions</div>
                    <div class="font-medium" data-deductions="{{ number_format($deductions ?? 0, 2) }}">
                        ₱{{ number_format($deductions ?? 0, 2) }}
                    </div>
                </div>
                <div class="flex justify-between text-sm">
                    <div>Tenure Bonus</div>
                    <div class="font-medium" data-tenure-amount="{{ number_format($tenureAmount ?? 0, 2) }}">
                        ₱{{ number_format($tenureAmount ?? 0, 2) }}
                    </div>
                </div>
                <hr class="my-2" />
                <div class="flex justify-between text-base">
                    <div class="font-semibold">Net Pay</div>
                    @php
                        $netPay = ($total_earnings ?? 0) + ($tenureAmount ?? 0) - ($deductions ?? 0);
                    @endphp
                    <div class="font-semibold" data-net-pay="{{ number_format($netPay, 2) }}">
                        ₱{{ number_format($netPay, 2) }}
                    </div>
                </div>

            </div>
        </div>

        <div class="mt-6 text-xs text-gray-500">This payslip is computer-generated and does not require a signature.
        </div>
    </div>

    <script>
        // Store tutor data for debugging
        const TUTOR_DATA = {
            tutor_id: {{ $tutor->tutor_id }},
            tutor_name: '{{ $tutor->full_name ?? $tutor->tusername }}',
            tutor_email: '{{ $tutor->email ?? ($tutor->account?->email ?? '') }}'
        };
        
        console.log('TUTOR_DATA initialized:', TUTOR_DATA);

        function printPayslip() {
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
            
            // Log PDF/Print action
            const payPeriod = '{{ now()->format('Y-m') }}';
            
            console.log('Logging PDF export with TUTOR_DATA:', TUTOR_DATA);
            console.log('tutor_id value:', TUTOR_DATA.tutor_id, 'type:', typeof TUTOR_DATA.tutor_id);
            
            if (!TUTOR_DATA.tutor_id) {
                console.error('ERROR: tutor_id is not set!');
                alert('Error: Tutor ID is missing. Please refresh the page.');
                return;
            }
            
            fetch('{{ route("payroll.log-pdf") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    tutor_id: TUTOR_DATA.tutor_id,
                    pay_period: payPeriod,
                    submission_type: 'pdf'
                })
            })
            .then(response => {
                console.log('PDF log response status:', response.status);
                if (!response.ok) {
                    console.error('Response not OK, status:', response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('PDF log response:', data);
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                    alert('Payroll logging failed - Validation errors:\n' + JSON.stringify(data.errors, null, 2));
                }
                if (!data.success) {
                    console.error('Logging failed:', data.message);
                    alert('Failed to log payroll submission:\n' + data.message);
                } else {
                    console.log('Payroll logged successfully');
                }
            })
            .catch(error => console.error('Error logging PDF export:', error));
            
            setTimeout(function() {
                printWindow.print();
            }, 250);
        }

        function emailPayslip(tutorID) {
            var tutorName = TUTOR_DATA.tutor_name;
            var tutorEmail = TUTOR_DATA.tutor_email;
            
            if (!tutorEmail) {
                alert('No email address available for this tutor.');
                return;
            }
            
            console.log('Logging email with TUTOR_DATA:', TUTOR_DATA);
            console.log('tutor_id value:', TUTOR_DATA.tutor_id, 'type:', typeof TUTOR_DATA.tutor_id);
            
            if (!TUTOR_DATA.tutor_id) {
                console.error('ERROR: tutor_id is not set!');
                alert('Error: Tutor ID is missing. Please refresh the page.');
                return;
            }
            
            // Log email submission
            const payPeriod = '{{ now()->format('Y-m') }}';
            
            const emailLogPayload = {
                tutor_id: TUTOR_DATA.tutor_id,
                pay_period: payPeriod,
                recipient_email: tutorEmail
            };
            
            console.log('Logging email with payload:', JSON.stringify(emailLogPayload));
            
            fetch('{{ route("payroll.log-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(emailLogPayload)
            })
            .then(response => {
                console.log('Email log response status:', response.status);
                if (!response.ok) {
                    console.error('Response not OK, status:', response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Email log response:', data);
                if (data.errors) {
                    console.error('Validation errors:', data.errors);
                    alert('Payroll logging failed - Validation errors:\n' + JSON.stringify(data.errors, null, 2));
                }
                if (!data.success) {
                    console.error('Logging failed:', data.message);
                    alert('Failed to log payroll submission:\n' + data.message);
                } else {
                    console.log('Payroll logged successfully');
                }
            })
            .catch(error => console.error('Error logging email:', error));
            
            // Actually send the email via the server
            console.log('Sending payslip email to:', tutorEmail);
            
            fetch('/payroll/tutor/' + TUTOR_DATA.tutor_id + '/send-email', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
            })
            .then(res => res.json())
            .then(data => {
                console.log('Send email response:', data);
                if (data.success) {
                    alert('Payslip emailed successfully to ' + tutorEmail);
                } else {
                    alert('Failed to send payslip: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error sending payslip:', err);
                alert('An error occurred while sending the payslip.');
            });
        }
    </script>
</div>