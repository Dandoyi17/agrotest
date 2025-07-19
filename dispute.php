<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $msg = trim($_POST['message']);
    if ($booking_id && $msg !== '') {
        $stmt = $pdo->prepare("INSERT INTO disputes (user_id, user_role, booking_id, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $role, $booking_id, $msg])) {
            $message = "Dispute submitted.";
        } else {
            $message = "Failed to submit dispute.";
        }
    }
}

// Fetch user's disputes
$stmt = $pdo->prepare("SELECT * FROM disputes WHERE user_id=? AND user_role=? ORDER BY created_at DESC");
$stmt->execute([$user_id, $role]);
$disputes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Disputes</title>
    <link rel="stylesheet" href="./css/booking.css">
</head>
<body>
    <h2>Raise a Dispute</h2>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <label>Booking ID:</label>
        <input type="number" name="booking_id" required>
        <label>Message:</label>
        <textarea name="message" required></textarea>
        <button type="submit">Submit Dispute</button>
    </form>
    <h3>My Disputes</h3>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Booking</th>
            <th>Message</th>
            <th>Status</th>
            <th>Admin Comment</th>
            <th>Date</th>
        </tr>
        <?php foreach ($disputes as $d): ?>
        <tr>
            <td><?php echo $d['id']; ?></td>
            <td><?php echo $d['booking_id']; ?></td>
            <td><?php echo htmlspecialchars($d['message']); ?></td>
            <td><?php echo htmlspecialchars($d['status']); ?></td>
            <td><?php echo htmlspecialchars($d['admin_comment'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($d['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="<?php echo $role; ?>_dashboard.php">Back to Dashboard</a></p>
</body>
</html>