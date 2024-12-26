<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #28a745;
            font-size: 24px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h1>Password Changed Successfully</h1>
        <p>Hello {{ $user->name }},</p>
        <p>We are pleased to inform you that your password has been successfully changed. You can now use your new password to log in to your account.</p>
        <p>If you did not request this change, please contact our support team immediately.</p>
        <p>Thank you for using {{ config('app.name') }}!</p>
        <div class="footer">
            <p>If you have any questions, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The {{ config('app.name') }} Team</p>
        </div>
    </div>
</body>
</html>
