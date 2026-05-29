<?php
include 'dbconfig.php';
session_start();

$id_number = $_POST['id_number'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id_number = ?");
$stmt->execute([$id_number]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['exists' => true, 'user_id' => $user['user_id'], 
                      'first_name' => $user['first_name'], 'last_name' => $user['last_name'],
                      'email' => $user['email'], 'contact_number' => $user['contact_number']]);
} else {
    echo json_encode(['exists' => false]);
}
?>