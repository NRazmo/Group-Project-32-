<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Get transfer details from session
$transferDetails = isset($_SESSION['transfer_details']) ? $_SESSION['transfer_details'] : null;
if (!$transferDetails) {
    header("Location: dashboard.php");
    exit();
}

// Clear transfer details from session
unset($_SESSION['transfer_details']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer Successful - MZMazwan Banking</title>
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

        .success-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
            width: 400px;
            text-align: center;
        }

        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .transfer-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .transfer-details p {
            margin: 8px 0;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .button {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            width: 48%;
        }

        .transfer-again {
            background-color: #007bff;
            color: white;
        }

        .transfer-again:hover {
            background-color: #0056b3;
        }

        .dashboard {
            background-color: #6c757d;
            color: white;
        }

        .dashboard:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="success-icon">✓</div>
        <h1>Transfer Successful!</h1>
        
        <div class="transfer-details">
            <p><strong>Recipient:</strong> <?php echo htmlspecialchars($transferDetails['recipientName']); ?></p>
            <p><strong>Amount:</strong> £<?php echo number_format($transferDetails['amount'], 2); ?></p>
            <p><strong>Account:</strong> <?php echo htmlspecialchars($transferDetails['recipientAccount']); ?></p>
            <p><strong>Sort Code:</strong> <?php echo htmlspecialchars($transferDetails['sortCode']); ?></p>
        </div>
        
        <div class="button-container">
            <a href="transfer.php" class="button transfer-again">Make Another Transfer</a>
            <a href="dashboard.php" class="button dashboard">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 