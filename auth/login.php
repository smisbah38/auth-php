<?php

date_default_timezone_set("Asia/Dhaka");

session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get login information
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Find user by username
    $stmt = $pdo->prepare(
        "SELECT * FROM user WHERE username = :username"
    );

    $stmt->execute([
        "username" => $username
    ]);

    $user = $stmt->fetch();

    // Check username and password
    if ($user && password_verify($password, $user["password"])) {

        // Session id regeneration
        session_regenerate_id(true);

        // Random OTP
        $otp = random_int(100000, 999999);

        // Hashing the OTP
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        // OTP expires after 5 minutes
        $expiresAt = date(
            "Y-m-d H:i:s",
            time() + 300
        );

        // Store OTP in database
        $stmt = $pdo->prepare(
            "INSERT INTO otp 
            (user_id, otp_hash, expires_at, attempts)
            VALUES
            (:user_id, :otp_hash, :expires_at, 0)"
        );

        $stmt->execute([
            "user_id" => $user["id"],
            "otp_hash" => $otpHash,
            "expires_at" => $expiresAt
        ]);

        // Get the ID of the OTP record we just created
        $otpId = $pdo->lastInsertId();

        // Store temporary authentication information
        $_SESSION["pending_user_id"] = $user["id"];
        $_SESSION["pending_username"] = $user["username"];

        // Store the specific OTP record ID
        $_SESSION["pending_otp_id"] = $otpId;

        // TEMPORARY:
        // Store OTP in session so we can see it during testing.
        // Remove this when SMS API is connected.
        $_SESSION["test_otp"] = $otp;

        // Go to OTP verification page
        header("Location: verify_otp.php");
        exit;

    } else {

        // Username or password is incorrect
        $error = "Incorrect username or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css">
    <title>Login</title>
</head>

<body>

    <div class="contents">
        <h2>Login</h2>
        <br>

    <?php if ($error): ?>

        <p style="color: red;">
            <?= htmlspecialchars($error) ?>
        </p>

    <?php endif; ?>

<br>
    <form method="POST">

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >

        <br><br>


        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <br><br>


        <button type="submit">
            Login
        </button>

    </form>
    </div>

</body>

</html>