<?php
session_start();
require_once 'db.php';

$stmt = $pdo->prepare("SELECT kyc_status FROM passengers WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$passenger = $stmt->fetch();

if (!$passenger || $passenger['kyc_status'] !== 'approved') {
    echo "<h2 style='color:red;text-align:center;'>Your KYC is not approved. You cannot book a ride.</h2>";
    echo "<p style='text-align:center;'><a href='kyc_upload.php'>Upload/Check KYC Status</a></p>";
    exit;
}

// Only allow passengers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

// Fetch bus stops for search form
$bus_stops = $pdo->query("SELECT id, fullname FROM bus_stops ORDER BY fullname ASC")->fetchAll(PDO::FETCH_ASSOC);
$bus_stop_names = [];
foreach ($bus_stops as $stop) {
    $bus_stop_names[$stop['id']] = $stop['fullname'];
}

$message = '';
$trips = [];

// Fetch passenger wallet balance
$stmt = $pdo->prepare("SELECT balance FROM wallets WHERE user_id=? AND user_role='passenger'");
$stmt->execute([$_SESSION['user_id']]);
$wallet = $stmt->fetch();
$wallet_balance = $wallet ? floatval($wallet['balance']) : 0.0;

// Handle booking
if (isset($_GET['book'])) {
    $trip_id = intval($_GET['book']);
    // Get trip price and available seats
    $stmt = $pdo->prepare("SELECT available_seats, price FROM trips WHERE id = ?");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
    if ($trip && $trip['available_seats'] > 0) {
        if ($wallet_balance >= $trip['price']) {
            // Insert booking (ensure bookings table exists)
            $stmt = $pdo->prepare("INSERT INTO bookings (trip_id, passenger_id, status) VALUES (?, ?, 'pending')");
            if ($stmt->execute([$trip_id, $_SESSION['user_id']])) {
                // Decrement available seats
                $pdo->prepare("UPDATE trips SET available_seats = available_seats - 1 WHERE id = ?")->execute([$trip_id]);
                $message = "Booking successful! Awaiting rider confirmation.";
                // Update wallet balance for next booking attempt
                $wallet_balance -= $trip['price'];
            } else {
                $message = "Booking failed.";
            }
        } else {
            $message = "Insufficient wallet balance to book this trip.";
        }
    } else {
        $message = "No seats available.";
    }
}

// Search trips
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = isset($_POST['origin']) && $_POST['origin'] !== '' ? intval($_POST['origin']) : null;
    $destination = isset($_POST['destination']) && $_POST['destination'] !== '' ? intval($_POST['destination']) : null;

    $query = "SELECT t.*, r.name AS rider_name,
                (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
                (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
              FROM trips t
              JOIN riders r ON t.rider_id = r.id
              WHERE t.available_seats > 0 AND t.trip_date >= CURDATE() AND t.status = 'scheduled'";

    $params = [];
    if ($origin !== null) {
        $query .= " AND t.origin = ?";
        $params[] = $origin;
    }
    if ($destination !== null) {
        $query .= " AND t.destination = ?";
        $params[] = $destination;
    }
    $query .= " ORDER BY t.trip_date ASC, t.start_time ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Show all available trips if no search is performed
    $stmt = $pdo->prepare(
        "SELECT t.*, r.name AS rider_name,
            (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
            (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
         FROM trips t
         JOIN riders r ON t.rider_id = r.id
         WHERE t.available_seats > 0 AND t.trip_date >= CURDATE() AND t.status = 'scheduled'
         ORDER BY t.trip_date ASC, t.start_time ASC"
    );
    $stmt->execute();
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search & Book Trips</title>
    <style>body {
    background: #f4f8fb;
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.header {
    text-align: center;
    color: #040530;
    margin-top: 30px;
    font-size: 2em;
    font-weight: 700;
    letter-spacing: 1px;
}

.balance {
    text-align: center;
    font-size: 1.1em;
    color: #222;
    margin-bottom: 10px;
}

.form-control {
    max-width: 600px;
    margin: 20px auto 30px auto;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(21,101,192,0.07);
    padding: 24px 28px 18px 28px;
}

.label {
    display: block;
    margin: 12px 0 4px 0;
    font-weight: 500;
    color: #040530;
}

.form-control select,
.form-control input[type="text"],
.form-control input[type="number"] {
    width: 100%;
    padding: 8px 10px;
    margin-bottom: 10px;
    border: 1px solid #b3c6e0;
    border-radius: 5px;
    background: #f9fafb;
    font-size: 1em;
}

.form-control button,
.btn,
.btn-outline-primary {
    background: #040530;
    color: #fff;
    border: none;
    padding: 10px 22px;
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.2s, color 0.2s, border 0.2s;
    text-decoration: none;
    display: inline-block;
}

.btn-outline-primary {
    background: #fff;
    color: #040530;
    border: 1.5px solid #040530;
}

.btn-outline-primary:hover,
.form-control button:hover,
.btn:hover {
    background: #040530;
    color: #fff;
}

h3 {
    color: #040530;
    text-align: center;
    margin-top: 30px;
    margin-bottom: 18px;
}

.table {
    width: 98%;
    margin: 0 auto 30px auto;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(21,101,192,0.06);
    overflow: hidden;
}

.table th, .table td {
    padding: 13px 10px;
    text-align: left;
    border-bottom: 1px solid #e3e7f1;
}

.table th {
    background: #e3f2fd;
    color: #040530;
    font-weight: 700;
    font-size: 1.05em;
}

.table tr:last-child td {
    border-bottom: none;
}

.table tr:hover {
    background: #f1f8ff;
}

.table a {
    color: #040530;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.2s;
}

.table a:hover {
    color: #040530;
    text-decoration: underline;
}

@media (max-width: 700px) {
    .form-control, .table {
        width: 99%;
        padding: 10px 4px;
    }
    .header {
        font-size: 1.2em;
    }
    .table th, .table td {
        font-size: 0.97em;
        padding: 8px 4px;
    }
}</style>
    <!--<link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/search_trip.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
-->
    <link rel="stylesheet" href="./css/search_trip.css">
</head>
<body>
    <div class="header" id="top"><h2>Search for Available Trips</h2></div>
    <p class="balance"><strong>Your Wallet Balance:</strong> ₦<?php echo number_format($wallet_balance, 2); ?></p>
    <p><a href="passenger_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a></p>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="" class="form-control">
        <label class="label">Origin:</label>
        <select name="origin">
            <option value="">Any Origin</option>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>"><?php echo htmlspecialchars($stop['fullname']); ?></option>
            <?php endforeach; ?>
        </select>
        <label class="label">Destination:</label>
        <select name="destination">
            <option value="">Any Destination</option>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>"><?php echo htmlspecialchars($stop['fullname']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-primary">Search</button>
    </form>
    <h3>Available Trips</h3>
    <?php if ($trips): ?>
        <table border="1" class="table">
            <tr>
                <th>Rider</th>
                <th>Date</th>
                <th>Time</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Intermediate Stops</th>
                <th>Seats</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
            <?php foreach ($trips as $trip): ?>
            <tr>
                <td><?php echo htmlspecialchars($trip['rider_name']); ?></td>
                <td><?php echo htmlspecialchars($trip['trip_date']); ?></td>
                <td><?php echo htmlspecialchars($trip['start_time']); ?></td>
                <td><?php echo htmlspecialchars($trip['origin_name']); ?></td>
                <td><?php echo htmlspecialchars($trip['destination_name']); ?></td>
                <td>
                    <?php
                    if (!empty($trip['bus_stops'])) {
                        $ids = array_filter(array_map('trim', explode(',', $trip['bus_stops'])));
                        $names = [];
                        foreach ($ids as $id) {
                            if (isset($bus_stop_names[$id])) {
                                $names[] = htmlspecialchars($bus_stop_names[$id]);
                            }
                        }
                        echo $names ? implode(', ', $names) : '<span style="color:#888;">None</span>';
                    } else {
                        echo '<span style="color:#888;">None</span>';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($trip['available_seats']); ?></td>
                <td><?php echo htmlspecialchars($trip['price']); ?></td>
                <td>
                    <a href="trip_details.php?id=<?php echo $trip['id']; ?>" target="_blank">View Details</a>
                    <?php if ($wallet_balance >= $trip['price']): ?>
                        | <a href="?book=<?php echo $trip['id']; ?>" onclick="return confirm('Book this trip?');">Book</a>
                    <?php else: ?>
                        | <span style="color:red;">Insufficient funds</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No trips found.</p>
    <?php endif; ?>
    
    <p><a href="#top" class="btn btn-outline-primary">Back to top</a></p>
</body>
</html>