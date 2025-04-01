<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $senderID = $_SESSION['userID'];
    $recipientAccount = $_POST['recipientAccount'];
    $sortCode = $_POST['sortCode'];
    $recipientName = $_POST['recipientName'];
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Invalid amount.";
    } else {
        $stmt = $conn->prepare("SELECT userID FROM Users WHERE accountNumber = :accountNumber AND sortCode = :sortCode");
        $stmt->bindParam(':accountNumber', $recipientAccount);
        $stmt->bindParam(':sortCode', $sortCode);
        $stmt->execute();
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            $error = "Recipient not found.";
        } else {
            $recipientID = $recipient['userID'];
            $stmt = $conn->prepare("SELECT balance FROM Users WHERE userID = :userID");
            $stmt->bindParam(':userID', $senderID);
            $stmt->execute();
            $sender = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($sender['balance'] < $amount) {
                $error = "Insufficient balance.";
            } else {
                $stmt = $conn->prepare("UPDATE Users SET balance = balance - :amount WHERE userID = :userID");
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':userID', $senderID);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE Users SET balance = balance + :amount WHERE userID = :userID");
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':userID', $recipientID);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO Transactions (senderID, recipientID, amount, transferDate) VALUES (:senderID, :recipientID, :amount, NOW())");
                $stmt->bindParam(':senderID', $senderID);
                $stmt->bindParam(':recipientID', $recipientID);
                $stmt->bindParam(':amount', $amount);
                $stmt->execute();

                $success = "Transfer successful!";
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
</head>
<body>

    <div class="transfer-box">
        <div class="header">
            <img src="Assets/icons8-banking-100.png" alt="Bank">
            Quick Transfer
        </div>

        <label for="from">From:</label>
        <select id="from">
            <option>Quick Account</option>
        </select>

        <input type="text" placeholder="Enter Amount (£)">

        <label for="to">To:</label>
        <input type="text" placeholder="Account Number">
        <input type="text" placeholder="Sort Code">
        <input type="text" placeholder="Recipient Name">

        <div class="button-container">
            <button class="confirm-btn">Confirm</button>
            <button class="back-btn" onclick="window.location.href='dashboard.php'">Back</button>
        </div>
    </div>

</body>
</html>



