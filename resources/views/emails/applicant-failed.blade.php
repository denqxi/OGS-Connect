<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status Update</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .email-header.reschedule {
            background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
        }
        .email-header.missed {
            background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
        }
        .email-header.declined {
            background: linear-gradient(135deg, #EF4444 0%, #F87171 100%);
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            background-color: rgba(255, 255, 255, 0.3);
        }
        .email-body {
            padding: 40px 30px;
            color: #333;
        }
        .email-body h2 {
            margin-top: 0;
            font-size: 24px;
        }
        .email-body h2.reschedule {
            color: #3B82F6;
        }
        .email-body h2.missed {
            color: #F59E0B;
        }
        .email-body h2.declined {
            color: #EF4444;
        }
        .email-body p {
            line-height: 1.6;
            font-size: 16px;
            color: #555;
        }
        .info-box {
            border-left: 4px solid #3B82F6;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box.reschedule {
            background-color: #eff6ff;
            border-left-color: #3B82F6;
        }
        .info-box.missed {
            background-color: #fffbeb;
            border-left-color: #F59E0B;
        }
        .info-box.declined {
            background-color: #fef2f2;
            border-left-color: #EF4444;
        }
        .info-box strong {
            display: block;
            margin-bottom: 5px;
        }
        .schedule-box {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border: 2px solid #3B82F6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .schedule-box h3 {
            color: #1E40AF;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .schedule-time {
            font-size: 20px;
            font-weight: bold;
            color: #1E40AF;
            margin: 10px 0;
        }
        .email-footer {
            background-color: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        .email-footer p {
            margin: 5px 0;
        }
        .highlight {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header - Dynamic based on fail reason -->
        <div class="email-header 
            @if($failReason === 're_schedule') reschedule
            @elseif($failReason === 'no_answer') missed
            @else declined
            @endif">
            <div class="status-icon">
                @if($failReason === 're_schedule')
                    📅
                @elseif($failReason === 'no_answer')
                    ⚠️
                @else
                    ℹ️
                @endif
            </div>
            <h1>
                @if($failReason === 're_schedule')
                    Interview Rescheduled
                @elseif($failReason === 'no_answer')
                    Missed Interview
                @elseif($failReason === 'declined')
                    Application Update
                @else
                    Application Status
                @endif
            </h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2 class="
                @if($failReason === 're_schedule') reschedule
                @elseif($failReason === 'no_answer') missed
                @else declined
                @endif">
                Hello {{ $applicantName }},
            </h2>
            
            <!-- Re-schedule message -->
            @if($failReason === 're_schedule')
                <p>
                    This is to inform you that your <strong class="highlight">{{ ucfirst($phase) }}</strong> 
                    interview has been rescheduled.
                </p>

                @if($newSchedule)
                <div class="schedule-box">
                    <h3>📆 New Interview Schedule</h3>
                    <div class="schedule-time">
                        {{ \Carbon\Carbon::parse($newSchedule)->format('l, F j, Y') }}
                    </div>
                    <div class="schedule-time">
                        {{ \Carbon\Carbon::parse($newSchedule)->format('g:i A') }}
                    </div>
                </div>
                @endif

                <p>
                    Please ensure you are available at the scheduled time. We look forward to speaking with you!
                </p>

            <!-- Missed/No answer message -->
            @elseif($failReason === 'no_answer')
                <p>
                    We attempted to contact you for your <strong class="highlight">{{ ucfirst($phase) }}</strong> 
                    interview, but we were unable to reach you.
                </p>

                <div class="info-box missed">
                    <strong>What happens next?</strong>
                    Our team will attempt to contact you again. Please ensure your phone is accessible 
                    and that you're available during your scheduled interview time.
                </div>

                @if($newSchedule)
                <div class="schedule-box">
                    <h3>📆 Next Attempt Schedule</h3>
                    <div class="schedule-time">
                        {{ \Carbon\Carbon::parse($newSchedule)->format('l, F j, Y \a\t g:i A') }}
                    </div>
                </div>
                @endif

                <p>
                    If you need to reschedule, please contact our HR team as soon as possible.
                </p>

            <!-- Declined message -->
            @elseif($failReason === 'declined')
                <p>
                    We wanted to inform you about the status of your application for the 
                    <strong class="highlight">{{ ucfirst($phase) }}</strong> phase.
                </p>

                <div class="info-box declined">
                    <strong>Application Status:</strong>
                    Your application has been marked as declined.
                </div>

                <p>
                    We appreciate your interest in OGS Connect and the time you invested in the application process.
                    We wish you all the best in your future endeavors.
                </p>

            <!-- Not recommended message -->
            @elseif($failReason === 'not_recommended')
                <p>
                    Thank you for participating in the <strong class="highlight">{{ ucfirst($phase) }}</strong> 
                    phase of our hiring process.
                </p>

                <div class="info-box declined">
                    <strong>Application Status:</strong>
                    After careful consideration, we have decided not to proceed with your application at this time.
                </div>

                <p>
                    We appreciate the time and effort you put into the application process. 
                    We encourage you to apply for future positions that match your skills and experience.
                </p>
            @endif

            @if($interviewer)
            <div class="info-box 
                @if($failReason === 're_schedule') reschedule
                @elseif($failReason === 'no_answer') missed
                @else declined
                @endif">
                <strong>Contact Person:</strong>
                {{ $interviewer }}
            </div>
            @endif

            @if($notes)
            <div class="info-box 
                @if($failReason === 're_schedule') reschedule
                @elseif($failReason === 'no_answer') missed
                @else declined
                @endif">
                <strong>Additional Notes:</strong>
                {{ $notes }}
            </div>
            @endif

            <p style="margin-top: 30px;">
                If you have any questions or concerns, please feel free to contact our HR team.
            </p>

            <p>
                Best regards,<br>
                <strong>OGS Connect Hiring Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>OGS Connect</strong></p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} OGS Connect. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
