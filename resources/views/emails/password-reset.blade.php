<!DOCTYPE html>
<html>

<head>
    <title>SystemHub</title>
    <style>
        .button {
            background-color: yellow;
            border: none;
            color: white;
            padding: 10px 22px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <h2>Hello!</h2>
    <p> We received a request to reset your password. Click the button below to reset it.</p>
    <p>
        <a href="{{ $url }}" class="button">Reset Password</a>
    </p>
    <p>If you did not request a password reset, no further action is required.</p>
    <p>Thanks.</p>
</body>

</html>
