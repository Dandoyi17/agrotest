<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Fetch current info
$stmt = $pdo->prepare("SELECT * FROM passengers WHERE id=?");
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
            // Use correct path for passenger
            $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $target = "uploads/passenger_" . $_SESSION['user_id'] . "_profile." . $ext;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target)) {
                $profile_photo = $target;
            } else {
                $error = "Failed to upload image.";
            }
        }
        if (!$error) {
            $stmt = $pdo->prepare("UPDATE passengers SET name=?, email=?, profile_photo=? WHERE id=?");
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
    <title>Update Passenger Profile</title>
    <link rel="stylesheet" href="./css/update_passenger_profile.css">
    
    <style>
        body {
    background: #f6fafd;
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    padding: 0;
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

.update-form label {
    display: block;
    margin: 14px 0 4px 0;
    font-weight: 500;
    color: #040546;
}

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

.update-form button:hover {
    background: #040530;
}

.btn-back {
    background: #040546;
    margin-left: 8px;
    color: #fff;
    text-decoration: none;
    padding: 10px 22px;
    border-radius: 5px;
    display: inline-block;
}

.message {
    text-align: center;
    color: #388e3c;
    font-weight: 600;
    margin-bottom: 10px;
}

.error {
    text-align: center;
    color: #c0392b;
    font-weight: 600;
    margin-bottom: 10px;
}

@media (max-width: 700px) {
    .update-container {
        padding: 12px 4px 10px 4px;
        width: 98vw;
    }
    .update-header {
        font-size: 1.2em;
    }
}
    </style>
    
</head>
<body>
    <h2>Update Profile</h2>
    <?php if ($message): ?><p style="color:green;"><?php echo $message; ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
        <label>Profile Photo:</label>
        <?php if ($profile_img): ?>
            <img src="<?php echo htmlspecialchars($profile_img) . '?t=' . time(); ?>" width="80" class="profile-avatar">
        <?php endif; ?>
        <input type="file" name="profile_photo" accept="image/*"><br>
        <label>Current Password:</label>
        <input type="password" name="current_password" required><br>
        <button type="submit">Update</button>
    </form>
    <br>
    <a href="passenger_profile.php">Back to Profile</a>
</body>
</html>