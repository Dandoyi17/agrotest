<?php
session_start();
require_once 'db.php';

// Only allow riders
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Fetch rider wallet
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id=? AND user_role='rider'");
$stmt->execute([$_SESSION['user_id']]);
$wallet = $stmt->fetch();
$balance = $wallet ? floatval($wallet['balance']) : 0.0;

// Handle withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $bank_name = trim($_POST['bank_name']);
    $account_number = trim($_POST['account_number']);
    $account_name = trim($_POST['account_name']);

    if ($amount <= 0) {
        $error = "Enter a valid amount.";
    } elseif ($amount > $balance) {
        $error = "Insufficient wallet balance.";
    } elseif (!$bank_name || !$account_number || !$account_name) {
        $error = "All bank details are required.";
    } else {
        // Insert withdrawal request
        $stmt = $pdo->prepare("INSERT INTO withdrawals (wallet_id, amount, bank_name, account_number, account_name, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        if ($stmt->execute([$wallet['id'], $amount, $bank_name, $account_number, $account_name])) {
            $message = "Withdrawal request submitted!";
            // Optionally, deduct from wallet immediately or after approval
            // $pdo->prepare("UPDATE wallets SET balance=balance-? WHERE id=?")->execute([$amount, $wallet['id']]);
            // Refresh balance
            $balance -= $amount;
        } else {
            $error = "Failed to submit withdrawal request.";
        }
    }
}

// Fetch withdrawal history
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE wallet_id=? ORDER BY created_at DESC");
$stmt->execute([$wallet['id']]);
$withdrawals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdraw Earnings</title>
    <link rel="stylesheet" href="./css/withdraw.css">
</head>
<body>
    <h2>Withdraw Earnings</h2>
    <p><strong>Wallet Balance:</strong> ₦<?php echo number_format($balance, 2); ?></p>
    <?php if ($message): ?><p style="color:green;"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <form method="post" action="">
        <label>Amount (₦):</label>
        <input type="number" name="amount" min="1" max="<?php echo intval($balance); ?>" step="0.01" required><br>
        <label>Bank Name:</label>
        <input type="text" name="bank_name" required><br>
        <label>Account Number:</label>
        <input type="text" name="account_number" required><br>
        <label>Account Name:</label>
        <input type="text" name="account_name" required><br>
        <button type="submit">Request Withdrawal</button>
    </form>

    <h3>Withdrawal History</h3>
    <table border="1">
        <tr>
            <th>Amount</th>
            <th>Bank</th>
            <th>Account No.</th>
            <th>Account Name</th>
            <th>Status</th>
            <th>Date</th>
            <th>Admin Comment</th>
        </tr>
        <?php foreach ($withdrawals as $w): ?>
        <tr>
            <td><?php echo number_format($w['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($w['bank_name']); ?></td>
            <td><?php echo htmlspecialchars($w['account_number']); ?></td>
            <td><?php echo htmlspecialchars($w['account_name']); ?></td>
            <td><?php echo htmlspecialchars($w['status']); ?></td>
            <td><?php echo htmlspecialchars($w['created_at']); ?></td>
            <td><?php echo htmlspecialchars($w['comment']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="rider_dashboard.php">Back to Dashboard</a></p>
</body>
</html>