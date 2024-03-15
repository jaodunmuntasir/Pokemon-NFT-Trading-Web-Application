<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    exit('Unauthorized access.');
}

if (isset($_GET['id'])) {
    $cardId = $_GET['id'];
    // Database credentials
    $servername = "localhost";
    $username = db_username;
    $password = db_password;
    $dbname = db_name;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM cards WHERE card_id = ?");
    $stmt->bind_param("i", $cardId);
    
    if ($stmt->execute()) {
        echo "Card deleted successfully.";
    } else {
        echo "Error deleting card: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
    
} else {
    echo "No card ID provided.";
}
?>
