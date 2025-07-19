<?php

session_start();
require_once 'db.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Filter options
$filters = [
    'all' => 'All Activities',
    'trips' => 'Trips',
    'messages' => 'Messages',
    'transactions' => 'Transactions',
    'payments' => 'Payments',
    'users' => 'Users',
    'kyc' => 'KYC Approvals',
    'daily' => 'Daily Stats'
];
$filter = isset($_GET['filter']) && isset($filters[$_GET['filter']]) ? $_GET['filter'] : 'all';

// Data queries
$activities = [];
$summary = [];

if ($filter === 'all' || $filter === 'users') {
    // User counts
    $summary['riders'] = $pdo->query("SELECT COUNT(*) FROM riders")->fetchColumn();
    $summary['passengers'] = $pdo->query("SELECT COUNT(*) FROM passengers")->fetchColumn();
    $summary['admins'] = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
}
if ($filter === 'all' || $filter === 'kyc') {
    // KYC
    $summary['kyc_pending'] = $pdo->query("SELECT COUNT(*) FROM kyc WHERE status='pending'")->fetchColumn();
    $summary['kyc_approved'] = $pdo->query("SELECT COUNT(*) FROM kyc WHERE status='approved'")->fetchColumn();
}
if ($filter === 'all' || $filter === 'trips' || $filter === 'daily') {
    // Trips
    $summary['total_trips'] = $pdo->query("SELECT COUNT(*) FROM trips")->fetchColumn();
    $summary['today_trips'] = $pdo->query("SELECT COUNT(*) FROM trips WHERE trip_date=CURDATE()")->fetchColumn();
    if ($filter === 'trips' || $filter === 'all') {
        $stmt = $pdo->query("SELECT t.*, r.name AS rider_name FROM trips t JOIN riders r ON t.rider_id=r.id ORDER BY t.created_at DESC LIMIT 50");
        $activities['trips'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
if ($filter === 'all' || $filter === 'transactions' || $filter === 'payments' || $filter === 'daily') {
    // Transactions
    $summary['total_transactions'] = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    $summary['today_transactions'] = $pdo->query("SELECT COUNT(*) FROM transactions WHERE DATE(created_at)=CURDATE()")->fetchColumn();
    if ($filter === 'transactions' || $filter === 'all') {
        $stmt = $pdo->query("SELECT t.*, w.user_role FROM transactions t JOIN wallets w ON t.wallet_id=w.id ORDER BY t.created_at DESC LIMIT 50");
        $activities['transactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if ($filter === 'payments' || $filter === 'all') {
        $stmt = $pdo->query("SELECT * FROM transactions WHERE type='payment' ORDER BY created_at DESC LIMIT 50");
        $activities['payments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
if ($filter === 'all' || $filter === 'messages') {
    $stmt = $pdo->query("SELECT m.*, b.id AS booking_id, b.passenger_id, b.trip_id FROM messages m JOIN bookings b ON m.booking_id=b.id ORDER BY m.created_at DESC LIMIT 50");
    $activities['messages'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Platform Activities</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .activities-filter { text-align:center; margin: 30px 0 20px 0; }
        .activities-filter select { padding: 7px 14px; border-radius: 6px; border: 1px solid #b3c6e0; font-size: 1em; }
        .activities-summary { max-width: 900px; margin: 0 auto 30px auto; display: flex; flex-wrap: wrap; gap: 18px; justify-content: center; }
        .summary-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(25,118,210,0.06); padding: 18px 28px; min-width: 180px; text-align: center; }
        .summary-card h4 { color: #040530; margin: 0 0 8px 0; font-size: 1.1em; }
        .summary-card .big { font-size: 2em; font-weight: 700; color: #040546; }
        .activities-table { width: 98%; margin: 0 auto 30px auto; border-collapse: collapse; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(25,118,210,0.06); overflow: hidden; }
        .activities-table th, .activities-table td { padding: 10px 8px; border-bottom: 1px solid #e3e7f1; }
        .activities-table th { background: #e3f2fd; color: #040530; font-weight: 700; }
        .activities-table tr:last-child td { border-bottom: none; }
        .activities-table tr:hover { background: #f1f8ff; }
        .section-title { color: #040530; margin: 30px 0 10px 0; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Platform Activities & Stats</h2><a href="admin_dashboard.php">Back to Dashboard</a>
    </div>
    <div class="activities-filter">
        <form method="get">
            <label for="filter">Show:</label>
            <select name="filter" id="filter" onchange="this.form.submit()">
                <?php foreach ($filters as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php if ($filter === $key) echo 'selected'; ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="activities-summary">
        <?php if (isset($summary['riders'])): ?>
            <div class="summary-card"><h4>Riders</h4><div class="big"><?php echo $summary['riders']; ?></div></div>
            <div class="summary-card"><h4>Passengers</h4><div class="big"><?php echo $summary['passengers']; ?></div></div>
            <div class="summary-card"><h4>Admins</h4><div class="big"><?php echo $summary['admins']; ?></div></div>
        <?php endif; ?>
        <?php if (isset($summary['kyc_pending'])): ?>
            <div class="summary-card"><h4>KYC Pending</h4><div class="big"><?php echo $summary['kyc_pending']; ?></div></div>
            <div class="summary-card"><h4>KYC Approved</h4><div class="big"><?php echo $summary['kyc_approved']; ?></div></div>
        <?php endif; ?>
        <?php if (isset($summary['total_trips'])): ?>
            <div class="summary-card"><h4>Total Trips</h4><div class="big"><?php echo $summary['total_trips']; ?></div></div>
            <div class="summary-card"><h4>Today's Trips</h4><div class="big"><?php echo $summary['today_trips']; ?></div></div>
        <?php endif; ?>
        <?php if (isset($summary['total_transactions'])): ?>
            <div class="summary-card"><h4>Total Transactions</h4><div class="big"><?php echo $summary['total_transactions']; ?></div></div>
            <div class="summary-card"><h4>Today's Transactions</h4><div class="big"><?php echo $summary['today_transactions']; ?></div></div>
        <?php endif; ?>
    </div>

    <?php if (($filter === 'all' || $filter === 'trips') && !empty($activities['trips'])): ?>
        <h3 class="section-title">Recent Trips</h3>
        <table class="activities-table">
            <tr>
                <th>ID</th><th>Rider</th><th>Date</th><th>Time</th><th>Origin</th><th>Destination</th><th>Status</th>
            </tr>
            <?php foreach ($activities['trips'] as $t): ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['rider_name']); ?></td>
                    <td><?php echo htmlspecialchars($t['trip_date']); ?></td>
                    <td><?php echo htmlspecialchars($t['start_time']); ?></td>
                    <td><?php echo htmlspecialchars($t['origin']); ?></td>
                    <td><?php echo htmlspecialchars($t['destination']); ?></td>
                    <td><?php echo htmlspecialchars($t['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (($filter === 'all' || $filter === 'transactions') && !empty($activities['transactions'])): ?>
        <h3 class="section-title">Recent Transactions</h3>
        <table class="activities-table">
            <tr>
                <th>ID</th><th>User Role</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr>
            <?php foreach ($activities['transactions'] as $t): ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td><?php echo htmlspecialchars($t['user_role']); ?></td>
                    <td><?php echo htmlspecialchars($t['type']); ?></td>
                    <td>₦<?php echo number_format($t['amount'],2); ?></td>
                    <td><?php echo htmlspecialchars($t['status']); ?></td>
                    <td><?php echo htmlspecialchars($t['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (($filter === 'all' || $filter === 'payments') && !empty($activities['payments'])): ?>
        <h3 class="section-title">Recent Payments</h3>
        <table class="activities-table">
            <tr>
                <th>ID</th><th>Wallet</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr>
            <?php foreach ($activities['payments'] as $p): ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo $p['wallet_id']; ?></td>
                    <td>₦<?php echo number_format($p['amount'],2); ?></td>
                    <td><?php echo htmlspecialchars($p['status']); ?></td>
                    <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (($filter === 'all' || $filter === 'messages') && !empty($activities['messages'])): ?>
        <h3 class="section-title">Recent Messages</h3>
        <table class="activities-table">
            <tr>
                <th>ID</th><th>Booking</th><th>Sender</th><th>Role</th><th>Message</th><th>Date</th>
            </tr>
            <?php foreach ($activities['messages'] as $m): ?>
                <tr>
                    <td><?php echo $m['id']; ?></td>
                    <td><?php echo $m['booking_id']; ?></td>
                    <td><?php echo htmlspecialchars($m['sender_id']); ?></td>
                    <td><?php echo htmlspecialchars($m['sender_role']); ?></td>
                    <td><?php echo htmlspecialchars($m['message']); ?></td>
                    <td><?php echo htmlspecialchars($m['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p style="text-align:center;"><a href="admin_dashboard.php" class="btn-outline-primary">Back to Dashboard</a><a href="#top">Back to Top</a></p>
</body>
</html>