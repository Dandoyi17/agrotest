<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$table = $role . 's'; // admins, riders, passengers

$stmt = $pdo->prepare("SELECT kyc_status FROM $table WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$kyc_status = $user ? $user['kyc_status'] : 'Unknown';
?>
<!DOCTYPE html>
<html>
<head>
    <title>KYC Status</title>
   <link rel="stylesheet" href="./css/style.css">
     <link rel="stylesheet" href="./css/trip.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
   
</head>
<body>
    <h2 class="header">KYC Status</h2>
    <p class="wrapper">Your KYC status: <strong><?php echo htmlspecialchars($kyc_status); ?></strong></p>
    <p><a href="<?php echo $role; ?>_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a></p>
</body>
</html>