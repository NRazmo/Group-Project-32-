<?php
session_start();

if (!isset($_SESSION['userID']) || !isset($_SESSION['transfer_data'])) {
    header("Location: dashboard.php"); 
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userAnswer = $_POST['answer'];
    $correctAnswer = $_SESSION['traffic_light_answer'];

    if ($userAnswer === $correctAnswer) {
        unset($_SESSION['traffic_light_answer']);
        header("Location: transaction_verify.php");
        exit();
    } else {
        $error = "Incorrect answer. Please try again.";
    }
}

$answers = ["Traffic Lights", "Road Signs", "Street Lights"];
$correctAnswer = $answers[0]; 
shuffle($answers); 
$_SESSION['traffic_light_answer'] = $correctAnswer;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Verification</title>
</head>
<body>
    <div class="verification-box">
        <h1>Verify the Image</h1>
        <img src="Assets/Picture1.png" alt="Traffic Lights">
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="traffic_lights_verification.php">
            <label>What is this image?</label><br>
            <?php foreach ($answers as $answer): ?>
                <div class="answer-option">
                    <input type="radio" id="<?php echo $answer; ?>" name="answer" value="<?php echo $answer; ?>" required>
                    <label for="<?php echo $answer; ?>"><?php echo $answer; ?></label>
                </div>
            <?php endforeach; ?>
            <br>
            <button type="submit">Verify</button>
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

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        button:hover {
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