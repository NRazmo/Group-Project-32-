<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php"); 
    exit();
}

require 'connection.php'; 

$userID = $_SESSION['userID'];
$sql = "SELECT accountNumber, sortCode, balance FROM Users WHERE userID = :userID";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $accountNumber = $user['accountNumber'];
    $sortCode = $user['sortCode'];
    $balance = number_format($user['balance'], 2); 

    $formattedSortCode = substr($sortCode, 0, 2) . '-' . substr($sortCode, 2, 2) . '-' . substr($sortCode, 4, 2);
} else {
    echo "Error: User data not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>MZMazwan Banking - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <style>
        .banking-details {
            font-size: 24px; 
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <a href="dashboard.php">MZMazwan Banking</a>
        <a href="dashboard.php">
            <img src="Assets/icons8-banking-100.png" alt="Bank" width="50" height="50">
        </a>

        <div class="logout">
            <a href="logout.php"><button>Logout</button></a>
        </div>        
    </header>

    <main>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['fullName']); ?>!</h2>
        <div class="Box">
            <p class="banking-details"><strong>Account Number:</strong> <?php echo htmlspecialchars($accountNumber); ?></p>
            <p class="banking-details"><strong>Sort Code:</strong> <?php echo htmlspecialchars($formattedSortCode); ?></p>
            <p class="banking-details"><strong>Balance:</strong> £<?php echo $balance; ?></p>
            <a href="transfer.php"><button class="transfer-btn">Transfer Money</button></a>
        </div>
    </main>

    <script src="script.js" async defer></script>
</body>
</html>

