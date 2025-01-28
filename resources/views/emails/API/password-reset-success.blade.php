<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            background-color: #ffffff;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #ddd;
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
        .cta-button {
            background-color: #0056b3;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-body">
            <p>Dear {{ $user->name }},</p>
            <p>We are pleased to inform you that your password has been successfully reset. If you did not request this change, please contact our support team immediately.</p>
            <p>Thank you for being a valued customer.</p>
        </div>
        <div class="email-footer">
            <p>Best regards,</p>
            <p>Support Team</p>
            <p>------------------------------</p>
            <p>If you have any issues, feel free to contact us</p>
        </div>
    </div>
</body>
</html>
