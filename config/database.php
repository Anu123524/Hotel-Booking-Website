<?php
if (!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    // Desktop (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'hotel_db');
}
else {
    // Live (InfinityFree)
    define('DB_HOST', 'sql309.infinityfree.com');
    define('DB_USER', 'if0_41269146');
    define('DB_PASS', '1kOw3JgGyebQ0pY');
    define('DB_NAME', 'if0_41269146_hotel');
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    die("DATABASE ERROR: " . $e->getMessage());
}
?>
