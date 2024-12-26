<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
</head>
<body>
    <h2>Hello {{ $user->name }},</h2>
    <p>Welcome to our platform. Your account has been created successfully.</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Password:</strong> {{ $password }}</p>
    <p>Thank you for joining us!</p>
</body>
</html>
