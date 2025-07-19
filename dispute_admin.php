<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Resolve or reject dispute
if (isset($_POST['dispute_id']) && isset($_POST['admin_comment']) && isset($_POST['action'])) {
    $id = intval($_POST['dispute_id']);
    $comment = trim($_POST['admin_comment']);
    $status = $_POST['action'] === 'resolve' ? 'resolved' : 'rejected';
    $stmt = $pdo->prepare("UPDATE disputes SET status=?, admin_comment=? WHERE id=?");
    $stmt->execute([$status, $comment, $id]);
    header("Location: dispute_admin.php");
    exit;
}

// Fetch all open disputes
$stmt = $pdo->query(
    "SELECT d.*, 
        (SELECT name FROM riders WHERE id=d.user_id AND d.user_role='rider') AS rider_name,
        (SELECT name FROM passengers WHERE id=d.user_id AND d.user_role='passenger') AS passenger_name
     FROM disputes d
     WHERE d.status='open'
     ORDER BY d.created_at DESC"
);
$disputes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dispute Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Open Disputes</h2>
    <table border="1">
        <tr>
            <th>User</th>
            <th>Role</th>
            <th>Booking</th>
            <th>Message</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($disputes as $d): ?>
        <tr>
            <td>
                <?php
                if ($d['user_role'] === 'rider') echo htmlspecialchars($d['rider_name']);
                else echo htmlspecialchars($d['passenger_name']);
                ?>
            </td>
            <td><?php echo htmlspecialchars($d['user_role']); ?></td>
            <td><?php echo $d['booking_id']; ?></td>
            <td><?php echo htmlspecialchars($d['message']); ?></td>
            <td><?php echo htmlspecialchars($d['created_at']); ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="dispute_id" value="<?php echo $d['id']; ?>">
                    <textarea name="admin_comment" placeholder="Admin comment" required></textarea><br>
                    <button type="submit" name="action" value="resolve">Resolve</button>
                    <button type="submit" name="action" value="reject">Reject</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>