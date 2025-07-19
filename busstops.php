<?php

session_start();
require_once 'db.php';

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$edit_stop = null;

// Handle edit request (populate form)
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM bus_stops WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_stop = $stmt->fetch();
}

// Handle add or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $abbreviation = trim($_POST['abbreviation']);
    $code = trim($_POST['code']);
    $fullname = trim($_POST['fullname']);
    $streetname = trim($_POST['streetname']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($abbreviation !== '' && $code !== '' && $fullname !== '' && $streetname !== '') {
        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE bus_stops SET abbreviation=?, code=?, fullname=?, streetname=? WHERE id=?");
            if ($stmt->execute([$abbreviation, $code, $fullname, $streetname, $id])) {
                $message = "Bus stop updated!";
            } else {
                $message = "Failed to update bus stop.";
            }
        } else {
            // Add
            $stmt = $pdo->prepare("INSERT IGNORE INTO bus_stops (abbreviation, code, fullname, streetname) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$abbreviation, $code, $fullname, $streetname])) {
                $message = "Bus stop added!";
            } else {
                $message = "Failed to add bus stop.";
            }
        }
    } else {
        $message = "All fields are required.";
    }
    // After add/update, clear edit state and reload
    header("Location: busstops.php");
    exit;
}

// Delete bus stop
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM bus_stops WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: busstops.php");
    exit;
}

// Fetch all bus stops
$stops = $pdo->query("SELECT * FROM bus_stops ORDER BY fullname ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Bus Stops</title><p><a href="admin_dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a></p>
    <link rel="stylesheet" href="./css/bustops.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <h2>Manage Bus Stops</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="" class="mb-4">
        <input type="hidden" name="id" value="<?php echo $edit_stop ? htmlspecialchars($edit_stop['id']) : ''; ?>">
        <label>Abbreviation:</label>
        <input type="text" name="abbreviation" value="<?php echo $edit_stop ? htmlspecialchars($edit_stop['abbreviation']) : ''; ?>" required>
        <label>Code:</label>
        <input type="text" name="code" value="<?php echo $edit_stop ? htmlspecialchars($edit_stop['code']) : ''; ?>" required>
        <label>Bus-stop:</label>
        <input type="text" name="fullname" value="<?php echo $edit_stop ? htmlspecialchars($edit_stop['fullname']) : ''; ?>" required>
        <label>Major Street:</label>
        <input type="text" name="streetname" value="<?php echo $edit_stop ? htmlspecialchars($edit_stop['streetname']) : ''; ?>" required>
        <button type="submit"><?php echo $edit_stop ? 'Update' : 'Add'; ?> Bus Stop</button>
        <?php if ($edit_stop): ?>
            <a href="busstops.php" class="btn btn-secondary btn-sm">Cancel Edit</a>
        <?php endif; ?>
    </form>
    <h3>All Bus Stops</h3>
    <table border="1" class="table">
        <tr>
            <th>Abbreviation</th>
            <th>Code</th>
            <th>Full Name</th>
            <th>Major Street</th>
            <th>Action</th>
        </tr>
        <?php foreach ($stops as $stop): ?>
        <tr>
            <td><?php echo htmlspecialchars($stop['abbreviation']); ?></td>
            <td><?php echo htmlspecialchars($stop['code']); ?></td>
            <td><?php echo htmlspecialchars($stop['fullname']); ?></td>
            <td><?php echo htmlspecialchars($stop['streetname']); ?></td>
            <td>
                <a href="?edit=<?php echo $stop['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                <a href="?delete=<?php echo $stop['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this bus stop?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
</body>
</html>