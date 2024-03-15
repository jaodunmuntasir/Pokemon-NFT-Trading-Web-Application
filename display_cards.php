<?php
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

$sql = "SELECT card_id, name, type, hp, attack, defense, price, image_url FROM cards";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='card'>
                <p><strong>Name:</strong> " . $row["name"] . "</p>
                <p><strong>Type:</strong> " . $row["type"] . "</p>
                <!-- Add more card details here if needed -->
                <button onclick='editCard(" . $row["card_id"] . ")'>Edit</button>
                <button onclick='deleteCard(" . $row["card_id"] . ")'>Delete</button>
              </div>";
    }
} else {
    echo "0 results";
}

$conn->close();
?>
