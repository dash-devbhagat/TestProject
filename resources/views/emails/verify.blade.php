<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            width: 100%;
            padding: 20px;
            background-color: #f4f4f9;
            text-align: center;
        }
        .email-content {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h2 {
            font-size: 24px;
            color: #4caf50;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            color: #555555;
        }
        a {
            background-color: #4caf50;
            color: #ffffff;
            padding: 12px 30px;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #45a049;
        }
        .footer {
            font-size: 14px;
            color: #888888;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="email-container">
    <div class="email-content">
        <h2>Hello {{ $user->name }},</h2>
        <p>Thank you for registering with us. We're excited to have you on board!</p>
        <p>To complete your registration, please click the button below to verify your email address.</p>
        <a href="{{ $verificationUrl }}">Verify Email Address</a>
        <p class="footer">
            If you didn't create an account, no further action is required. If you have any questions, feel free to contact us.
        </p>
    </div>
</div>

</body>
</html>
