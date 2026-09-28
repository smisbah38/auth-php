<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];

    // Hash the password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query
    $stmt = $pdo->prepare(
        "INSERT INTO user (username, password, email, phone)
        VALUES (:username, :password, :email, :phone)"
    );

    // Execute the query
    $stmt->execute([
        "username" => $username,
        "password" => $hashedPassword,
        "email" => $email,
        "phone" => $phone
    ]);

    // Show success message
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Registration Successful</title>
    </head>

    <body>

        <h2>Registration Successful!</h2>
        <p>Redirecting you to the login page...</p>

        <script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 2000);
        </script>

    </body>
    </html>
    ";

    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/index.css">
    <title>Sign Up</title>
</head>

<body>
    <div class="contents">
        <h2>Sign Up</h2>
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

        <input 
            type="email"
            name="email"
            placeholder="Email"
            required
        >

        <br><br>

        <input 
            type="text"
            name="phone"
            placeholder="Phone Number"
            required
        >

        <br><br>

        <button type="submit">Sign Up</button>

    </form>
    </div>

    

</body>
</html>