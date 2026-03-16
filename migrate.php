<?php
require_once 'config/database.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS room_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
    )");

    $pdo->exec("DELETE FROM room_images");

    $images = [
        [1, 'room1_bed.jpg'], [1, 'room1_bathroom.jpg'],
        [2, 'room2_view.jpg'], [2, 'room2_balcony.jpg'],
        [3, 'room3_suite.jpg'], [3, 'room3_spa.jpg'], [3, 'room3_int.jpg'], [3, 'room3_terrace.jpg'],
        [4, 'room4_bed.jpg'], [4, 'room4_lounge.jpg'], [4, 'room4_workspace.jpg']
    ];

    $stmt = $pdo->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
    foreach ($images as $img) {
        $stmt->execute($img);
    }

    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_email VARCHAR(100) AFTER guest_name");
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_contact VARCHAR(20) AFTER guest_email");
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_address TEXT AFTER guest_contact");
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS arrival_time VARCHAR(20) DEFAULT '12:00 PM' AFTER check_out");
    $pdo->exec("ALTER TABLE bookings ADD COLUMN IF NOT EXISTS is_edited TINYINT(1) DEFAULT 0");

    echo "<h1 style='color: green;'>UNIQUE GALLERY & SCHEMA V2 SYNCED! ✅</h1>";
    echo "<p>Every room now has a 100% unique photo gallery.</p>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";

}
catch (PDOException $e) {
    echo "<h1 style='color: red;'>DATABASE ERROR ❌</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
