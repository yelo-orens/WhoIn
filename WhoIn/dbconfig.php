<?php
// Set the server timezone for consistent PHP timestamps
date_default_timezone_set('Asia/Manila');

$host = 'localhost';
$dbname = 'whoin';       
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>