<?php


require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone    = trim($_POST['phone']);
    $address  = trim($_POST['address']);
    $bus_stop = trim($_POST['bus_stop']);

    // Check if email already exists in riders table
    $stmt = $pdo->prepare("SELECT id FROM riders WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $message = "Email already registered.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO riders (name, email, password, phone, address, bus_stop) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $password, $phone, $address, $bus_stop])) {
            $message = "Registration successful! You can now log in.";
        } else {
            $message = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rider Registration</title>
   <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <h2>Rider Registration</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <div class="login-logo-container" style="text-align:center; margin-bottom:24px;">
        <img src="./images/3dbluelogo.png" alt="P-Ride Logo" class="login-logo" style="width:70px; height:70px; object-fit:contain;">
    </div>
        <label>Name:</label><br>
        <input type="text" name="name" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <label>Phone:</label><br>
        <input type="text" name="phone" required><br>
        <label>Address:</label><br>
        <input type="text" name="address" required><br>
        <label>Nearest Bus Stop:</label><br>
        <input type="text" name="bus_stop" required><br>
        <button type="submit">Register</button>
    </form>
    <p><a href="index.php">Back to Home</a></p>
</body>
</html>