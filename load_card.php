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
        echo "<div class='card'>";
        $cardTypeClass = "card-" . strtolower($row["type"]);
        echo "<div class='card $cardTypeClass'>";
        echo "<img src='" . htmlspecialchars($row["image_url"]) . "' alt='" . htmlspecialchars($row["name"]) . "'>";
        echo "</div>";
        echo "<div class='card-info'>";
        echo "<h2>" . htmlspecialchars($row["name"]) . "</h2>";
        echo "<p class='type'><span class='tag-icon'></span>" . htmlspecialchars($row["type"]) . "</p>";
        echo "<div class='stats'>";
        echo "<span class='hp'><span class='heart-icon'></span>" . htmlspecialchars($row["hp"]) . "</p>";
        echo "<span class='attack'><span class='swords-icon'></span>" . htmlspecialchars($row["attack"]) . "</p>";
        echo "<span class='defense'><span class='shield-icon'></span>" . htmlspecialchars($row["defense"]) . "</p>";
        echo "</div>";
        echo "<div class='actions'>";
        echo "<button class='action' onclick='editCard(" . $row["card_id"] . ")'>Edit</button>";
        echo "<button class='action' onclick='deleteCard(" . $row["card_id"] . ")'>Delete</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>No cards found.</p>";
}

$conn->close();
?>
