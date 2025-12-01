<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; padding: 20px; color: #111; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header .title { font-size: 18px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: 600; }
        .text-right { text-align: right; }
        .totals { font-weight: 700; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Payslip</div>
        <div class="meta">Pay Period: {{ $periodStart }} to {{ $periodEnd }}</div>
    </div>

    <h3>Hello {{ $tutor->full_name }},</h3>
    <p>Please find your payslip details below:</p>

    @php
        $tenureYears = null;
        $tenureAmount = 0;
        if (!empty($tutor->hired_date_time)) {
            $hired = \Carbon\Carbon::parse($tutor->hired_date_time);
            $tenureYears = round($hired->diffInYears(\Carbon\Carbon::now()));
            $tenureAmount = $tenureYears * 10;
        }
    @endphp

    <ul>
        <li>Gross Pay: ₱{{ number_format($totalEarnings, 2) }}</li>
        <li>Deductions: ₱{{ number_format($deductions ?? 0, 2) }}</li>
        <li>Tenure Bonus: @if(!is_null($tenureYears)) ₱{{ number_format($tenureAmount, 2) }} ({{ $tenureYears }} year(s)) @else — @endif</li>
        @php
            $net = ($totalEarnings ?? 0) + ($tenureAmount ?? 0) - ($deductions ?? 0);
        @endphp
        <li><strong>Net Pay:</strong> ₱{{ number_format($net, 2) }}</li>
    </ul>

    <p>Approved Work:</p>
    <table>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Duration</th>
            <th>Amount </th>
        </tr>
        @foreach ($details as $d)
            @php
                if ($d->start_time && $d->end_time) {
                    $start = \Carbon\Carbon::parse($d->start_time);
                    $end   = \Carbon\Carbon::parse($d->end_time);
                    if ($end->lessThan($start)) $end->addDay();
                    $minutes = $start->diffInMinutes($end);
                    $hours = floor($minutes / 60);
                    $mins  = $minutes % 60;
                    $duration = sprintf('%02d:%02d', $hours, $mins);
                } else {
                    $duration = '—';
                }
                $amount = $d->work_type === 'hourly'
                    ? round(($minutes / 60) * ($d->rate_per_hour ?? 0), 2)
                    : $d->rate_per_class ?? 0;
            @endphp
            <tr>
                <td>{{ $d->day ?? $d->created_at?->format('Y-m-d') }}</td>
                <td>{{ ucfirst($d->work_type) }}</td>
                <td>{{ $duration }}</td>
                <td>{{ number_format($amount, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <p>Thank you.</p>
</body>
</html>
