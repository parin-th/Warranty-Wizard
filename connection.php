<?php
// Database file path
$dbfile = 'loginsystem.db'; // Change this to your SQLite database file path

try {
    // Create a new PDO instance
    $conn = new PDO("sqlite:$dbfile");
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // No output to avoid header issues
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$_SESSION['mymail'] = 'warrantytracker1@gmail.com';
$_SESSION['mypw'] = 'ftrr qbtv wkjk sacs';
?>
