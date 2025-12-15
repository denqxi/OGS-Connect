<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #111">
    <p>Hi {{ $user->first_name ?? $user->name ?? 'User' }},</p>
    <p>We received a request to reset your OGS Connect password. Use the one-time PIN (OTP) below to verify your identity. This code expires in 10 minutes.</p>
    <h2 style="letter-spacing: 4px;">{{ $otp }}</h2>
    <p>If you did not request this, please ignore this email.</p>
    <p>Thanks,<br/>OGS Connect Team</p>
</div>