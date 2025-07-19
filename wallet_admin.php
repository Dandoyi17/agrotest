<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Status filter options
$status_options = ['pending', 'approved', 'rejected'];
$filter_status = isset($_GET['status']) && in_array($_GET['status'], $status_options) ? $_GET['status'] : '';

// Approve deposit
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $stmt = $pdo->prepare("UPDATE transactions SET status='approved', proof_comment=NULL WHERE id=?");
    $stmt->execute([$id]);
    // Credit wallet
    $stmt = $pdo->prepare("SELECT wallet_id, amount FROM transactions WHERE id=?");
    $stmt->execute([$id]);
    $tx = $stmt->fetch();
    if ($tx) {
        $pdo->prepare("UPDATE wallets SET balance = balance + ? WHERE id=?")->execute([$tx['amount'], $tx['wallet_id']]);
    }
    header("Location: wallet_admin.php" . ($filter_status ? "?status=$filter_status" : ""));
    exit;
}

// Reject deposit with comment
if (isset($_POST['reject_id']) && isset($_POST['reject_comment'])) {
    $id = intval($_POST['reject_id']);
    $comment = trim($_POST['reject_comment']);
    $stmt = $pdo->prepare("UPDATE transactions SET status='rejected', proof_comment=? WHERE id=?");
    $stmt->execute([$comment, $id]);
    header("Location: wallet_admin.php" . ($filter_status ? "?status=$filter_status" : ""));
    exit;
}

// Fetch pending deposits (always show pending for approval)
$stmt = $pdo->query(
    "SELECT t.*, w.user_role, w.user_id, 
        (SELECT name FROM admins WHERE id=w.user_id AND w.user_role='admin') AS admin_name,
        (SELECT name FROM riders WHERE id=w.user_id AND w.user_role='rider') AS rider_name,
        (SELECT name FROM passengers WHERE id=w.user_id AND w.user_role='passenger') AS passenger_name
     FROM transactions t
     JOIN wallets w ON t.wallet_id = w.id
     WHERE t.type='deposit' AND t.status='pending'
     ORDER BY t.created_at DESC"
);
$deposits = $stmt->fetchAll();

// Fetch all transactions with optional status filter
$query = "SELECT t.*, w.user_role, w.user_id, 
        (SELECT name FROM admins WHERE id=w.user_id AND w.user_role='admin') AS admin_name,
        (SELECT name FROM riders WHERE id=w.user_id AND w.user_role='rider') AS rider_name,
        (SELECT name FROM passengers WHERE id=w.user_id AND w.user_role='passenger') AS passenger_name
     FROM transactions t
     JOIN wallets w ON t.wallet_id = w.id";
$params = [];
if ($filter_status) {
    $query .= " WHERE t.status = ?";
    $params[] = $filter_status;
}
$query .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$all_transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Wallet Approvals</title>
    <link rel="stylesheet" href="./css/booking.css"><a href="admin_dashboard.php">Back to Dashboard</a>
</head>
<body>
    <h2>Pending Wallet Deposits</h2>
    <table border="1">
        <tr>
            <th>User</th>
            <th>Role</th>
            <th>Amount (₦)</th>
            <th>Deposit Method</th>
            <th>Date</th>
            <th>Proof</th>
            <th>Action</th>
        </tr>
        <?php foreach ($deposits as $d): ?>
        <tr>
            <td>
                <?php
                if ($d['user_role'] === 'admin') echo htmlspecialchars($d['admin_name']);
                elseif ($d['user_role'] === 'rider') echo htmlspecialchars($d['rider_name']);
                else echo htmlspecialchars($d['passenger_name']);
                ?>
            </td>
            <td><?php echo htmlspecialchars($d['user_role']); ?></td>
            <td><?php echo number_format($d['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($d['deposit_method'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($d['created_at']); ?></td>
            <td>
                <?php if ($d['proof']): ?>
                    <a href="<?php echo $d['proof']; ?>" target="_blank">View</a>
                <?php endif; ?>
            </td>
            <td>
                <a href="?approve=<?php echo $d['id']; ?>">Approve</a>
                <!-- Reject with comment form -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="reject_id" value="<?php echo $d['id']; ?>">
                    <input type="text" name="reject_comment" placeholder="Reason for rejection" required>
                    <button type="submit">Reject</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>All Transactions</h2>
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
            <th>User</th>
            <th>Role</th>
            <th>Type</th>
            <th>Amount (₦)</th>
            <th>Status</th>
            <th>Deposit Method</th>
            <th>Comment</th>
            <th>Date</th>
            <th>Proof</th>
        </tr>
        <?php foreach ($all_transactions as $t): ?>
        <tr>
            <td>
                <?php
                if ($t['user_role'] === 'admin') echo htmlspecialchars($t['admin_name']);
                elseif ($t['user_role'] === 'rider') echo htmlspecialchars($t['rider_name']);
                else echo htmlspecialchars($t['passenger_name']);
                ?>
            </td>
            <td><?php echo htmlspecialchars($t['user_role']); ?></td>
            <td><?php echo htmlspecialchars($t['type']); ?></td>
            <td><?php echo number_format($t['amount'], 2); ?></td>
            <td><?php echo htmlspecialchars($t['status']); ?></td>
            <td><?php echo htmlspecialchars($t['deposit_method'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($t['proof_comment'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($t['created_at']); ?></td>
            <td>
                <?php if ($t['proof']): ?>
                    <a href="<?php echo $t['proof']; ?>" target="_blank">View</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="admin_dashboard.php">Back to Dashboard</a><a href="#top">Back to top</a></p>
</body>
</html>