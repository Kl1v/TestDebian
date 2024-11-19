<?php
session_start();
$user_id = $_SESSION['user_id'];

$today = date('Y-m-d');
$sql = "SELECT last_bonus_date FROM users WHERE id=$user_id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['last_bonus_date'] !== $today) {
    $sql = "UPDATE users SET balance = balance + 1000, last_bonus_date='$today' WHERE id=$user_id";
    if ($conn->query($sql) === TRUE) {
        echo "1000€ wurden Ihrem Konto gutgeschrieben!";
    }
}
?>
