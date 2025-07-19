<?php

session_start();
require_once 'db.php';

// Only allow riders
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
    exit;
}

// Status filter options
$status_options = ['pending', 'confirmed', 'completed', 'cancelled'];
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $status_options) ? $_GET['status'] : '';

// Approve booking
if (isset($_GET['approve'])) {
    $booking_id = intval($_GET['approve']);
    $stmt = $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?");
    $stmt->execute([$booking_id]);
    header("Location: rider_bookings.php" . ($filter_status ? "?status=$filter_status" : ""));
    exit;
}

// Cancel booking
if (isset($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);
    $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?");
    $stmt->execute([$booking_id]);
    // Increment available seats back
    $stmt = $pdo->prepare("SELECT trip_id FROM bookings WHERE id=?");
    $stmt->execute([$booking_id]);
    $trip_id = $stmt->fetchColumn();
    if ($trip_id) {
        $pdo->prepare("UPDATE trips SET available_seats = available_seats + 1 WHERE id=?")->execute([$trip_id]);
    }
    header("Location: rider_bookings.php" . ($filter_status ? "?status=$filter_status" : ""));
    exit;
}

// Mark trip as completed (so you can trigger payment for each passenger)
if (isset($_POST['complete_trip'])) {
    $trip_id = intval($_POST['complete_trip']);
    $stmt = $pdo->prepare("UPDATE trips SET status='completed' WHERE id=?");
    $stmt->execute([$trip_id]);
    header("Location: rider_bookings.php" . ($filter_status ? "?status=$filter_status" : ""));
    exit;
}

// Trigger payment for a booking (after trip completed)
if (isset($_POST['trigger_payment'])) {
    $booking_id = intval($_POST['trigger_payment']);
    // Only allow trigger if booking is confirmed and trip is completed
    $stmt = $pdo->prepare(
        "SELECT b.id FROM bookings b
         JOIN trips t ON b.trip_id = t.id
         WHERE b.id=? AND b.status='confirmed' AND t.status='completed'"
    );
    $stmt->execute([$booking_id]);
    if ($stmt->fetch()) {
        $stmt2 = $pdo->prepare("UPDATE bookings SET payment_triggered=1 WHERE id=?");
        $stmt2->execute([$booking_id]);
    }
    header("Location: rider_bookings.php" . ($filter_status ? "?status=$filter_status" : ""));
    exit;
}

// Fetch all bookings for this rider's trips, with optional status filter
$query = "SELECT b.*, t.trip_date, t.start_time, t.origin, t.destination, t.id AS trip_id, t.status AS trip_status, 
        p.name AS passenger_name, p.email AS passenger_email, p.phone AS passenger_phone,
        (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
        (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
     FROM bookings b
     JOIN trips t ON b.trip_id = t.id
     JOIN passengers p ON b.passenger_id = p.id
     WHERE t.rider_id = ?";
$params = [$_SESSION['user_id']];
if ($filter_status) {
    $query .= " AND b.status = ?";
    $params[] = $filter_status;
}
$query .= " ORDER BY t.trip_date DESC, t.start_time DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Trip Bookings</title>
    <link rel="stylesheet" href="./css/booking.css">
</head>
<body>
    <h2>Bookings for My Trips</h2>
    <form method="get" action="" style="margin-bottom:15px;">
        <label>Filter by Status:</label>
        <select name="status">
            <option value="">All</option>
            <?php foreach ($status_options as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php if($filter_status===$opt) echo 'selected'; ?>>
                    <?php echo ucfirst($opt); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form><button><a href="rider_dashboard.php">Back to Dashboard</a></button>
    <?php if ($bookings): ?>
        <table border="1">
            <tr>
                <th>Passenger</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['passenger_name']); ?></td>
                <td><?php echo htmlspecialchars($b['passenger_email']); ?></td>
                <td>
                    <?php
                    if ($b['status'] === 'confirmed') {
                        echo '<a href="tel:' . htmlspecialchars($b['passenger_phone']) . '">' . htmlspecialchars($b['passenger_phone']) . '</a>';
                    } else {
                        echo '<span style="color:#888;">Hidden</span>';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($b['origin_name']); ?></td>
                <td><?php echo htmlspecialchars($b['destination_name']); ?></td>
                <td><?php echo htmlspecialchars($b['trip_date']); ?></td>
                <td><?php echo htmlspecialchars($b['start_time']); ?></td>
                <td><?php echo htmlspecialchars($b['status']); ?></td>
                <td>
                    <?php if ($b['status'] === 'pending'): ?>
                        <a href="?approve=<?php echo $b['id']; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?>">Approve</a> |
                        <a href="?cancel=<?php echo $b['id']; ?><?php echo $filter_status ? '&status=' . $filter_status : ''; ?>" onclick="return confirm('Cancel this booking?');">Cancel</a>
                    <?php elseif ($b['status'] === 'confirmed' && $b['trip_status'] === 'scheduled'): ?>
                        <span>Confirmed</span>
                    <?php elseif ($b['status'] === 'completed'): ?>
                        <span>Completed</span>
                    <?php elseif ($b['status'] === 'cancelled'): ?>
                        <span>Cancelled</span>
                    <?php endif; ?>

                    <?php
                    // Show trigger payment button if:
                    // - Booking is confirmed
                    // - Trip is completed
                    // - Payment not yet triggered
                    if (
                        $b['status'] === 'confirmed'
                        && $b['trip_status'] === 'completed'
                        && !$b['payment_triggered']
                    ): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="trigger_payment" value="<?php echo $b['id']; ?>">
                            <button type="submit" onclick="return confirm('Trigger payment request for this passenger?');">Trigger Payment</button>
                        </form>
                    <?php elseif ($b['status'] === 'confirmed' && $b['trip_status'] === 'completed' && $b['payment_triggered']): ?>
                        <span style="color:green;">Awaiting passenger approval</span>
                    <?php endif; ?>

                    <br>
                    <a href="messages.php?booking_id=<?php echo $b['id']; ?>">Message</a>
                    <br>
                    <a href="trip_details.php?id=<?php echo $b['trip_id']; ?>">View Trip Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <?php
        // Show "Mark as Completed" button once per trip if still scheduled
        $shown_trips = [];
        foreach ($bookings as $b) {
            if ($b['trip_status'] === 'scheduled' && !in_array($b['trip_id'], $shown_trips)) {
                echo '<form method="post" style="display:inline;">
                        <input type="hidden" name="complete_trip" value="' . $b['trip_id'] . '">
                        <button type="submit" onclick="return confirm(\'Mark this trip as completed? This will allow you to trigger payment for each passenger.\');">Mark Trip as Completed</button>
                      </form><br>';
                $shown_trips[] = $b['trip_id'];
            }
        }
        ?>
    <?php else: ?>
        <p>No bookings for your trips yet.</p>
    <?php endif; ?>
    <p><a href="rider_dashboard.php">Back to Dashboard</a><a href="#top">Back to top</a></p>
</body>
</html>