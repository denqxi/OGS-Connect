<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - OGS Connect</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Password Reset Request</h1>
        <p>OGS Connect System</p>
    </div>
    
    <div class="content">
        <h2>Hello!</h2>
        
        <p>You are receiving this email because we received a password reset request for your <strong>{{ ucfirst($userType) }}</strong> account.</p>
        
        <p>Click the button below to reset your password:</p>
        
        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Reset Password</a>
        </p>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong>
            <ul>
                <li>This password reset link will expire in <strong>60 minutes</strong></li>
                <li>If you did not request a password reset, please ignore this email</li>
                <li>Never share this link with anyone</li>
            </ul>
        </div>
        
        <p>If the button above doesn't work, you can copy and paste the following link into your browser:</p>
        <p style="word-break: break-all; color: #666; font-size: 12px;">{{ $resetUrl }}</p>
        
        <div class="footer">
            <p><strong>OGS Connect Team</strong></p>
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you have any questions, please contact your system administrator.</p>
        </div>
    </div>
</body>
</html>