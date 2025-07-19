<?php
session_start();
require_once 'db.php';

// Only allow passengers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle booking cancellation
if (isset($_GET['cancel'])) {
    $booking_id = intval($_GET['cancel']);
    $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND passenger_id=? AND (status='pending' OR status='confirmed')");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    // Increment available seats back
    $stmt = $pdo->prepare("SELECT trip_id FROM bookings WHERE id=?");
    $stmt->execute([$booking_id]);
    $trip_id = $stmt->fetchColumn();
    if ($trip_id) {
        $pdo->prepare("UPDATE trips SET available_seats = available_seats + 1 WHERE id=?")->execute([$trip_id]);
    }
    header("Location: my_bookings.php");
    exit;
}

// Handle payment approval (with password)
if (isset($_POST['approve_payment']) && isset($_POST['password'])) {
    $booking_id = intval($_POST['approve_payment']);
    $password = $_POST['password'];

    // Fetch passenger info and booking
    $stmt = $pdo->prepare("SELECT p.password, t.price, b.trip_id, t.rider_id FROM bookings b JOIN passengers p ON b.passenger_id = p.id JOIN trips t ON b.trip_id = t.id WHERE b.id=? AND b.passenger_id=?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $row = $stmt->fetch();

    if ($row && password_verify($password, $row['password'])) {
        // Deduct from passenger wallet
        $stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id=? AND user_role='passenger'");
        $stmt->execute([$_SESSION['user_id']]);
        $wallet = $stmt->fetch();
        if ($wallet && $wallet['balance'] >= $row['price']) {
            $pdo->prepare("UPDATE wallets SET balance=balance-? WHERE id=?")->execute([$row['price'], $wallet['id']]);
            // Credit rider wallet
            $stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id=? AND user_role='rider'");
            $stmt->execute([$row['rider_id']]);
            $rider_wallet = $stmt->fetch();
            if ($rider_wallet) {
                $pdo->prepare("UPDATE wallets SET balance=balance+? WHERE id=?")->execute([$row['price'], $rider_wallet['id']]);
            }
            // Mark booking as completed and payment_triggered as 0
            $pdo->prepare("UPDATE bookings SET status='completed', payment_triggered=0 WHERE id=?")->execute([$booking_id]);
            $success = "Payment released successfully!";
        } else {
            $error = "Insufficient wallet balance.";
        }
    } else {
        $error = "Invalid password.";
    }
}

// Status filter options
$status_options = ['pending', 'confirmed', 'completed', 'cancelled'];
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $status_options) ? $_GET['status'] : '';

// Fetch bookings for this passenger
$query = "SELECT b.*, t.trip_date, t.start_time, t.price, t.origin, t.destination, t.status AS trip_status, 
        r.name AS rider_name, r.email AS rider_email, r.phone AS rider_phone,
        (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
        (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
     FROM bookings b
     JOIN trips t ON b.trip_id = t.id
     JOIN riders r ON t.rider_id = r.id
     WHERE b.passenger_id = ?";
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
    <title>My Bookings</title>
   <link rel="stylesheet" href="./css/booking.css">
   
</head>
<body>
    <h2>My Bookings</h2><a href="passenger_dashboard.php">Back to Dashboard</a>
    <?php if ($success): ?><p style="color:green;"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
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
    </form>
    <?php if ($bookings): ?>
        <table border="1">
            <tr>
                <th>Rider</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Date</th>
                <th>Time</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?php echo htmlspecialchars($b['rider_name']); ?></td>
                <td><?php echo htmlspecialchars($b['rider_email']); ?></td>
                <td>
                    <?php
                    if ($b['status'] === 'confirmed') {
                        echo '<a href="tel:' . htmlspecialchars($b['rider_phone']) . '">' . htmlspecialchars($b['rider_phone']) . '</a>';
                    } else {
                        echo '<span style="color:#888;">Hidden</span>';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($b['origin_name']); ?></td>
                <td><?php echo htmlspecialchars($b['destination_name']); ?></td>
                <td><?php echo htmlspecialchars($b['trip_date']); ?></td>
                <td><?php echo htmlspecialchars($b['start_time']); ?></td>
                <td><?php echo htmlspecialchars($b['price']); ?></td>
                <td><?php echo htmlspecialchars($b['status']); ?></td>
                <td>
                    <?php if ($b['status'] === 'confirmed' && $b['payment_triggered']): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="approve_payment" value="<?php echo $b['id']; ?>">
                            <input type="password" name="password" placeholder="Enter your password" required>
                            <button type="submit" onclick="return confirm('Approve and release payment for this trip?');">Confirm Ride & Release Payment</button>
                        </form>
                    <?php elseif ($b['status'] === 'confirmed' && $b['trip_status'] === 'completed'): ?>
                        <span>Awaiting rider to trigger payment</span>
                    <?php elseif ($b['status'] === 'pending' || $b['status'] === 'confirmed'): ?>
                        <a href="?cancel=<?php echo $b['id']; ?>" onclick="return confirm('Cancel this booking?');">Cancel</a>
                    <?php elseif ($b['status'] === 'completed'): ?>
                        <span>Completed</span>
                    <?php elseif ($b['status'] === 'cancelled'): ?>
                        <span>Cancelled</span>
                    <?php else: ?>
                        <span><?php echo htmlspecialchars($b['status']); ?></span>
                    <?php endif; ?>
                    <br>
                    <a href="messages.php?booking_id=<?php echo $b['id']; ?>">Message</a>
                    <br>
                    <a href="trip_details.php?id=<?php echo $b['trip_id']; ?>">View Trip Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
    <?php endif; ?>
    <p><a href="passenger_dashboard.php">Back to Dashboard</a><a href="#top">Back Up</a></p>
</body>
</html>