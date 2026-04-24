@php
    /** @var string $title */
    /** @var string|null $roomName */
    /** @var string|null $startsAt */
    /** @var string|null $endsAt */
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking confirmed</title>
</head>
<body>
    <h1>Your booking is confirmed</h1>
    <p><strong>Title:</strong> {{ $title }}</p>
    <p><strong>Room:</strong> {{ $roomName }}</p>
    <p><strong>Starts:</strong> {{ $startsAt }}</p>
    <p><strong>Ends:</strong> {{ $endsAt }}</p>
</body>
</html>
