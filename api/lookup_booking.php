<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$email = $_GET['email'] ?? '';
$id = $_GET['id'] ?? '';

if (!$email || !$id) {
    echo json_encode(['success' => false, 'message' => 'Missing details.']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT b.*, r.name as room_name FROM bookings b JOIN rooms r ON b.room_id = r.id WHERE b.id = ? AND b.guest_email = ?");
    $stmt->execute([$id, $email]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        echo json_encode(['success' => true, 'booking' => $booking]);
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Booking not found.']);
    }
}
catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error.']);
}
?>
