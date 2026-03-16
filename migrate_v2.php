<?php
require_once 'config/database.php';

echo "<h2>Grand Vista Database Migration</h2>";

try {
    // 1. Create room_images table for gallery
    $pdo->exec("CREATE TABLE IF NOT EXISTS room_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_id INT,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
    )");
    echo "✅ Room images table verified.<br>";

    // 2. Add columns to bookings if they don't exist
    $columns = $pdo->query("SHOW COLUMNS FROM bookings")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('arrival_time', $columns)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN arrival_time VARCHAR(20) DEFAULT NULL");
        echo "✅ Added arrival_time to bookings.<br>";
    }

    if (!in_array('is_edited', $columns)) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN is_edited TINYINT(1) DEFAULT 0");
        echo "✅ Added is_edited to bookings.<br>";
    }

    echo "<br><b>Migration completed successfully!</b>";
}
catch (PDOException $e) {
    echo "❌ Error during migration: " . $e->getMessage();
}
?>
