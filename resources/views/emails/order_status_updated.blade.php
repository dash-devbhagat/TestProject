<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
</head>
<body>
    <h1>Hello {{ $order->user->name }},</h1>
    <p>Your order with Order Number <strong>{{ $order->order_number }}</strong> has been updated to: <strong>{{ ucfirst($order->status) }}</strong>.</p>

    @if($additionalMessage)
        <p>{{ $additionalMessage }}</p>
    @endif

    <p>Thank you for shopping with us!</p>
</body>
</html>
