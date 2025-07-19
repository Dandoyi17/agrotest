<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rider') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Fetch current info
$stmt = $pdo->prepare("SELECT * FROM riders WHERE id=?");
$stmt->execute([$_SESSION['user_id']]);

$user = $stmt->fetch();

if (!$user) {
    echo "User not found."; exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];

    // Password check
    if (!password_verify($current_password, $user['password'])) {
        $error = "Incorrect password.";
    } else {
        // Handle profile photo upload
        $profile_photo = $user['profile_photo'];
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['tmp_name']) {
            // Ensure uploads directory exists
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
            $target = "uploads/rider_" . $_SESSION['user_id'] . "_profile." . $ext;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
                $profile_photo = $target;
            } else {
                $error = "Failed to upload image.";
            }
        }
        if (!$error) {
            $stmt = $pdo->prepare("UPDATE riders SET name=?, email=?, profile_photo=? WHERE id=?");
            if ($stmt->execute([$name, $email, $profile_photo, $_SESSION['user_id']])) {
                $message = "Profile updated!";
                // Refresh user info
                $user['name'] = $name;
                $user['email'] = $email;
                $user['profile_photo'] = $profile_photo;
            } else {
                $error = "Update failed.";
            }
        }
    }
}

// Set default avatar if no photo
$profile_img = (!empty($user['profile_photo']) && file_exists($user['profile_photo'])) ? $user['profile_photo'] : 'default_avatar.png';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Rider Profile</title>
    <link rel="stylesheet" href="css/update_profile.css">
    <style>
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
        .message { text-align:center; color:#388e3c; font-weight:600; margin-bottom:10px; }
        .error { text-align:center; color:#c0392b; font-weight:600; margin-bottom:10px; }
        .update-form label { display:block; margin:14px 0 4px 0; font-weight:500; color:#040546; }
        .update-form input[type="text"],
        .update-form input[type="email"],
        .update-form input[type="password"],
        .update-form input[type="file"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #b3c6e0;
            border-radius: 5px;
            background: #f9fafb;
            font-size: 1em;
        }
        .update-form button {
            background: #040530;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        .update-form button:hover { background: #040530; }
        .btn-back {
            background: #040546;
            margin-left: 8px;
            color: #fff;
            text-decoration: none;
            padding: 10px 22px;
            border-radius: 5px;
            display: inline-block;
        }
        .update-container {
            max-width: 480px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 16px rgba(25, 118, 210, 0.10);
            padding: 32px 32px 24px 32px;
        }
        .update-header {
            text-align: center;
            color: #040530;
            font-size: 2em;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        @media (max-width: 700px) {
            .update-container { padding: 12px 4px 10px 4px; width: 98vw; }
            .update-header { font-size: 1.2em; }
        }
    </style>
</head>
<body>
    <div class="update-container">
        <div class="update-header">Update Rider Profile</div>
        <img src="<?php echo htmlspecialchars($profile_img) . '?t=' . time(); ?>" alt="Profile Photo" class="profile-avatar">
        <?php if ($message): ?><div class="message"><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data" class="update-form">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <label>Profile Photo:</label>
            <input type="file" name="profile_photo" accept="image/*">
            <label>Current Password:</label>
            <input type="password" name="current_password" required>
            <button type="submit">Update</button>
        </form>
        <br>
        <a href="rider_profile.php" class="btn-back">Back to Profile</a>
    </div>
</body>
</html>