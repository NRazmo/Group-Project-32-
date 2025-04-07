<?php
session_start();

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
        header("Location: transaction_verify.php");
        exit();
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