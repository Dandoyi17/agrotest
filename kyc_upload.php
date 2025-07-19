<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$table = $role . 's'; // admins, riders, passengers

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_dir = 'uploads/';
    $allowed_ext = ['jpg','jpeg','png','pdf','mp4'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $fields = [];

    // For riders: ID, vehicle document, profile photo
    // For passengers: ID, profile photo, ID video
    if ($role === 'rider') {
        $fields = [
            'id_doc' => 'Driving License',
            'vehicle_doc' => 'Vehicle Document',
            'vehicle_image' => 'Vehicle Photo'
        ];
    } elseif ($role === 'passenger') {
        $fields = [
            'id_doc' => 'Government Issued ID',
            'photo' => 'Personal Photo',
            'id_video' => 'ID Holding Video'
        ];
    }

    $updates = [];
    foreach ($fields as $field => $label) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) {
                $message .= "Invalid file type for $label.<br>";
                continue;
            }
            if ($_FILES[$field]['size'] > $max_size) {
                $message .= "$label is too large (max 5MB).<br>";
                continue;
            }
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = $role . '_' . $user_id . '_' . $field . '_' . time() . '.' . $ext;
            $target = $upload_dir . $filename;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                $updates[$field] = $target;
            } else {
                $message .= "Failed to upload $label.<br>";
            }
        }
    }

    if ($updates) {
        $set = [];
        $params = [];
        foreach ($updates as $col => $val) {
            $set[] = "$col = ?";
            $params[] = $val;
        }
        $params[] = $user_id;
        $sql = "UPDATE $table SET " . implode(', ', $set) . ", kyc_status='pending' WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $message .= "Documents uploaded. Awaiting admin approval.";
        } else {
            $message .= "Database update failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>KYC Document Upload</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/trip.css">
</head>
<body>
    <h2 class="header">KYC Document Upload</h2>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="wrapper">
        <?php if ($role === 'rider'): ?>
            <label>Driving License:</label><br>
            <input type="file" name="id_doc" required><br>
            <label>Vehicle Document:</label><br>
            <input type="file" name="vehicle_doc" required><br>
            <label>Vehicle Photo:</label><br>
            <input type="file" name="vehicle_image" required><br>
        <?php elseif ($role === 'passenger'): ?>
            <label>Government Issued ID:</label><br>
            <input type="file" name="id_doc" required><br>
            <label>5*7 Personal Photo:</label><br>
            <input type="file" name="photo" required><br>
            <label>ID holding Video (10 seconds):</label><br>
            <input type="file" name="id_video" accept="video/*" required><br>
        <?php else: ?>
            <p>No KYC required for admins.</p>
        <?php endif; ?>
        <?php if ($role !== 'admin'): ?>
            <button type="submit" class="btn btn-outline-primary">Upload Documents</button>
        <?php endif; ?>
    </form>
    <p><a href="<?php echo $role; ?>_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a></p>
</body>
</html>