<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
    exit;
}

// Fetch rider info
$stmt = $pdo->prepare("SELECT * FROM riders WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

// Set profile photo
$profile_img = (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) ? $user['profile_photo'] : 'default_avatar.png';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rider Profile</title>
    <link rel="stylesheet" href="./css/profile.css">
   
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">Rider Profile</div>
        <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile Photo" class="profile-avatar">
        <ul class="profile-info">
            <li><span class="label">Name:</span> <?php echo htmlspecialchars($user['name']); ?></li>
            <li><span class="label">Email:</span> <?php echo htmlspecialchars($user['email']); ?></li>
            <li>
                <span class="label">ID Document:</span>
                <?php if ($user['id_doc']): ?>
                    <a href="<?php echo htmlspecialchars($user['id_doc']); ?>" target="_blank">View</a>
                <?php else: ?>
                    <span style="color:#888;">Not uploaded</span>
                <?php endif; ?>
            </li>
            <li>
                <span class="label">Vehicle Document:</span>
                <?php if ($user['vehicle_doc']): ?>
                    <a href="<?php echo htmlspecialchars($user['vehicle_doc']); ?>" target="_blank">View</a>
                <?php else: ?>
                    <span style="color:#888;">Not uploaded</span>
                <?php endif; ?>
            </li>
            <li><span class="label">Password:</span> ********</li>
            <li>
                <span class="label">KYC Status:</span>
                <span class="profile-status"><?php echo htmlspecialchars($user['kyc_status']); ?></span>
            </li>
        </ul>
        <div class="profile-actions">
            <a href="update_rider_profile.php" class="btn">Update Profile</a>
            <a href="rider_dashboard.php" class="btn" style="background:#040546;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>