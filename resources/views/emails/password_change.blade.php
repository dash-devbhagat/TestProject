<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change Request</title>
</head>
<body>
    <h1>Hello, {{ $name }}</h1>
    <p>We received a request to change your password. Click the link below to change your password:</p>
    <a href="{{ $url }}">Change Password</a>
    <p>If you did not request a password reset, please ignore this email.</p>
</body>
</html>
