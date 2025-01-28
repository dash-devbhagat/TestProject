<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            width: 100%;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .email-header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .email-header img {
            width: 150px;
        }
        .email-body {
            padding: 20px;
        }
        .email-footer {
            text-align: center;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
        .otp-box {
            background-color: #e3f2fd;
            padding: 10px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-body">
            <p>Dear {{ $user->name }},</p>
            <p>We received a request to reset the password for your account.</p>
            <p>To complete the password reset, please enter the OTP below:</p>
            <div class="otp-box">
                {{ $otp }}
            </div>
            <p>Please note that this OTP will expire in 2 minutes.</p>
            <p>If you did not request a password reset, please ignore this email or contact support immediately.</p>
        </div>
        <div class="email-footer">
            <p>Thank you</p>
        </div>
    </div>
</body>
</html>
