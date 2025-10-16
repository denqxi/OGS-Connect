<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - OGS Connect</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
        }
        
        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1a365d 0%, #2d5a8a 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .logo-section {
            margin-bottom: 20px;
        }
        
        .logo-text {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 8px;
            letter-spacing: -1px;
            color: white !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .logo-section h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            color: white !important;
        }
        
        .logo-section p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
            color: white !important;
        }
        
        .content {
            padding: 40px 30px;
            background: white;
        }
        
        .greeting {
            font-size: 24px;
            color: #1a365d;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .message {
            font-size: 16px;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 30px;
        }
        
        .user-type {
            color: #2b6cb0;
            font-weight: 600;
            text-transform: capitalize;
        }
        
        .cta-section {
            text-align: center;
            margin: 40px 0;
        }
        
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
            color: white;
            text-decoration: none;
            padding: 16px 40px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }
        
        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
        }
        
        .security-notice {
            background: #fef7cd;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .security-notice h3 {
            color: #d97706;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .security-notice ul {
            list-style: none;
            color: #92400e;
        }
        
        .security-notice li {
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .security-notice li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #d97706;
            font-weight: bold;
        }
        
        .url-fallback {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .url-fallback p {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 8px;
        }
        
        .url-text {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #2563eb;
            word-break: break-all;
            background: white;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
        .footer {
            background: #f8fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-brand {
            font-size: 18px;
            color: #1a365d;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .footer-tagline {
            font-size: 14px;
            color: #64748b;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 20px;
                border-radius: 8px;
            }
            
            .header, .content, .footer {
                padding: 25px 20px;
            }
            
            .logo-section h1 {
                font-size: 28px;
            }
            
            .greeting {
                font-size: 20px;
            }
            
            .reset-button {
                padding: 14px 30px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header with OGS Connect Branding -->
        <div class="header">
            <div class="logo-section">
                <div class="logo-text" style="font-size: 36px; font-weight: 900; margin-bottom: 8px; letter-spacing: -1px; color: white !important; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">OGS</div>
                <h1 style="font-size: 32px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; color: white !important;">Connect</h1>
                <p style="font-size: 16px; opacity: 0.9; font-weight: 300; color: white !important;">Online Tutoring Management System</p>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <h2 class="greeting">Password Reset Request</h2>
            
            <p class="message">
                Hello! We received a password reset request for your <span class="user-type">{{ $userType }}</span> account on the OGS Connect platform.
            </p>
            
            <p class="message">
                To set a new password for your account, click the button below:
            </p>
            
            <!-- Call to Action -->
            <div class="cta-section">
                <a href="{{ $resetUrl }}" class="reset-button">
                    Reset My Password
                </a>
            </div>
            
            <!-- Security Notice -->
            <div class="security-notice">
                <h3>Security Information</h3>
                <ul>
                    <li>This reset link will expire in <strong>60 minutes</strong></li>
                    <li>If you didn't request this reset, you can safely ignore this email</li>
                    <li>For security, never share this link with anyone</li>
                    <li>The link can only be used once</li>
                </ul>
            </div>
            
            <!-- URL Fallback -->
            <div class="url-fallback">
                <p><strong>Button not working?</strong> Copy and paste this link into your browser:</p>
                <div class="url-text">{{ $resetUrl }}</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-brand">OGS Connect Team</div>
            <div class="footer-tagline">Connecting Students with Quality Education</div>
        </div>
    </div>
</body>
</html>