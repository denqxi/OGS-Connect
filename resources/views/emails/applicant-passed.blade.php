<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations - You've Passed!</title>
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
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 100%);
            padding: 30px 20px;
            text-align: center;
            color: white;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background-color: #65DB7F;
            border-radius: 50%;
            margin: 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        .email-body {
            padding: 40px 30px;
            color: #333;
        }
        .email-body h2 {
            color: #1E40AF;
            margin-top: 0;
            font-size: 24px;
        }
        .email-body p {
            line-height: 1.6;
            font-size: 16px;
            color: #555;
        }
        .info-box {
            background-color: #f0f7ff;
            border-left: 4px solid #1E40AF;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box strong {
            color: #1E40AF;
            display: block;
            margin-bottom: 5px;
        }
        .credentials-box {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
        }
        .credentials-box h3 {
            color: #d97706;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .credential-item {
            background-color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }
        .credential-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .credential-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .next-steps {
            background-color: #f0fdf4;
            border-left: 4px solid #65DB7F;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .next-steps h3 {
            color: #16a34a;
            margin-top: 0;
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
            color: #1E40AF;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="success-icon">✓</div>
            <h1>Congratulations {{ $applicantName }}!</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <h2>You've Successfully Passed the {{ ucfirst($phase) }} Phase! 🎉</h2>
            
            <p>
                We are pleased to inform you that you have successfully completed the 
                <strong class="highlight">{{ ucfirst($phase) }}</strong> phase of our hiring process.
            </p>

            @if($interviewer)
            <div class="info-box">
                <strong>Assessed By:</strong>
                {{ $interviewer }}
            </div>
            @endif

            @if($notes)
            <div class="info-box">
                <strong>Feedback:</strong>
                {{ $notes }}
            </div>
            @endif

            <!-- Show credentials if this is the onboarding pass (becoming an employee) -->
            @if($companyEmail && $password)
            <div class="credentials-box">
                <h3>🔐 Your Employee Credentials</h3>
                <p style="margin-bottom: 15px; color: #666;">
                    Welcome to the team! Below are your login credentials for the OGS Connect system.
                </p>
                
                <div class="credential-item">
                    <div class="credential-label">Company Email</div>
                    <div class="credential-value">{{ $companyEmail }}</div>
                </div>
                
                <div class="credential-item">
                    <div class="credential-label">Temporary Password</div>
                    <div class="credential-value">{{ $password }}</div>
                </div>

                <p style="margin-top: 15px; font-size: 12px; color: #d97706;">
                    ⚠️ <strong>Important:</strong> Please change your password upon first login for security purposes.
                </p>
            </div>
            @endif

            <!-- Next steps -->
            @if($nextPhase)
            <div class="next-steps">
                <h3>Next Steps 📋</h3>
                <p>
                    You have been moved to the <strong>{{ ucfirst($nextPhase) }}</strong> phase.
                </p>
                @if($nextSchedule)
                <p>
                    <strong>Scheduled for:</strong> {{ \Carbon\Carbon::parse($nextSchedule)->format('l, F j, Y \a\t g:i A') }}
                </p>
                @endif
            </div>
            @endif

            @if(!$companyEmail)
            <p>
                Please check your email regularly for updates and further instructions regarding the next steps in your application process.
            </p>
            @else
            <p>
                You can now log in to the OGS Connect system using the credentials provided above. 
                We look forward to working with you!
            </p>
            @endif

            <p style="margin-top: 30px;">
                If you have any questions, please don't hesitate to reach out to our HR team.
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
