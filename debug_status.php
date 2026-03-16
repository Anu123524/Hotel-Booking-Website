<?php
require_once 'config/database.php';
$stmt = $pdo->query("SELECT id, status FROM bookings");
$output = "";
while ($row = $stmt->fetch()) {
    $output .= "ID: " . $row['id'] . " | Status: [" . $row['status'] . "] | Length: " . strlen($row['status']) . "\n";
}
file_put_contents('debug_output.txt', $output);
?>
