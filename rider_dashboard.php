<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? AND user_role=? AND is_read=0 ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id'], $_SESSION['role']]);
$notifications = $stmt->fetchAll();

// Mark notifications as read after fetching
if ($notifications) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND user_role=? AND is_read=0");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['role']]);
}

// Fetch rider profile info and KYC status
$stmt = $pdo->prepare("SELECT name, profile_photo, kyc_status FROM riders WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();
$profile_img = (!empty($profile['profile_photo']) && file_exists($profile['profile_photo'])) ? $profile['profile_photo'] : 'default_avatar.png';
$profile_name = $profile ? $profile['name'] : 'Rider';
$kyc_status = $profile ? $profile['kyc_status'] : 'pending';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rider Dashboard</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .header {
            display: flex;
            align-items: center;
            background: #040530;
            color: #fff;
            padding: 24px 0 18px 0;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.07);
        }
        .profile-header-box {
            display: flex;
            align-items: center;
            margin-left: 32px;
        }
        .profile-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid #fff;
            background: #e3f2fd;
            margin-right: 18px;
        }
        .profile-header-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .profile-header-info .profile-name {
            font-size: 1.25em;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .profile-header-info .profile-role {
            font-size: 1em;
            color: #e3f2fd;
            font-weight: 500;
            margin-bottom: 2px;
        }
        .profile-header-info .welcome {
            font-size: 1.05em;
            color: #fff;
            font-weight: 500;
        }
        .header h2 {
            flex: 1;
            text-align: center;
            margin: 0;
            font-size: 2em;
            letter-spacing: 1px;
            font-weight: 700;
        }
        .kyc-status-box {
            text-align: center;
            margin: 18px 0 0 0;
        }
        .kyc-status {
            font-weight: bold;
            color: <?php echo ($kyc_status == 'approved') ? 'green' : 'red'; ?>;
        }
        .column.middle video {
            width: 700px;
            height: 450px;
            border-radius: 12px;
            margin-top: 0;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.09);
        }
        nav.row {
            display: flex;
            margin-top: 30px;
        }
        .column.side {
            flex: 0 0 220px;
            background: #f6fafd;
            padding: 18px 10px;
            border-radius: 12px;
            border-style: #040546 solid;
            margin: 0 12px;
            min-width: 180px;
        }
        .column.side ul {
            list-style: none;
            padding: 0;
        }
        .column.side li {
            margin-bottom: 14px;
        }
        .column.side a {
            color: #040530;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .column.side a:hover {
            color: #040546;
        }
        .column.middle {
            flex: 1 1 0;
            background: #fff;
            border-radius: 12px;
            border-width: 2px;
            padding: 24px 18px;
            margin: 0 12px;
            min-width: 0;
        }
        .footer {
            background: #e3f2fd;
            color: #040530;
            text-align: center;
            padding: 18px 0;
            margin-top: 40px;
        }
        @media (max-width: 900px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px 0 10px 0;
            }
            .profile-header-box {
                margin-left: 10px;
                margin-bottom: 8px;
            }
            .header h2 {
                font-size: 1.2em;
            }
            nav.row {
                flex-direction: column;
            }
            .column.side, .column.middle {
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="profile-header-box">
            <img src="<?php echo htmlspecialchars($profile_img); ?>" alt="Profile" class="profile-avatar">
            <div class="profile-header-info">
                <span class="profile-name"><?php echo htmlspecialchars($profile_name); ?></span>
                <span class="welcome">Welcome!</span>
                <span class="profile-role">Rider</span>
            </div>
        </div>
        <h2>Welcome to the Rider Dashboard</h2>
    </div>
    <div class="kyc-status-box">
        <strong>KYC Status:</strong>
        <span class="kyc-status"><?php echo ucfirst($kyc_status); ?></span>
        <?php if ($kyc_status !== 'approved'): ?>
            <br><a href="kyc_upload.php" style="color:#1976d2;">Upload/Check KYC</a>
        <?php endif; ?>
    </div>

    <?php if ($notifications): ?>
        <h3 style="margin-left:32px;">Notifications</h3>
        <ul style="margin-left:32px;">
            <?php foreach ($notifications as $n): ?>
                <li><?php echo htmlspecialchars($n['message']); ?> <small>(<?php echo $n['created_at']; ?>)</small></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <nav class="row">
        <ul class="column side">
            <li>
                <?php if ($kyc_status === 'approved'): ?>
                    <a href="trip.php">Create Trip</a>
                <?php else: ?>
                    <span style="color:gray;cursor:not-allowed;">Create Trip (KYC not approved)</span>
                <?php endif; ?>
            </li>
            <li><a href="kyc.php">KYC Status</a></li>
            <li><a href="kyc_upload.php">Upload KYC Documents</a></li>
            <li><a href="wallet.php">Wallet</a></li>
            <li><a href="withdraw_rider.php">Withdrawal</a></li>
            <li><a href="rider_bookings.php">My Trip Bookings</a></li>
            <li><a href="rider_profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
        <div class="column middle">
            <h1>
                <video src="./videos/test.mp4" autoplay muted loop controls style="margin-top:10px;" class="video"></video>
            </h1>
            <!-- You can add more dashboard content here -->
        </div>
        <div class="column side">
            <h2>Advertise With Us!!!<br>07047440709</h2>
            <!-- You can add adverts or info here -->
        </div>
    </nav>
    <div class="footer">
        <p>Rider has the right to create and cancel trips with reasons</p>
    </div>
</body>
</html>