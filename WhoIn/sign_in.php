<?php
include 'dbconfig.php';

$user_id = $_POST['user_id'];
$sign_in_time = date('Y-m-d H:i:s');

// Check if user already signed in
$check = $pdo->prepare("SELECT * FROM logs WHERE user_id = ? AND sign_out_time IS NULL");
$check->execute([$user_id]);

if ($check->rowCount() == 0) {
    $stmt = $pdo->prepare("INSERT INTO logs (user_id, sign_in_time) VALUES (?, ?)");
    $stmt->execute([$user_id, $sign_in_time]);
    echo "Signed in successfully";
} else {
    echo "User already signed in";
}
?>