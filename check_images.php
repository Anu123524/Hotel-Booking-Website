<?php
require_once 'c:/xampp/htdocs/grand-vista-hotel/config/database.php';
try {
    $stmt = $pdo->query("SELECT * FROM rooms");
    $rooms = $stmt->fetchAll();
    echo "ROOMS:\n";
    foreach ($rooms as $r) {
        echo "ID: " . $r['id'] . " | Name: " . $r['name'] . "\n";
    }

    $stmt = $pdo->query("SELECT * FROM room_images");
    $images = $stmt->fetchAll();
    echo "\nIMAGES:\n";
    foreach ($images as $i) {
        echo "Room ID: " . $i['room_id'] . " | Image: " . $i['image_path'] . "\n";
    }
}
catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
