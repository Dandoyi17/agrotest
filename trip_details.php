<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) {
    header('Location: search_trips.php');
    exit;
}
$trip_id = intval($_GET['id']);

// Fetch trip and rider info
$stmt = $pdo->prepare(
    "SELECT t.*, r.name AS rider_name, r.vehicle_image, r.vehicle_info,
        (SELECT fullname FROM bus_stops WHERE id = t.origin) AS origin_name,
        (SELECT fullname FROM bus_stops WHERE id = t.destination) AS destination_name
     FROM trips t
     JOIN riders r ON t.rider_id = r.id
     WHERE t.id = ?"
);
$stmt->execute([$trip_id]);
$trip = $stmt->fetch();

if (!$trip) {
    echo "Trip not found.";
    exit;
}

// Fetch intermediate stops
$intermediate_stops = [];
if ($trip['bus_stops']) {
    $ids = explode(',', $trip['bus_stops']);
    if ($ids) {
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $pdo->prepare("SELECT fullname FROM bus_stops WHERE id IN ($in)");
        $stmt->execute($ids);
        $intermediate_stops = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Vehicle image and info from rider
$vehicle_image = (!empty($trip['vehicle_image']) && file_exists($trip['vehicle_image'])) ? $trip['vehicle_image'] : 'default_avatar.png';
$vehicle_info = !empty($trip['vehicle_info']) ? $trip['vehicle_info'] : 'Car Info';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trip Details</title>
    <link rel="stylesheet" href="./css/style.css">
    <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">-->
    <style>
        .body{
            height: 100%;
        }
        .body-fixed{
            background-attachment: fixed;
            background: linear-gradient(
                to top,
                #fff 50px,
                #040530 50px,
                #040530 100%
    
            );
        }
        .wrapper{
            background-color: #fff;
            max-width: 600px;
            height: auto;
            margin-left: 30%;
            margin-right: 30px;
            padding: 2px;
            border-radius: 7px;
            color: #040536;
            text-align: center;
            border: 1px solid #040530;
        }
        .card {
            margin: 24px auto;
            background: #f6fafd;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.09);
            max-width: 340px;
            padding: 18px 12px;
        }
        .card-img-top {
            width: 200px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 12px;
            background: #e3f2fd;
            border: 2px solid #1976d2;
        }
        .card-title {
            color: #1976d2;
            font-size: 1.18em;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .card-text {
            color: #3949ab;
            font-size: 1em;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: #1976d2;
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
            display: inline-block;
        }
        .btn-primary:hover {
            background: #1565c0;
        }
        @media (max-width: 700px) {
            .wrapper {
                margin-left: 2vw;
                margin-right: 2vw;
                max-width: 98vw;
            }
            .card {
                max-width: 98vw;
            }
        }
    </style>
</head>
<body class="body">
    <section class="wrapper">
        <h2>Trip Details</h2>
        <p><strong>Rider Name:</strong> <?php echo htmlspecialchars($trip['rider_name']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($trip['trip_date']); ?></p>
        <p><strong>Time:</strong> <?php echo htmlspecialchars($trip['start_time']); ?></p>
        <p><strong>Origin:</strong> <?php echo htmlspecialchars($trip['origin_name']); ?></p>
        <p><strong>Destination:</strong> <?php echo htmlspecialchars($trip['destination_name']); ?></p>
        <p><strong>Intermediate Stops:</strong>
            <?php echo $intermediate_stops ? implode(', ', array_map('htmlspecialchars', $intermediate_stops)) : 'None'; ?>
        </p>
        <p><strong>Seats:</strong> <?php echo htmlspecialchars($trip['available_seats']); ?></p>
        <p><strong>Price:</strong> <?php echo htmlspecialchars($trip['price']); ?></p>

        <div class="card">
            <img src="<?php echo htmlspecialchars($vehicle_image); ?>" alt="No car image uploaded." class="card-img-top">
            <div class="card-body">
                <h5 class="card-title">Car Type:</h5>
                <p class="card-text"><?php echo htmlspecialchars($vehicle_info); ?></p>
                <p class="card-text">Please note! Cars are private ownered and approved.</p>
                <a href="#" class="btn btn-primary">View More</a>
            </div>
        </div>
        <p><a href="index.php">Back Home</a></p>
    </section>
</body>
</html>