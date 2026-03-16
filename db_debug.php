<?php
require_once 'config/database.php';
echo "<h1>Database Connection Debug</h1>";
echo "<b>Host detected:</b> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<b>Attempting connection to:</b> " . DB_HOST . "<br>";
echo "<b>Database Name:</b> " . DB_NAME . "<br>";
echo "<b>User:</b> " . DB_USER . "<br>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    echo "<h2 style='color: green;'>✅ SUCCESS: Connected to Database!</h2>";
}
catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ FAILED: Connection Error</h2>";
    echo "<b>Error Message:</b> " . $e->getMessage();
}
?>
