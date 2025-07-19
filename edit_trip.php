<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: trip.php');
    exit;
}
$trip_id = intval($_GET['id']);

// Fetch trip
$stmt = $pdo->prepare("SELECT * FROM trips WHERE id=? AND rider_id=?");
$stmt->execute([$trip_id, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) {
    echo "Trip not found.";
    exit;
}

// Fetch bus stops for dropdowns
$bus_stops = $pdo->query("SELECT id, fullname FROM bus_stops ORDER BY fullname ASC")->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = intval($_POST['origin']);
    $destination = intval($_POST['destination']);
    $intermediate_stops = isset($_POST['bus_stops']) ? $_POST['bus_stops'] : [];
    $available_seats = intval($_POST['available_seats']);
    $price = floatval($_POST['price']);
    $trip_date = $_POST['trip_date'];
    $start_time = $_POST['start_time'];
    $bus_stops_str = implode(',', $intermediate_stops);

    $stmt = $pdo->prepare("UPDATE trips SET origin=?, destination=?, bus_stops=?, available_seats=?, price=?, trip_date=?, start_time=? WHERE id=? AND rider_id=?");
    if ($stmt->execute([$origin, $destination, $bus_stops_str, $available_seats, $price, $trip_date, $start_time, $trip_id, $_SESSION['user_id']])) {
        $message = "Trip updated successfully!";
        // Refresh trip data
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id=? AND rider_id=?");
        $stmt->execute([$trip_id, $_SESSION['user_id']]);
        $trip = $stmt->fetch();
    } else {
        $message = "Failed to update trip.";
    }
}

// Prepare selected intermediate stops
$selected_stops = $trip['bus_stops'] ? explode(',', $trip['bus_stops']) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Trip</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Edit Trip</h2>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <label>Origin:</label>
        <select name="origin" required>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>" <?php if ($trip['origin'] == $stop['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($stop['fullname']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>Destination:</label>
        <select name="destination" required>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>" <?php if ($trip['destination'] == $stop['id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($stop['fullname']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label>Intermediate Stops (hold Ctrl to select multiple):</label>
        <select name="bus_stops[]" multiple>
            <?php foreach ($bus_stops as $stop): ?>
                <?php if ($stop['id'] != $trip['origin'] && $stop['id'] != $trip['destination']): ?>
                    <option value="<?php echo $stop['id']; ?>" <?php if (in_array($stop['id'], $selected_stops)) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($stop['fullname']); ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <label>Available Seats:</label>
        <input type="number" name="available_seats" min="1" value="<?php echo htmlspecialchars($trip['available_seats']); ?>" required>
        <label>Price (₦):</label>
        <input type="number" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($trip['price']); ?>" required>
        <label>Trip Date:</label>
        <input type="date" name="trip_date" value="<?php echo htmlspecialchars($trip['trip_date']); ?>" required>
        <label>Start Time:</label>
        <input type="time" name="start_time" value="<?php echo htmlspecialchars($trip['start_time']); ?>" required>
        <button type="submit">Update Trip</button>
    </form>
    <p><a href="trip.php">Back to My Trips</a></p>
</body>
</html>