<?php


session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? AND user_role=? AND is_read=0 ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['role']]);
$notifications = $stmt->fetchAll();

// Mark notifications as read after fetching
if ($notifications) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND user_role=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['role']]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/style.css">
   
</head>
<body>
    <div class="header">
  

    <h2>Welcome to the Admin Dashboard</h2>
    
</div>
    <!-- Display notifications if any -->
    <?php if ($notifications): ?>
        <h3>Notifications</h3>
        <ul>
            <?php foreach ($notifications as $n): ?>
                <li><?php echo htmlspecialchars($n['message']); ?> <small>(<?php echo $n['created_at']; ?>)</small></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>


    <nav class="row">
        <ul class="column side">
            <li><a href="busstops.php">Manage Bus Stops</a></li>
            <li><a href="kyc_admin.php">KYC Approvals</a></li>
            <li><a href="withdraw_admin.php">Withdraw Request</a></li>
            <li><a href="wallet_admin.php">All Transaction</a></li>
            <li><a href="activities.php">Activity</a></li>
            <li><a href="dispute.php">Disputes</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <div class="column middle"><h1>Our Brands and about Us!!!</h1></div>
        <div class="column side"><h2>Advertise With Us!!!</h2></div></div>
    </nav>

    

<div class="footer">
  <p>Only admin have access to.</p>
</div>
 
</body>
</html>