<?php
include 'dbconfig.php';

$user_id = $_POST['user_id'];
$sign_out_time = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("UPDATE logs SET sign_out_time = ? WHERE user_id = ? AND sign_out_time IS NULL");
$stmt->execute([$sign_out_time, $user_id]);
echo "Signed out successfully";
?>