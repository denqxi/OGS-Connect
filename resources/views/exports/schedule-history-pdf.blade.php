<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Schedule History Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0E335D;
        }
        
        .header h1 {
            font-size: 24px;
            color: #0E335D;
            margin-bottom: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header .meta-info {
            font-size: 11px;
            color: #666;
            margin-top: 8px;
        }
        
        .header .meta-info strong {
            color: #0E335D;
        }
        
        .summary-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #0E335D;
        }
        
        .summary-section h2 {
            font-size: 14px;
            color: #0E335D;
            margin-bottom: 10px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .summary-item {
            display: table-cell;
            padding: 8px;
            text-align: center;
            background: white;
            border-radius: 6px;
            margin: 0 5px;
        }
        
        .summary-item .label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #0E335D;
            margin-top: 5px;
        }
        
        .schedule-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            page-break-inside: avoid;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .schedule-card-header {
            background: linear-gradient(135deg, #0E335D 0%, #1e5a8e 100%);
            color: white;
            padding: 12px 15px;
            border-radius: 6px 6px 0 0;
            margin: -15px -15px 15px -15px;
            font-weight: bold;
        }
        
        .schedule-card-header .date-badge {
            font-size: 14px;
            display: inline-block;
            margin-right: 15px;
        }
        
        .schedule-card-header .school-name {
            font-size: 12px;
            opacity: 0.95;
        }
        
        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .details-row {
            display: table-row;
        }
        
        .details-row .label-col {
            display: table-cell;
            width: 30%;
            padding: 8px;
            font-weight: bold;
            color: #4b5563;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .details-row .value-col {
            display: table-cell;
            width: 70%;
            padding: 8px;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .tutor-section {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-left: 4px solid #10b981;
            padding: 12px;
            margin-top: 12px;
            border-radius: 6px;
        }
        
        .tutor-section h3 {
            font-size: 11px;
            color: #065f46;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .tutor-info {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        
        .tutor-row {
            display: table-row;
        }
        
        .tutor-row .tutor-label {
            display: table-cell;
            width: 35%;
            padding: 6px 8px;
            font-weight: bold;
            font-size: 9px;
            color: #059669;
            text-transform: uppercase;
        }
        
        .tutor-row .tutor-name {
            display: table-cell;
            width: 65%;
            padding: 6px 8px;
            color: #064e3b;
            font-size: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #10b981;
            color: white;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 5px;
            vertical-align: middle;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #0E335D;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .footer .company-name {
            font-size: 12px;
            font-weight: bold;
            color: #0E335D;
            margin-bottom: 5px;
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 20px 0;
        }
        
        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header">
        <h1>📅 Schedule History Report</h1>
        <div class="meta-info">
            <strong>Generated:</strong> {{ $generatedAt }} | 
            <strong>Total Schedules:</strong> {{ $totalSchedules }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h2>📊 Report Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Schedules</div>
                <div class="value">{{ $totalSchedules }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Status</div>
                <div class="value">✓ Fully Assigned</div>
            </div>
            <div class="summary-item">
                <div class="label">Document Type</div>
                <div class="value">Official</div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Schedule Cards -->
    @foreach($schedules as $index => $schedule)
        <div class="schedule-card">
            <div class="schedule-card-header">
                @php
                    $dateObj = \Carbon\Carbon::parse($schedule->date);
                    $formattedDate = $dateObj->format('F j, Y');
                    $dayName = !empty($schedule->day) ? $schedule->day : $dateObj->format('l');
                @endphp
                <span class="date-badge">
                    &#128197; {{ $formattedDate }} - {{ $dayName }}
                </span>
                <span class="school-name">&#127979; {{ $schedule->school ?? 'N/A' }}</span>
            </div>

            <!-- Schedule Details -->
            <div class="details-grid">
                <div class="details-row">
                    <div class="label-col">📅 Date</div>
                    <div class="value-col">
                        <strong>{{ $formattedDate }}</strong> ({{ $dayName }})
                    </div>
                </div>
                
                <div class="details-row">
                    <div class="label-col">⏰ Time</div>
                    <div class="value-col">
                        @if($schedule->time)
                            {{ \Carbon\Carbon::parse($schedule->time)->format('g:i A') }}
                            @if($schedule->duration)
                                - {{ \Carbon\Carbon::parse($schedule->time)->addMinutes($schedule->duration)->format('g:i A') }}
                                ({{ $schedule->duration }} minutes)
                            @endif
                        @else
                            Not Specified
                        @endif
                    </div>
                </div>
                
                <div class="details-row">
                    <div class="label-col">📚 Class</div>
                    <div class="value-col">{{ $schedule->class ?? 'N/A' }}</div>
                </div>
                
                <div class="details-row">
                    <div class="label-col">🏫 School</div>
                    <div class="value-col">{{ $schedule->school ?? 'N/A' }}</div>
                </div>
                
                <div class="details-row">
                    <div class="label-col">📊 Status</div>
                    <div class="value-col">
                        <span class="status-badge">✓ Fully Assigned</span>
                    </div>
                </div>

                @if($schedule->finalized_at)
                <div class="details-row">
                    <div class="label-col">✅ Finalized At</div>
                    <div class="value-col">{{ \Carbon\Carbon::parse($schedule->finalized_at)->format('M j, Y g:i A') }}</div>
                </div>
                @endif
            </div>

            <!-- Tutor Assignment Section -->
            <div class="tutor-section">
                <h3>👥 Assigned Tutors</h3>
                <div class="tutor-info">
                    <div class="tutor-row">
                        <div class="tutor-label">🎓 Main Tutor</div>
                        <div class="tutor-name">
                            @if(!empty(trim($schedule->main_tutor_name ?? '')))
                                {{ $schedule->main_tutor_name }}
                            @else
                                <em style="color: #999;">Not Assigned</em>
                            @endif
                        </div>
                    </div>
                    <div class="tutor-row">
                        <div class="tutor-label">🔄 Backup Tutor</div>
                        <div class="tutor-name">
                            @if(!empty(trim($schedule->backup_tutor_name ?? '')))
                                {{ $schedule->backup_tutor_name }}
                            @else
                                <em style="color: #999;">Not Assigned</em>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(($index + 1) % 3 == 0 && ($index + 1) < $totalSchedules)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach

    <!-- Footer -->
    <div class="footer">
        <div class="company-name">🎓 OGS Connect - Schedule Management System</div>
        <p>This is an official document. All information is confidential and for internal use only.</p>
        <p style="margin-top: 5px;">© {{ now()->format('Y') }} OGS Connect. All rights reserved.</p>
    </div>
</body>
</html>
