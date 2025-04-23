<?php
session_start();
include('connection.php');

if (!isset($_SESSION['userID']) || !isset($_SESSION['require_tac'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['tac_attempts'])) {
    $_SESSION['tac_attempts'] = 0;
}

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tac_code1'], $_POST['tac_code2'], $_POST['tac_code3'], $_POST['tac_code4'], $_POST['tac_code5'])) {
    $tac_code_input = $_POST['tac_code1'] . $_POST['tac_code2'] . $_POST['tac_code3'] . $_POST['tac_code4'] . $_POST['tac_code5'];
    $userID = $_SESSION['userID'];

    try {
        $stmt = $conn->prepare("SELECT tacCode FROM TACCodes WHERE userID = :userID ORDER BY createdAt DESC LIMIT 1");
        $stmt->bindParam(':userID', $userID);
        $stmt->execute();
        $userTacResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $userTac = $userTacResult ? trim($userTacResult['tacCode']) : '';

        if (trim($tac_code_input) === $userTac) {
            unset($_SESSION['tac_attempts'], $_SESSION['require_tac']);
            $updateLogin = $conn->prepare("UPDATE Users SET lastLogin = NOW() WHERE userID = :userID");
            $updateLogin->bindParam(':userID', $userID);
            $updateLogin->execute();
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['tac_attempts']++;
            if ($_SESSION['tac_attempts'] >= 2) {
                session_destroy();
                header("Location: login.php");
                exit();
            }
            $errorMessage = "Invalid TAC code. Please try again. You have 1 more attempt.";
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
            if (current.value.length === current.maxLength && next) {
                next.focus();
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Login Authorization Code (TAC)</h1>
    </header>

    <main>
        <div class="login-box">
            <form action="login_tac_verify.php" method="POST">
                <label for="tac_code">Enter your TAC Code:</label><br>
                <input type="text" name="tac_code1" maxlength="1" size="1" required oninput="moveFocus(this, this.form.tac_code2)" />
                <input type="text" name="tac_code2" maxlength="1" size="1" required oninput="moveFocus(this, this.form.tac_code3)" />
                <input type="text" name="tac_code3" maxlength="1" size="1" required oninput="moveFocus(this, this.form.tac_code4)" />
                <input type="text" name="tac_code4" maxlength="1" size="1" required oninput="moveFocus(this, this.form.tac_code5)" />
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