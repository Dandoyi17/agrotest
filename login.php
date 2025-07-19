<?php

require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role']; // admin, rider, passenger

    // Set table based on role
    if ($role === 'admin') {
        $table = 'admins';
        $redirect = 'admin_dashboard.php';
    } elseif ($role === 'rider') {
        $table = 'riders';
        $redirect = 'rider_dashboard.php';
    } else {
        $table = 'passengers';
        $redirect = 'passenger_dashboard.php';
    }

    $stmt = $pdo->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $role;
        header("Location: $redirect");
        exit;
    } else {
        $message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    
    <h2>Login</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <div class="login-logo-container" style="text-align:center; margin-bottom:24px;">
        <img src="./images/3dbluelogo.png" alt="P-Ride Logo" class="login-logo" style="width:70px; height:70px; object-fit:contain;">
    </div>
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <label>Role:</label><br>
        <select name="role" required>
            <option value="admin">Admin</option>
            <option value="rider">Rider</option>
            <option value="passenger">Passenger</option>
        </select><br><br>
        <button type="submit">Login</button>
    </form>
    <p>
        <a href="#">Register as Admin</a> |
        <a href="rider_register.php">Register as Rider</a> |
        <a href="passenger_register.php">Register as Passenger</a>
    </p>
</body>
</html>