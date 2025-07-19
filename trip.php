<?php
session_start();
require_once 'db.php';

// Check rider KYC status
$stmt = $pdo->prepare("SELECT kyc_status FROM riders WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$rider = $stmt->fetch();

if (!$rider || $rider['kyc_status'] !== 'approved') {
    echo "<h2 style='color:red;text-align:center;'>Your KYC is not approved. You cannot create a ride.</h2>";
    echo "<p style='text-align:center;'><a href='kyc_upload.php'>Upload/Check KYC Status</a></p>";
    exit;
}

// Only allow riders
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
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

    // Store intermediate stops as comma-separated IDs
    $bus_stops_str = implode(',', $intermediate_stops);

    $stmt = $pdo->prepare("INSERT INTO trips (rider_id, origin, destination, bus_stops, available_seats, price, trip_date, start_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$_SESSION['user_id'], $origin, $destination, $bus_stops_str, $available_seats, $price, $trip_date, $start_time])) {
        $message = "Trip created successfully!";
    } else {
        $message = "Failed to create trip.";
    }
}

// Fetch all trips created by this rider
$stmt = $pdo->prepare(
    "SELECT t.*, 
        (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
        (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
     FROM trips t
     WHERE t.rider_id = ?
     ORDER BY t.trip_date DESC, t.start_time DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$my_trips = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <style>body {
    background: #f5f8fa;
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.header {
    text-align: center;
    color: #040530;
    margin-top: 30px;
    font-size: 2.2em;
    letter-spacing: 1px;
    font-weight: 700;
}

.wrapper {
    max-width: 540px;
    margin: 30px auto 20px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(26,35,126,0.07);
    padding: 32px 32px 20px 32px;
}

.wrapper label {
    display: block;
    margin: 16px 0 6px 0;
    font-weight: 500;
    color: #040530;
}

.wrapper input[type="number"],
.wrapper input[type="date"],
.wrapper input[type="time"],
.wrapper select {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 8px;
    border: 1px solid #c5cae9;
    border-radius: 6px;
    background: #f7f9fc;
    font-size: 1em;
    transition: border 0.2s;
}

.wrapper input:focus,
.wrapper select:focus {
    border: 1.5px solid #040546;
    outline: none;
}

.wrapper button {
    background: linear-gradient(90deg, #040546 60%, #040530 100%);
    color: #fff;
    border: none;
    padding: 12px 28px;
    border-radius: 6px;
    font-size: 1.1em;
    font-weight: 600;
    cursor: pointer;
    margin-top: 12px;
    box-shadow: 0 2px 8px rgba(26,35,126,0.08);
    transition: background 0.2s;
}

.wrapper button:hover {
    background: linear-gradient(90deg, #040530 60%, #040546 100%);
}

.table {
    width: 95%;
    margin: 30px auto 0 auto;
    border-collapse: separate;
    border-spacing: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(26,35,126,0.06);
    overflow: hidden;
}

.table th, .table td {
    padding: 14px 12px;
    text-align: left;
    border-bottom: 1px solid #e3e7f1;
}

.table th {
    background: #e8eaf6;
    color: #040530;
    font-weight: 700;
    font-size: 1.05em;
}

.table tr:last-child td {
    border-bottom: none;
}

.table tr:hover {
    background: #f3f6fd;
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

.btn,
.btn-outline-primary {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 5px;
    font-size: 1em;
    font-weight: 500;
    text-decoration: none;
    margin: 8px 0;
    transition: background 0.2s, color 0.2s, border 0.2s;
}

.btn-outline-primary {
    color: #040546;
    border: 1.5px solid #040546;
    background: #fff;
}

.btn-outline-primary:hover {
    background: #040546;
    color: #fff;
}

@media (max-width: 700px) {
    .wrapper, .table {
        width: 98%;
        padding: 10px 4px;
    }
    .header {
        font-size: 1.3em;
    }
    .table th, .table td {
        font-size: 0.97em;
        padding: 8px 4px;
    }
}</style>
    <title>Create Trip</title>
    
     <link rel="stylesheet" href="./css/trip.css">
    
</head>
<body>
    <h2 class="header">Create a New Trip</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="" class="wrapper">
        <label>Origin:</label>
        <select name="origin" required>
            <option value="">Select Origin</option>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>"><?php echo htmlspecialchars($stop['fullname']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <label>Destination:</label>
        <select name="destination" required>
            <option value="">Select Destination</option>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>"><?php echo htmlspecialchars($stop['fullname']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <label>Intermediate Bus Stops (hold Ctrl to select multiple):</label>
        <select name="bus_stops[]" multiple>
            <?php foreach ($bus_stops as $stop): ?>
                <option value="<?php echo $stop['id']; ?>"><?php echo htmlspecialchars($stop['fullname']); ?></option>
            <?php endforeach; ?>
        </select><br>
        <label>Available Seats:</label>
        <input type="number" name="available_seats" min="1" required><br>
        <label>Price (per seat):</label>
        <input type="number" name="price" min="0" step="0.01" required><br>
        <label>Trip Date:</label>
        <input type="date" name="trip_date" required><br>
        <label>Start Time:</label>
        <input type="time" name="start_time" required><br>
        <button type="submit">Create Trip</button>
    </form>
    <p><a href="rider_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a></p>

    <h3>My Trip Schedules</h3>
    <?php if ($my_trips): ?>
        <table class="table table-bordered">
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Seats</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($my_trips as $trip): ?>
            <tr>
                <td><?php echo htmlspecialchars($trip['trip_date']); ?></td>
                <td><?php echo htmlspecialchars($trip['start_time']); ?></td>
                <td><?php echo htmlspecialchars($trip['origin_name']); ?></td>
                <td><?php echo htmlspecialchars($trip['destination_name']); ?></td>
                <td><?php echo htmlspecialchars($trip['available_seats']); ?></td>
                <td><?php echo htmlspecialchars($trip['price']); ?></td>
                <td><?php echo htmlspecialchars($trip['status']); ?></td>
                <td>
                    <a href="edit_trip.php?id=<?php echo $trip['id']; ?>">Edit</a>
                    <br>
                    <a href="trip_details.php?id=<?php echo $trip['id']; ?>">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No trips created yet.</p>
    <?php endif; ?>
    <p><a href="#top" class="btn btn-outline-primary">Back to top</a></p>
</body>
</html>