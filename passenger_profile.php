<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

// Fetch passenger info
$stmt = $pdo->prepare("SELECT * FROM passengers WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

// Set profile photo (if you have a photo column, otherwise always use default)
/*$profile_img = (!empty($user['photo']) && file_exists($user['photo'])) ? $user['photo'] : 'default_avatar.png';*/
$profile_photo = (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) ? $user['profile_photo'] : 'default_avatar.png';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Passenger Profile</title>
    <link rel="stylesheet" href="./css/profile.css">
    <style>
        body {
            background: #f6fafd;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 480px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 16px rgba(25, 118, 210, 0.10);
            padding: 32px 32px 24px 32px;
        }
        .profile-header {
            text-align: center;
            color: #040530;
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .profile-avatar {
            display: block;
            margin: 0 auto 18px auto;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #040530;
            background: #e3f2fd;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        .profile-info {
            margin: 0 auto 18px auto;
            padding: 0;
            list-style: none;
            max-width: 350px;
        }
        .profile-info li {
            margin: 14px 0;
            font-size: 1.08em;
            color: #34495e;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e3e7f1;
            padding-bottom: 7px;
        }
        .profile-info li:last-child {
            border-bottom: none;
        }
        .profile-info li span.label {
            color: #040530;
            font-weight: 600;
            margin-right: 10px;
            min-width: 120px;
            display: inline-block;
        }
        .profile-actions {
            text-align: center;
            margin-top: 22px;
        }
        .profile-actions .btn,
        .profile-actions a {
            background: #040530;
            color: #fff;
            border: none;
            padding: 10px 26px;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            margin: 0 6px 8px 6px;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .profile-actions .btn:hover,
        .profile-actions a:hover {
            background: #040530;
        }
        .profile-status {
            text-align: center;
            margin-bottom: 12px;
            font-size: 1.08em;
            color: #388e3c;
            font-weight: 600;
        }
        @media (max-width: 700px) {
            .profile-container {
                padding: 12px 4px 10px 4px;
                width: 98vw;
            }
            .profile-header {
                font-size: 1.2em;
            }
            .profile-info li {
                flex-direction: column;
                align-items: flex-start;
                font-size: 1em;
            }
            .profile-info li span.label {
                min-width: unset;
                margin-bottom: 2px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">Passenger Profile</div>
       <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" class="profile-avatar">
          <ul class="profile-info">
            <li><span class="label">Name:</span> <?php echo htmlspecialchars($user['name']); ?></li>
            <li><span class="label">Email:</span> <?php echo htmlspecialchars($user['email']); ?></li>
            <li><span class="label">Phone:</span> <?php echo htmlspecialchars($user['phone']); ?></li>
            <li><span class="label">Password:</span> ********</li>
            <li>
                <span class="label">KYC Status:</span>
                <span class="profile-status"><?php echo isset($user['kyc_status']) ? htmlspecialchars($user['kyc_status']) : 'N/A'; ?></span>
            </li>
        </ul>
        <div class="profile-actions">
            <a href="update_passenger_profile.php" class="btn">Update Profile</a>
            <a href="passenger_dashboard.php" class="btn" style="background:#040546;">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>