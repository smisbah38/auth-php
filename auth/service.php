<?php 
session_start();
if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Page</title>
</head>
<body>
    <h2>Welcome 
        <?php echo htmlspecialchars($_SESSION["username"]); ?>
</h2>
<p>You're successfully logged in</p>
</body>
</html>