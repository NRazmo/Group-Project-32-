<?php
session_start();
include('connection.php'); 

$error = "";

if (!isset($_SESSION['used_images'])) {
    $_SESSION['used_images'] = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userAnswer = $_POST['answer'];
    $correctAnswer = $_SESSION['verification_answer'];

    if ($userAnswer === $correctAnswer) {
        unset($_SESSION['used_images']);
        unset($_SESSION['verification_answer']);

        error_log("Traffic light verification successful."); 

        if (isset($_SESSION['transfer_data']['amount'])) {
            $amount = $_SESSION['transfer_data']['amount'];
            error_log("Transfer amount: " . $amount); 
            error_log("Contents of \$_SESSION['transfer_data']: " . print_r($_SESSION['transfer_data'], true)); 
            if ($amount >= 1000) {
                header("Location: tac_verify.php");
                exit();
            } else {
                error_log("Processing transfer for amount less than 1000."); 
                $transferData = $_SESSION['transfer_data'];
                $senderID = $transferData['senderID'];
                $recipientAccount = $transferData['recipientAccount'];
                $transferAmount = $transferData['amount'];

                $conn->beginTransaction();
                try {
                    $stmtSender = $conn->prepare("UPDATE Users SET balance = balance - :amount WHERE userID = :senderID");
                    $stmtSender->bindParam(':amount', $transferAmount);
                    $stmtSender->bindParam(':senderID', $senderID);
                    $stmtSender->execute();
                    error_log("Sender balance updated."); 

                    $stmtReceiver = $conn->prepare("SELECT userID FROM Users WHERE accountNumber = :recipientAccount");
                    $stmtReceiver->bindParam(':recipientAccount', $recipientAccount);
                    $stmtReceiver->execute();
                    $receiverResult = $stmtReceiver->fetch(PDO::FETCH_ASSOC);
                    error_log("Receiver Result: " . print_r($receiverResult, true)); 

                    if ($receiverResult && isset($receiverResult['userID'])) {
                        $receiverID = $receiverResult['userID'];
                        
                        $stmtReceiverBalance = $conn->prepare("UPDATE Users SET balance = balance + :amount WHERE userID = :receiverID");
                        $stmtReceiverBalance->bindParam(':amount', $transferAmount);
                        $stmtReceiverBalance->bindParam(':receiverID', $receiverID);
                        $stmtReceiverBalance->execute();
                        error_log("Receiver balance updated.");

                        $stmtTransaction = $conn->prepare("INSERT INTO Transactions (senderID, receiverID, amount, transactionDate, status) VALUES (:senderID, :receiverID, :amount, NOW(), 'Completed')");
                        $stmtTransaction->bindParam(':senderID', $senderID);
                        $stmtTransaction->bindParam(':receiverID', $receiverID);
                        $stmtTransaction->bindParam(':amount', $transferAmount);
                        $stmtTransaction->execute();
                        error_log("Transaction recorded."); 

                        $conn->commit();
                        error_log("Transaction committed."); 
                        $_SESSION['transfer_details'] = $_SESSION['transfer_data']; 
                        error_log("Attempting to redirect to transfer_success.php"); 
                        header("Location: transfer_success.php");
                        exit();
                    } else {
                        $conn->rollBack();
                        $error = "Recipient account not found.";
                        error_log("Recipient account not found."); 
                    }
                } catch (PDOException $e) {
                    $conn->rollBack();
                    $error = "Transfer failed: " . $e->getMessage();
                    error_log("Database error: " . $e->getMessage()); 
                }
            }
        } else {
            error_log("Error: \$_SESSION['transfer_data']['amount'] not set."); 
            header("Location: dashboard.php"); 
            exit();
        }
    } else {
        $error = "Incorrect answer. Please try again.";
    }
}

$verificationChallenges = [
    [
        'image' => 'Assets/Picture1.png',
        'question' => 'What is this image?',
        'correctAnswer' => 'Traffic Lights',
        'options' => ['Traffic Lights', 'Road Signs', 'Street Lights', 'Traffic Signals']
    ],
    [
        'image' => 'Assets/picture2.jpeg',
        'question' => 'What is shown in this image?',
        'correctAnswer' => 'School Bus',
        'options' => ['School Bus', 'City Bus', 'Tour Bus', 'Shuttle Bus']
    ],
    [
        'image' => 'Assets/picture3.jpeg',
        'question' => 'What is displayed in this image?',
        'correctAnswer' => 'Sidewalk',
        'options' => ['Sidewalk', 'Road', 'Parking Lot', 'Bike Path']
    ]
];

$availableChallenges = array_filter($verificationChallenges, function($challenge) {
    return !in_array($challenge['image'], $_SESSION['used_images']);
});

if (empty($availableChallenges)) {
    $_SESSION['used_images'] = [];
    $availableChallenges = $verificationChallenges;
}

$selectedChallenge = $availableChallenges[array_rand($availableChallenges)];
$correctAnswer = $selectedChallenge['correctAnswer'];

$_SESSION['used_images'][] = $selectedChallenge['image'];

$options = $selectedChallenge['options'];
shuffle($options);

$_SESSION['verification_answer'] = $correctAnswer;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Verification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="verification-box">
        <h1>Verify the Image</h1>
        <img src="<?php echo $selectedChallenge['image']; ?>" alt="Verification Image">
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="traffic_lights_verification.php">
            <label><?php echo $selectedChallenge['question']; ?></label><br>
            <?php foreach ($options as $option): ?>
                <div class="answer-option">
                    <input type="radio" id="<?php echo $option; ?>" name="answer" value="<?php echo $option; ?>" required>
                    <label for="<?php echo $option; ?>"><?php echo $option; ?></label>
                </div>
            <?php endforeach; ?>
            <br>
            <button type="submit">Verify</button>
        </form>

        <div style="margin-top: 20px;">
            <a href="dashboard.php" class="dashboard-button">Return to Dashboard</a>
        </div>
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

    .verification-box {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.15);
        width: 400px;
        text-align: center;
    }

    h1 {
        color: #333;
        margin-bottom: 20px;
    }

    img {
        max-width: 100%;
        height: auto;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .error {
        color: red;
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
    }

    input[type="radio"] {
        margin-right: 5px;
        vertical-align: middle;
    }

    button, .dashboard-button {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
    }

    button:hover, .dashboard-button:hover {
        background-color: #0056b3;
    }

    .answer-option {
        margin-bottom: 10px;
        text-align: left;
        display: flex;
        align-items: center;
    }

    .answer-option label {
        margin-bottom: 0;
    }
</style>
