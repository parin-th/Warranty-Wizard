<?php
session_start();
include('connect/connection.php');

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch items for the calendar
$sql = "SELECT name, end_date FROM items WHERE user_id = $user_id";
$result = $conn->query($sql);

$events = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => $row['name'],
            'start' => $row['end_date'],
            'end' => $row['end_date']
        ];
    }
}

// Return events in JSON format
header('Content-Type: application/json');
echo json_encode($events);

$conn->close();
?>
