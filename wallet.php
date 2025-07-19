<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get or create wallet
$stmt = $pdo->prepare("SELECT * FROM wallets WHERE user_id=? AND user_role=?");
$stmt->execute([$user_id, $role]);
$wallet = $stmt->fetch();
if (!$wallet) {
    $pdo->prepare("INSERT INTO wallets (user_id, user_role, balance) VALUES (?, ?, 0)")->execute([$user_id, $role]);
    $stmt->execute([$user_id, $role]);
    $wallet = $stmt->fetch();
}

// Handle deposit proof upload
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['deposit_method'];
    $proof_required = in_array($method, ['bank_transfer', 'paystack', 'bank_deposit', 'flutterwave']);
    $proof_path = null;

    if ($amount > 0) {
        // Handle proof upload if required
        if ($proof_required && isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
            $filename = 'deposit_' . $wallet['id'] . '_' . time() . '.' . $ext;
            $target = 'uploads/' . $filename;
            if (move_uploaded_file($_FILES['proof']['tmp_name'], $target)) {
                $proof_path = $target;
            } else {
                $message = "Failed to upload proof.";
            }
        } elseif ($proof_required) {
            $message = "Proof of payment is required for this method.";
        }

        if (!$proof_required || $proof_path) {
            $stmt = $pdo->prepare("INSERT INTO transactions (wallet_id, amount, type, status, proof, deposit_method) VALUES (?, ?, 'deposit', 'pending', ?, ?)");
            if ($stmt->execute([$wallet['id'], $amount, $proof_path, $method])) {
                $message = "Deposit request submitted. Awaiting admin approval.";
            } else {
                $message = "Failed to submit deposit request.";
            }
        }
    } else {
        $message = "Amount is required.";
    }
}

// Fetch transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE wallet_id=? ORDER BY created_at DESC");
$stmt->execute([$wallet['id']]);
$transactions = $stmt->fetchAll();


?>
<!DOCTYPE html>
<html>
<head>
    <title>Wallet</title>
     <link rel="stylesheet" href="./css/style.css">
   <link rel="stylesheet" href="./css/wallet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function showInstructions() {
        var method = document.getElementById('deposit_method').value;
        document.getElementById('bank_transfer_info').style.display = (method === 'bank_transfer') ? 'block' : 'none';
        document.getElementById('paystack_info').style.display = (method === 'paystack') ? 'block' : 'none';
        document.getElementById('bank_deposit_info').style.display = (method === 'bank_deposit') ? 'block' : 'none';
        document.getElementById('flutterwave_info').style.display = (method === 'flutterwave') ? 'block' : 'none';
        // Show/hide proof upload
        var proofRow = document.getElementById('proof_row');
        if (method === 'bank_transfer' || method === 'paystack' || method === 'bank_deposit' || method === 'flutterwave') {
            proofRow.style.display = '';
        } else {
            proofRow.style.display = 'none';
        }
    }
    </script>
</head>
<body onload="showInstructions()">
    <h2 class="header">  Wallet Balance: <strong>₦<?php echo number_format($wallet['balance'], 2); ?></strong></p>
    <?php if ($message): ?></h2>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>


    
    <h3>Deposit Funds</h3><div class="container">
    <form method="post" enctype="multipart/form-data" class="form">
        <label>Amount (₦):</label>
        <input type="number" name="amount" min="1" step="0.01" required>
        <label class="row">Deposit Method:</label>
        <select name="deposit_method" id="deposit_method" onchange="showInstructions()" required>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="paystack">Paystack (screenshot proof)</option>
            <option value="bank_deposit">Bank Deposit (P-Ride Account)</option>
            <option value="flutterwave">Flutterwave (screenshot proof)</option>
        </select>
        <div id="bank_transfer_info" style="display:none; margin:8px 0; color:#333;">
            <strong>Bank Transfer Instructions:</strong><br>
            Transfer to P-Ride Account: <br>
            <b>Bank:</b> First Bank<br>
            <b>Account Name:</b> P-Ride Ltd<br>
            <b>Account Number:</b> 66548720<br>
            Upload your transfer receipt below.
        </div>
        <div id="paystack_info" style="display:none; margin:8px 0; color:#333;">
            <strong>Paystack Instructions:</strong><br>
            <a href="https://paystack.com/pay/pride" target="_blank" class="btn btn-warning">Click here to pay with Paystack</a><br>
            After payment, upload your screenshot proof below.
        </div>
        <div id="bank_deposit_info" style="display:none; margin:8px 0; color:#333;">
            <strong>Bank Deposit Instructions:</strong><br>
            Deposit cash into P-Ride Account:<br>
            <b>Bank:</b> Access Bank<br>
            <b>Account Name:</b> P-Ride Nig. Ltd<br>
            <b>Account Number:</b> 72883762<br>
            Upload your deposit slip below.
        </div>
        <div id="flutterwave_info" style="display:none; margin:8px 0; color:#333;">
            <strong>Flutterwave Instructions:</strong><br>
            <a href="https://flutterwave.com/pay/pride" target="_blank" class="btn btn-primary">Click to pay with Flutterwave</a><br>
            After payment, upload your screenshot proof below.
        </div>
        <div id="proof_row" style="margin:8px 0;">
            <label>Upload Payment Proof:</label>
            <input type="file" name="proof" accept="image/*,application/pdf">
        </div>
        <button type="submit" class="btn btn-success">Submit Deposit</button>
    </form>
    </div>
   <h3>Transaction History</h3>
<table border="1" class="table">
    <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Amount (₦)</th>
        <th>Status</th>
        <th>Method</th>
        <th>Proof</th>
        <th>Admin Comment</th>
    </tr>
    <?php foreach ($transactions as $t): ?>
    <tr>
        <td><?php echo htmlspecialchars($t['created_at']); ?></td>
        <td><?php echo htmlspecialchars($t['type']); ?></td>
        <td><?php echo number_format($t['amount'], 2); ?></td>
        <td><?php echo htmlspecialchars($t['status']); ?></td>
        <td><?php echo htmlspecialchars($t['deposit_method'] ?? ''); ?></td>
        <td>
            <?php if ($t['proof']): ?>
                <a href="<?php echo $t['proof']; ?>" target="_blank">View</a>
            <?php endif; ?>
        </td>
        <td><?php echo htmlspecialchars($t['proof_comment'] ?? ''); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
    <p><a href="<?php echo $role; ?>_dashboard.php" class="btn btn-primary">Back to Dashboard</a></p>
</body>
</html>