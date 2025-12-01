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
            <button type="button" onclick="printPayslip()" class="px-3 py-1 bg-slate-700 text-white rounded text-xs">Print
                / Save PDF</button>
            <button type="button" onclick="emailPayslip('{{ $tutor->tutorID }}')"
                class="px-3 py-1 bg-emerald-600 text-white rounded text-xs">Email Payslip</button>
            <button type="button" onclick="closeTutorSummary()" class="text-gray-500 text-2xl">&times;</button>
        </div>
    </div>

        <div id="payslipContent" class="mt-4 bg-white shadow-sm rounded p-6 text-sm text-gray-800"
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

    {{-- <script>
        function printPayslip() {
            var content = document.getElementById('payslipContent').innerHTML;
            var printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Payslip</title>');
            printWindow.document.write(
                '<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;color:#111}.header{display:flex;justify-content:space-between;align-items:center}.header .title{font-size:18px;font-weight:700}.header .meta{font-size:12px;color:#666}table{width:100%;border-collapse:collapse;margin-top:8px}th,td{border:1px solid #e5e7eb;padding:8px;text-align:left}th{background:#f3f4f6;font-weight:600} .text-right{text-align:right} .totals{font-weight:700}</style>'
            );
            printWindow.document.write('</head><body>');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function() {
                printWindow.print();
            }, 250);
        }

        function emailPayslip(tutorID) {
            var tutorName = `{!! addslashes($tutor->full_name ?? $tutor->tusername) !!}`;
            var tutorEmail = `{!! $tutor->email ?? ($tutor->account?->email ?? '') !!}`;
            if (!tutorEmail) {
                alert('No email address available for this tutor.');
                return;
            }
            var subject = encodeURIComponent('Payslip for ' + tutorName);
            var bodyLines = [];
            bodyLines.push('Hello ' + tutorName + ',');
            bodyLines.push('');
            bodyLines.push('Please find your payslip below:');
            bodyLines.push('Gross Pay: ₱' + '{{ number_format($total_earnings, 2) }}');
            bodyLines.push('Deductions: ₱' + '{{ number_format($deductions ?? 0, 2) }}');
            bodyLines.push('Net Pay: ₱' + '{{ number_format($total_earnings - ($deductions ?? 0), 2) }}');
            bodyLines.push('');
            bodyLines.push('You can view the full payslip here: ' + window.location.origin +
                '{{ url('/payroll/tutor/' . urlencode($tutor->tutorID) . '/summary') }}');
            var body = encodeURIComponent(bodyLines.join('\n'));
            window.location.href = 'mailto:' + tutorEmail + '?subject=' + subject + '&body=' + body;
        }
    </script> --}}
</div>
