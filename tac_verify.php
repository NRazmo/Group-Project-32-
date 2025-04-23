<?php
session_start();
include('connection.php');

if (!isset($_SESSION['userID']) || !isset($_SESSION['transfer_data'])) {
    header("Location: transfer.php");
    exit();
}

if (!isset($_SESSION['tac_attempts'])) {
    $_SESSION['tac_attempts'] = 0;
}

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tac_code1'], $_POST['tac_code2'], $_POST['tac_code3'], $_POST['tac_code4'], $_POST['tac_code5'])) {
    $tac_code = $_POST['tac_code1'] . $_POST['tac_code2'] . $_POST['tac_code3'] . $_POST['tac_code4'] . $_POST['tac_code5'];
    $userID = $_SESSION['userID'];
    $transferData = $_SESSION['transfer_data'];
    $amount = $transferData['amount'];
    $recipientAccount = $transferData['recipientAccount'];
    $senderID = $transferData['senderID'];

    try {
        $query = "SELECT tacCode FROM TACCodes WHERE userID = :userID";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && trim($user['tacCode']) === trim($tac_code)) {
            unset($_SESSION['tac_attempts']); 
            $conn->beginTransaction();
            try {
                $stmtSender = $conn->prepare("UPDATE Users SET balance = balance - :amount WHERE userID = :senderID");
                $stmtSender->bindParam(':amount', $amount);
                $stmtSender->bindParam(':senderID', $senderID);
                $stmtSender->execute();

                $stmtReceiver = $conn->prepare("SELECT userID FROM Users WHERE accountNumber = :recipientAccount");
                $stmtReceiver->bindParam(':recipientAccount', $recipientAccount);
                $stmtReceiver->execute();
                $receiverResult = $stmtReceiver->fetch(PDO::FETCH_ASSOC);

                if ($receiverResult && isset($receiverResult['userID'])) {
                    $receiverID = $receiverResult['userID'];
                    $stmtReceiverBalance = $conn->prepare("UPDATE Users SET balance = balance + :amount WHERE userID = :receiverID");
                    $stmtReceiverBalance->bindParam(':amount', $amount);
                    $stmtReceiverBalance->bindParam(':receiverID', $receiverID);
                    $stmtReceiverBalance->execute();

                    $stmtTransaction = $conn->prepare("INSERT INTO Transactions (senderID, receiverID, amount, transactionDate, status) VALUES (:senderID, :receiverID, :amount, NOW(), 'Completed')");
                    $stmtTransaction->bindParam(':senderID', $senderID);
                    $stmtTransaction->bindParam(':receiverID', $receiverID);
                    $stmtTransaction->bindParam(':amount', $amount);
                    $stmtTransaction->execute();

                    $conn->commit();
                    $_SESSION['transfer_details'] = $_SESSION['transfer_data'];
                    header("Location: transfer_success.php");
                    exit();
                } else {
                    $conn->rollBack();
                    $errorMessage = "Recipient account not found.";
                }
            } catch (PDOException $e) {
                $conn->rollBack();
                $errorMessage = "Transfer failed due to a database error: " . $e->getMessage();
            }
        } else {
            $_SESSION['tac_attempts']++;
            if ($_SESSION['tac_attempts'] >= 2) {
                unset($_SESSION['tac_attempts']);
                unset($_SESSION['transfer_data']);
                header("Location: dashboard.php");
                exit();
            }
            $errorMessage = "Invalid TAC code. Please try again. You have 1 more attempt!";
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
            <p>Please enter the Transaction Authorization Code (TAC) to authorize the transfer.</p>
            <form action="tac_verify.php" method="POST">
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
        </div>
    </main>
</body>
</html>
