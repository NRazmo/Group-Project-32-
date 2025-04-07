<?php
session_start();
include('connection.php');

if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tac_code1'], $_POST['tac_code2'], $_POST['tac_code3'], $_POST['tac_code4'], $_POST['tac_code5'])) {
    $tac_code = $_POST['tac_code1'] . $_POST['tac_code2'] . $_POST['tac_code3'] . $_POST['tac_code4'] . $_POST['tac_code5'];
    $userID = $_SESSION['userID'];

    try {
        $query = "SELECT tacCode FROM TACCodes WHERE userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['tacCode'] === $tac_code) {
            $transfer_data = $_SESSION['transfer_data'];
            $recipientName = $transfer_data['recipientName'];
            $recipientAccount = $transfer_data['recipientAccount'];
            $amount = $transfer_data['amount'];
            $senderID = $transfer_data['senderID'];

            $conn->beginTransaction();

            try {
                $stmt = $conn->prepare("SELECT userID FROM Users WHERE accountNumber = :accountNumber");
                $stmt->bindParam(':accountNumber', $recipientAccount);
                $stmt->execute();
                $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$recipient) {
                    throw new Exception("Recipient account not found.");
                }

                $recipientID = $recipient['userID'];

                $stmt = $conn->prepare("UPDATE Users SET balance = balance - :amount WHERE userID = :userID");
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':userID', $senderID);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE Users SET balance = balance + :amount WHERE userID = :userID");
                $stmt->bindParam(':amount', $amount);
                $stmt->bindParam(':userID', $recipientID);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO Transactions (senderID, receiverID, amount, status) VALUES (:senderID, :receiverID, :amount, 'Completed')");
                $stmt->bindParam(':senderID', $senderID);
                $stmt->bindParam(':receiverID', $recipientID);
                $stmt->bindParam(':amount', $amount);
                $stmt->execute();

                $conn->commit();

                $_SESSION['transfer_details'] = $transfer_data;
                
                unset($_SESSION['transfer_data']);
                
                header("Location: transfer_success.php");
                exit();
            } catch (Exception $e) {
                $conn->rollBack();
                $errorMessage = "Transfer failed: " . $e->getMessage();
            }
        } else {
            $errorMessage = "Invalid TAC code. Please try again.";
        }
    } catch (PDOException $e) {
        $errorMessage = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enter TAC - MZMazwan Banking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <script>
        function moveFocus(current, next) {
            if (current.value.length === current.maxLength) {
                next.focus();
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Transaction Authorization Code (TAC)</h1>
    </header>
    <main>
        <div class="login-box">
            <form action="transaction_verify.php" method="POST">
                <label for="tac_code">Enter your TAC Code:</label><br>
                <input type="text" name="tac_code1" maxlength="1" size="1" required oninput="moveFocus(this, tac_code2)" />
                <input type="text" name="tac_code2" maxlength="1" size="1" required oninput="moveFocus(this, tac_code3)" />
                <input type="text" name="tac_code3" maxlength="1" size="1" required oninput="moveFocus(this, tac_code4)" />
                <input type="text" name="tac_code4" maxlength="1" size="1" required oninput="moveFocus(this, tac_code5)" />
                <input type="text" name="tac_code5" maxlength="1" size="1" required /><br><br>
                <button type="submit">Verify</button>
            </form>
            <?php if ($errorMessage): ?>
                <p class="error"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
            <button onclick="window.location.href='dashboard.php'">Go to Dashboard</button>
        </div>
    </main>
</body>
</html>
