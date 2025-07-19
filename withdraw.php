<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get wallet
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id=? AND user_role=?");
$stmt->execute([$user_id, $role]);
$wallet = $stmt->fetch();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    if ($wallet && $amount > 0 && $wallet['balance'] >= $amount) {
        $stmt = $pdo->prepare("INSERT INTO withdrawals (wallet_id, amount) VALUES (?, ?)");
        if ($stmt->execute([$wallet['id'], $amount])) {
            $pdo->prepare("UPDATE wallets SET balance=balance-? WHERE id=?")->execute([$amount, $wallet['id']]);
            $message = "Withdrawal request submitted. Awaiting admin approval.";
        } else {
            $message = "Failed to submit withdrawal request.";
        }
    } else {
        $message = "Insufficient balance or invalid amount.";
    }
}

// Fetch withdrawals
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE wallet_id=? ORDER BY created_at DESC");
$stmt->execute([$wallet['id']]);
$withdrawals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdraw Funds</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Withdraw Funds</h2>
    <p>Wallet Balance: ₦<?php echo number_format($wallet['balance'], 2); ?></p>
    <?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <form method="post">
        <label>Amount (₦):</label>
        <input type="number" name="amount" min="1" step="0.01" required>
        <button type="submit">Request Withdrawal</button>
    </form>
    <h3>Withdrawal History</h3>
    <table border="1">
        <tr>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Admin Comment</th>
        </tr>
        <?php foreach ($withdrawals as $w): ?>
        <tr>
            <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            <td><?php echo number_format($w['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($w['status']); ?></td>
            <td><?php echo htmlspecialchars($w['comment'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="<?php echo $role; ?>_dashboard.php">Back to Dashboard</a></p>
</body>
</html>