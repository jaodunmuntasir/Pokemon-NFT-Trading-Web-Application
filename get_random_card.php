<?php
session_start();
$servername = "localhost";
$username = db_username;
$password = db_password;
$dbname = db_name;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select a random card
$sql = "SELECT card_id, price FROM cards ORDER BY RAND() LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $card = $result->fetch_assoc();
    echo json_encode($card);
} else {
    echo json_encode(["error" => "No cards found"]);
}

$conn->close();
?>
