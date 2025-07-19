<?php
session_start();
require_once 'db.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal_id'])) {
    $withdrawal_id = intval($_POST['withdrawal_id']);
    $status = trim($_POST['status']);
    $admin_comment = trim($_POST['admin_comment']);

    // Fetch withdrawal, wallet, and rider info
    $stmt = $pdo->prepare("SELECT w.*, wa.user_id, r.email, r.name
        FROM withdrawals w
        JOIN wallets wa ON w.wallet_id = wa.id
        JOIN riders r ON wa.user_id = r.id
        WHERE w.id=? AND wa.user_role='rider'");
    $stmt->execute([$withdrawal_id]);
    $withdrawal = $stmt->fetch();

    if ($withdrawal) {
        // Update withdrawal status and comment
        $stmt = $pdo->prepare("UPDATE withdrawals SET status=?, comment=? WHERE id=?");
        $stmt->execute([$status, $admin_comment, $withdrawal_id]);

        // Send email to rider
        $to = $withdrawal['email'];
        $subject = "Withdrawal Request Update";
        $body = "Dear " . $withdrawal['name'] . ",\n\nYour withdrawal request of ₦" . number_format($withdrawal['amount'],2) . " has been updated to: $status.\n\nAdmin message: $admin_comment\n\nThank you.";
        $headers = "From: noreply@yourdomain.com\r\n";
        @mail($to, $subject, $body, $headers);

        $message = "Withdrawal updated and rider notified.";
    } else {
        $error = "Withdrawal not found.";
    }
}

// Status filter
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';
$status_options = ['pending', 'processing', 'completed', 'paid', 'cancelled'];

// Fetch all rider withdrawals with filter
$query = "SELECT w.*, wa.user_id, r.name AS rider_name, r.email AS rider_email
     FROM withdrawals w
     JOIN wallets wa ON w.wallet_id = wa.id
     JOIN riders r ON wa.user_id = r.id
     WHERE wa.user_role = 'rider'";
$params = [];
if ($filter_status && in_array($filter_status, $status_options)) {
    $query .= " AND w.status = ?";
    $params[] = $filter_status;
}
$query .= " ORDER BY w.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$withdrawals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Process Rider Withdrawals</title>
   <link rel="stylesheet" href="./css/booking.css">
</head>
<body>
    <h2>Rider Withdrawal Requests</h2>
    <?php if ($message): ?><p style="color:green;"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
<a href="admin_dashboard.php">Back to Dashboard</a>
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

    <table border="1">
        <tr>
            <th>Rider</th>
            <th>Email</th>
            <th>Amount</th>
            <th>Bank</th>
            <th>Account No.</th>
            <th>Account Name</th>
            <th>Status</th>
            <th>Date</th>
            <th>Admin Comment</th>
            <th>Action</th>
        </tr>
        <?php foreach ($withdrawals as $w): ?>
        <tr>
            <td><?php echo htmlspecialchars($w['rider_name']); ?></td>
            <td><?php echo htmlspecialchars($w['rider_email']); ?></td>
            <td><?php echo number_format($w['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($w['bank_name']); ?></td>
            <td><?php echo htmlspecialchars($w['account_number']); ?></td>
            <td><?php echo htmlspecialchars($w['account_name']); ?></td>
            <td><?php echo htmlspecialchars($w['status']); ?></td>
            <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            <td><?php echo htmlspecialchars($w['comment']); ?></td>
            <td>
                <form method="post" style="min-width:180px;">
                    <input type="hidden" name="withdrawal_id" value="<?php echo $w['id']; ?>">
                    <select name="status" required>
                        <?php foreach ($status_options as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if($w['status']==$opt) echo 'selected'; ?>>
                                <?php echo ucfirst($opt); ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                    <input type="text" name="admin_comment" placeholder="Message to rider" value="<?php echo htmlspecialchars($w['comment']); ?>">
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="admin_dashboard.php">Back to Dashboard</a><a href="#top">Back Up</a></p>
</body>
</html>