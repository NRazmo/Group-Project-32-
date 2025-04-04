<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

require 'connection.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senderID = $_SESSION['userID'];
    $recipientName = $_POST['recipientName'];
    $recipientAccount = $_POST['recipientAccount'];
    $sortCode = $_POST['sortCode'];
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Invalid amount.";
    } else {
        $stmt = $conn->prepare("SELECT balance FROM Users WHERE userID = :userID");
        $stmt->bindParam(':userID', $senderID);
        $stmt->execute();
        $sender = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sender['balance'] < $amount) {
            $error = "Insufficient balance.";
        } else {
            if ($amount > 1000) {
                $_SESSION['transfer_data'] = [
                    'recipientName' => $recipientName,
                    'recipientAccount' => $recipientAccount,
                    'sortCode' => $sortCode,
                    'amount' => $amount,
                    'senderID' => $senderID,
                ];
                header("Location: traffic_lights_verification.php"); // Redirect to image verification
                exit();
            } else {
                $stmt = $conn->prepare("UPDATE Users SET balance = balance - :amount WHERE userID = :userID");
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':userID', $senderID);
                $stmt->execute();

                $success = "Successfully sent £" . $amount . " to " . $recipientName;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Money</title>
</head>
<body>
    <div class="transfer-box">
        <div class="header">
            <img src="Assets/icons8-banking-100.png" alt="Bank">
            Quick Transfer
        </div>
        <?php if ($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="transfer.php">
            <label for="from">From:</label>
            <select id="from" name="from">
                <option>Quick Account</option>
            </select>
            <input type="text" name="amount" placeholder="Enter Amount (£)">
            <label for="to">To:</label>
            <input type="text" name="recipientAccount" placeholder="Account Number">
            <input type="text" name="sortCode" placeholder="Sort Code">
            <input type="text" name="recipientName" placeholder="Recipient Name">
            <div class="button-container">
                <button type="submit" class="confirm-btn">Confirm</button>
                <button type="button" class="back-btn" onclick="window.location.href='dashboard.php'">Back</button>
            </div>
        </form>
    </div>
</body>
</html>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #EFFFFC;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .transfer-box {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
        width: 400px;
    }

    .header {
        background: #eafaf1;
        padding: 12px;
        border-radius: 10px;
        text-align: center;
        font-weight: bold;
        font-size: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .header img {
        width: 24px;
        height: 24px;
        margin-right: 10px;
    }

    label {
        font-weight: bold;
        display: block;
        margin-top: 15px;
        font-size: 14px;
    }

    select, input {
        width: 93%;
        padding: 12px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-color: white;
    }

    select {
        background-image: url('Assets/dropdown-arrow.png');
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 12px;
    }

    .button-container {
        display: flex;
        justify-content: space-between;
        margin-top: 18px;
    }

    .confirm-btn, .back-btn {
        width: 48%;
        padding: 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
    }

    .confirm-btn {
        background: #007bff;
        color: white;
    }

    .confirm-btn:hover {
        background: #0056b3;
    }

    .back-btn {
        background: #6c757d;
        color: white;
    }

    .back-btn:hover {
        background: #545b62;
    }
</style>
