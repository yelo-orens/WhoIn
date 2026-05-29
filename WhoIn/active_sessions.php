<?php
// Direct connection with correct database name 'whoin'
try {
    $pdo = new PDO("mysql:host=localhost;dbname=whoin;charset=utf8", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "<div class='alert-dark warning'>Database Error: " . $e->getMessage() . "</div>";
    exit();
}

$stmt = $pdo->query("
    SELECT u.first_name, u.last_name, l.sign_in_time 
    FROM logs l 
    JOIN users u ON l.user_id = u.user_id 
    WHERE l.sign_out_time IS NULL 
    ORDER BY l.sign_in_time DESC
");

if ($stmt->rowCount() == 0) {
    echo "<div class='empty-state'>
            <div class='empty-state-icon'><i class='fas fa-users-slash'></i></div>
            <p class='empty-state-text'>No one is currently inside</p>
          </div>";
} else {
    echo "<ul class='active-list'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $firstName = htmlspecialchars($row['first_name']);
        $lastName = htmlspecialchars($row['last_name']);
        $initials = strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
        $time = date('h:i A', strtotime($row['sign_in_time']));
        
        echo "<li class='active-list-item'>
                <div class='active-avatar'>{$initials}</div>
                <div class='active-info'>
                    <div class='active-name'>{$firstName} {$lastName}</div>
                    <div class='active-time'>Signed in: {$time}</div>
                </div>
                <span class='active-badge inside'>
                    <span class='pulse-dot'></span>
                    Inside
                </span>
              </li>";
    }
    echo "</ul>";
}
?>