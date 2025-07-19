<?php
session_start();
require_once 'db.php';

if (!isset($_GET['booking_id'])) {
    echo "No booking selected.";
    exit;
}
$booking_id = intval($_GET['booking_id']);

// Handle send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $stmt = $pdo->prepare("INSERT INTO messages (booking_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$booking_id, $_SESSION['user_id'], $_SESSION['role'], $msg]);
    }
}

// Fetch messages
$stmt = $pdo->prepare("SELECT * FROM messages WHERE booking_id=? ORDER BY created_at ASC");
$stmt->execute([$booking_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <link rel="stylesheet" href="./css/massage.css">
</head>
<body>
    <h2>Messages for Booking #<?php echo $booking_id; ?></h2>
    <div class="chat-container">
        <ul class="message-list">
            <?php foreach ($messages as $m): ?>
                <li class="message-item<?php echo ($m['sender_id'] == $_SESSION['user_id']) ? ' self' : ''; ?>">
                    <span class="sender-label"><?php echo htmlspecialchars($m['sender_role']); ?></span>
                    <span class="bubble"><?php echo nl2br(htmlspecialchars($m['message'])); ?></span>
                    <span class="timestamp"><?php echo $m['created_at']; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <form method="post">
        <textarea name="message" required placeholder="Type your message..."></textarea>
        <button type="submit">Send</button>
    </form>
    <p><a href="my_bookings.php">Back to Bookings</a></p>
</body>
</html>