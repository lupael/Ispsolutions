<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8d7da; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #721c24;">
            ⚠️ Account Suspended
        </h2>
    </div>

    <p>Dear {{ $user->name }},</p>

    <p>Your account has been suspended.</p>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <strong>Reason:</strong> {{ $reason }}
    </div>

    <p>Please contact our support team for more information and to resolve this issue.</p>

    <p style="margin-top: 30px;">
        Best regards,<br>
        {{ config('app.name') }} Team
    </p>
</body>
</html>
