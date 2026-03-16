<?php
require_once 'config/database.php';

try {
    $stmt = $pdo->prepare("UPDATE rooms SET adults = 2, children = 1");
    $stmt->execute();
    echo "Successfully updated all room capacities to 2 Adults, 1 Kid.\n";

    // Also update the migrate.php for future-proofing
    $migrate_path = 'migrate.php';
    if (file_exists($migrate_path)) {
        $content = file_get_contents($migrate_path);
        // This is a bit fragile but good for consistency
        $content = preg_replace("/'adults' => \d+/", "'adults' => 2", $content);
        $content = preg_replace("/'children' => \d+/", "'children' => 1", $content);
        file_put_contents($migrate_path, $content);
        echo "Updated migrate.php templates.\n";
    }
}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
