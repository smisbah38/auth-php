<?php

session_start();
require_once "db.php";

$error = "";

// Make sure the user actually came through the login process
if (!isset($_SESSION["pending_user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["pending_user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $enteredOtp = trim($_POST["otp"]);

    // Find the latest OTP for this user
    $stmt = $pdo->prepare(
        "SELECT * FROM otp
         WHERE user_id = :user_id
         ORDER BY id DESC
         LIMIT 1"
    );

    $stmt->execute([
        "user_id" => $userId
    ]);

    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {

        $error = "No OTP found.";

    } elseif (strtotime($otpRecord["expires_at"]) < time()) {

        $error = "OTP has expired.";

    } elseif ($otpRecord["attempts"] >= 5) {

        $error = "Too many incorrect attempts.";

        // Redirect after displaying the error
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
              </script>";

    } elseif (password_verify($enteredOtp, $otpRecord["otp_hash"])) {

        // OTP is correct.
        // Now the user is fully authenticated.

        session_regenerate_id(true);

        $_SESSION["user_id"] = $userId;
        $_SESSION["username"] = $_SESSION["pending_username"];

        // Remove temporary authentication data
        unset($_SESSION["pending_user_id"]);
        unset($_SESSION["pending_username"]);
        unset($_SESSION["test_otp"]);

        // Go to the protected service page
        header("Location: service.php");
        exit;

    } else {

        // Increase failed attempt count
        $stmt = $pdo->prepare(
            "UPDATE otp
             SET attempts = attempts + 1
             WHERE id = :id"
        );

        $stmt->execute([
            "id" => $otpRecord["id"]
        ]);

        // Check if this was the 5th incorrect attempt
        if ($otpRecord["attempts"] + 1 >= 5) {

            $error = "Too many incorrect attempts.";

            // Redirect to login after 2 seconds
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                  </script>";

        } else {

            $error = "Incorrect OTP.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css">
    <title>OTP Verification</title>
</head>

<body>

    <div class="contents">

        <h2>OTP Verification</h2>

        <br>

        <?php if ($error): ?>

            <p style="color: red;">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php endif; ?>

        <!-- TEMPORARY TESTING ONLY -->
        <?php if (isset($_SESSION["test_otp"])): ?>

            <br>

            <p style="color: blue;">
                Test OTP:
                <strong>
                    <?= htmlspecialchars($_SESSION["test_otp"]) ?>
                </strong>
            </p>

            <br>

        <?php endif; ?>

        <form method="post">

            <input
                type="text"
                name="otp"
                placeholder="Enter 6-digit OTP"
                maxlength="6"
                required
            >

            <br><br>

            <button type="submit">Verify OTP</button>

        </form>

    </div>

</body>

</html>

