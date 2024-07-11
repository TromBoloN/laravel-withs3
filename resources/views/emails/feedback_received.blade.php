<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Feedback Received</title>
</head>
<body>
    <h1>New Feedback Received</h1>
    <p><strong>Name:</strong> {{ $feedback['name'] }}</p>
    <p><strong>Email:</strong> {{ $feedback['email'] }}</p>
    <p><strong>Feedback:</strong></p>
    <p>{{ $feedback['feedback'] }}</p>
</body>
</html>