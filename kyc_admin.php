<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Approve or reject KYC
if (isset($_GET['role'], $_GET['id'], $_GET['action'])) {
    $role = $_GET['role'];
    $id = intval($_GET['id']);
    $action = $_GET['action'] === 'approve' ? 'approved' : 'rejected';
    $table = $role . 's';

    // Fetch user info for email
    $stmt = $pdo->prepare("SELECT name, email FROM $table WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    // Optional: handle rejection comment
    $comment = isset($_GET['comment']) ? trim($_GET['comment']) : null;
    if ($action === 'rejected' && $comment) {
        $stmt = $pdo->prepare("UPDATE $table SET kyc_status=?, kyc_comment=? WHERE id=?");
        $stmt->execute([$action, $comment, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE $table SET kyc_status=? WHERE id=?");
        $stmt->execute([$action, $id]);
    }

    // Send email notification
    if ($user && $user['email']) {
        $to = $user['email'];
        $subject = "KYC Verification " . ucfirst($action);
        if ($action === 'approved') {
            $body = "Dear {$user['name']},\n\nYour KYC verification has been approved. You can now use all features of the platform.\n\nThank you.";
        } else {
            $body = "Dear {$user['name']},\n\nYour KYC verification has been rejected.\nReason: $comment\n\nPlease review your documents and try again.\n\nThank you.";
        }
        $headers = "From: noreply@yourdomain.com\r\n";
        @mail($to, $subject, $body, $headers);
    }

    header("Location: kyc_admin.php?role_filter=$role");
    exit;
}

// Filter logic
$role_filter = isset($_GET['role_filter']) ? $_GET['role_filter'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$status_options = ['pending', 'approved', 'rejected'];

// Fetch KYC requests
$riders = [];
$passengers = [];
if ($role_filter === 'rider' || $role_filter === '') {
    $query = "SELECT id, name, email, id_doc, vehicle_doc, vehicle_image, kyc_status, kyc_comment FROM riders WHERE 1";
    $params = [];
    if ($status_filter && in_array($status_filter, $status_options)) {
        $query .= " AND kyc_status=?";
        $params[] = $status_filter;
    }
    $riders = $pdo->prepare($query);
    $riders->execute($params);
    $riders = $riders->fetchAll();
}
if ($role_filter === 'passenger' || $role_filter === '') {
    $query = "SELECT id, name, email, id_doc, photo, id_video, kyc_status, kyc_comment FROM passengers WHERE 1";
    $params = [];
    if ($status_filter && in_array($status_filter, $status_options)) {
        $query .= " AND kyc_status=?";
        $params[] = $status_filter;
    }
    $passengers = $pdo->prepare($query);
    $passengers->execute($params);
    $passengers = $passengers->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>KYC Approvals (Admin)</title>
    <link rel="stylesheet" href="./css/booking.css">
    <style>
        .kyc-comment { color: #b00; font-size: 0.95em; }
    </style>
</head>
<body>
    <h2>KYC Approvals</h2>
    <form method="get" action="" style="margin-bottom:15px;">
        <label>User:</label>
        <select name="role_filter">
            <option value="" <?php if($role_filter==='') echo 'selected'; ?>>All</option>
            <option value="rider" <?php if($role_filter==='rider') echo 'selected'; ?>>Rider</option>
            <option value="passenger" <?php if($role_filter==='passenger') echo 'selected'; ?>>Passenger</option>
        </select>
        <label>Status:</label>
        <select name="status_filter">
            <option value="" <?php if($status_filter==='') echo 'selected'; ?>>All</option>
            <?php foreach ($status_options as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php if($status_filter===$opt) echo 'selected'; ?>>
                    <?php echo ucfirst($opt); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>
<a href="admin_dashboard.php">Back to Dashboard</a>
    <?php if ($role_filter === 'rider' || $role_filter === ''): ?>
    <h3>Riders</h3>
    <table border="1">
        <tr>
            <th>Name</th><th>Email</th><th>ID Doc</th><th>Vehicle Doc</th><th>Profile Photo</th><th>Status</th><th>Comment</th><th>Action</th>
        </tr>
        <?php foreach ($riders as $r): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['name']); ?></td>
            <td><?php echo htmlspecialchars($r['email']); ?></td>
            <td><?php if ($r['id_doc']) echo "<a href='{$r['id_doc']}' target='_blank'>View</a>"; ?></td>
            <td><?php if ($r['vehicle_doc']) echo "<a href='{$r['vehicle_doc']}' target='_blank'>View</a>"; ?></td>
            <td><?php if ($r['vehicle_image']) echo "<a href='{$r['vehicle_image']}' target='_blank'>View</a>"; ?></td>
            <td><?php echo htmlspecialchars($r['kyc_status']); ?></td>
            <td class="kyc-comment"><?php echo htmlspecialchars($r['kyc_comment'] ?? ''); ?></td>
            <td>
                <?php if ($r['kyc_status'] === 'pending'): ?>
                    <a href="?role=rider&id=<?php echo $r['id']; ?>&action=approve&role_filter=rider">Approve</a> |
                    <form method="get" style="display:inline;">
                        <input type="hidden" name="role" value="rider">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="role_filter" value="rider">
                        <input type="text" name="comment" placeholder="Rejection reason" required>
                        <button type="submit">Reject</button>
                    </form>
                <?php else: ?>
                    <span>-</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($role_filter === 'passenger' || $role_filter === ''): ?>
    <h3>Passengers</h3>
    <table border="1">
        <tr>
            <th>Name</th><th>Email</th><th>ID Doc</th><th>Profile Photo</th><th>ID Video</th><th>Status</th><th>Comment</th><th>Action</th>
        </tr>
        <?php foreach ($passengers as $p): ?>
        <tr>
            <td><?php echo htmlspecialchars($p['name']); ?></td>
            <td><?php echo htmlspecialchars($p['email']); ?></td>
            <td><?php if ($p['id_doc']) echo "<a href='{$p['id_doc']}' target='_blank'>View</a>"; ?></td>
            <td><?php if ($p['photo']) echo "<a href='{$p['photo']}' target='_blank'>View</a>"; ?></td>
            <td><?php if ($p['id_video']) echo "<a href='{$p['id_video']}' target='_blank'>View</a>"; ?></td>
            <td><?php echo htmlspecialchars($p['kyc_status']); ?></td>
            <td class="kyc-comment"><?php echo htmlspecialchars($p['kyc_comment'] ?? ''); ?></td>
            <td>
                <?php if ($p['kyc_status'] === 'pending'): ?>
                    <a href="?role=passenger&id=<?php echo $p['id']; ?>&action=approve&role_filter=passenger">Approve</a> |
                    <form method="get" style="display:inline;">
                        <input type="hidden" name="role" value="passenger">
                        <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="role_filter" value="passenger">
                        <input type="text" name="comment" placeholder="Rejection reason" required>
                        <button type="submit">Reject</button>
                    </form>
                <?php else: ?>
                    <span>-</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>