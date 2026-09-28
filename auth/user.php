<?php
require_once "db.php";

$stmt = $pdo->query("SELECT * FROM user");

$users = $stmt->fetchAll();

foreach ($users as $user){
    echo $user["username"];
    echo "<br>";
}