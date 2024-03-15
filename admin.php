<?php
session_start();

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !$_SESSION['is_admin']) {
    echo "Unauthorized access.";
    exit;
}

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

// Handle Add Card Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_card'])) {
    // Get card details from form
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $hp = $_POST['hp'];
    $attack = $_POST['attack'];
    $defense = $_POST['defense'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image_url = $_POST['image_url'];

    // Use a prepared statement to insert the card safely
    $stmt = $conn->prepare("INSERT INTO cards (name, type, hp, attack, defense, price, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiidss", $name, $type, $hp, $attack, $defense, $price, $description, $image_url);


    if ($stmt->execute()) {
        echo "New card added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Set Purchase Limit Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['set_limit'])) {
    // Check if the limit is set and is a number
    if (isset($_POST['purchase_limit']) && is_numeric($_POST['purchase_limit'])) {
        $purchase_limit = $_POST['purchase_limit'];
        
        // Update the purchase limit in the settings table
        $sql = "UPDATE settings SET value = ? WHERE name = 'card_purchase_limit'";
        
        // Prepare and bind
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $purchase_limit);
        
        // Execute and check success
        if ($stmt->execute()) {
            echo "Purchase limit updated successfully.";
        } else {
            echo "Error updating purchase limit: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        echo "Invalid purchase limit.";
    }
}

// Function to load existing cards
function loadCards($conn) {
    $sql = "SELECT card_id, name, type, hp, attack, defense, price, image_url FROM cards";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='card'>";
            echo "<img src='" . htmlspecialchars($row["image_url"]) . "' alt='" . htmlspecialchars($row["name"]) . "'>";
            echo "<div class='card-info'>";
            echo "<h2>" . htmlspecialchars($row["name"]) . "</h2>";
            echo "<p class='type'><span class='tag-icon'></span>" . htmlspecialchars($row["type"]) . "</p>";
            echo "<div class='stats'>";
            echo "<span class='hp'><span class='heart-icon'></span>" . htmlspecialchars($row["hp"]) . "</p>";
            echo "<span class='attack'><span class='swords-icon'></span>" . htmlspecialchars($row["attack"]) . "</p>";
            echo "<span class='defense'><span class='shield-icon'></span>" . htmlspecialchars($row["defense"]) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>No cards found.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin_styles.css">
    <script src="admin.js" defer></script>
</head>
<body>
    <header>
        <button id="menu-button" class="menu-button">☰ Menu</button>

        <!-- Navigation Menu -->
        <nav id="navigation-menu" class="navigation-menu hidden">
            <ul>
                <li><a href="index.php">HomePage</a></li>
                <li><a href="user_details.php">Profile</a></li>
                <li><a href="admin.php">Admin</a></li>
            </ul>
        </nav>

        <div class="admin-header">
            <h1>Admin Panel</h1>
        </div>
    </header>

    
    
    <div class="admin-container">
        <div class="admin-sidebar">
            <button class="sidebar-btn" onclick="showSettings()">Settings</button>
            <button class="sidebar-btn" onclick="showCards()">Cards</button>
        </div>

        <div class="admin-content">
            <div class="settings-section" id="settings-section" style="display: none;">
                <form id="set-limit-form" action="admin.php" method="post">
                    <h2>Set Purchase Limit</h2>
                    <input type="number" id="purchase_limit" name="purchase_limit" min="1" required>
                    <button type="submit" name="set_limit">Set Limit</button>
                </form>
            </div>

            <div class="cards-section" id="cards-section">
                <h2>Manage Cards</h2>
                <div class="add-card">
                    <button type="button" onclick="openAddCardForm()">+</button>
                    <p>Add New Card</p>
                </div>
                <div id="formContainer"></div>
                <div class="card-list" id="cards-list">
                    <?php loadCards($conn); ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Get the menu button and navigation menu elements
        var menuButton = document.getElementById('menu-button');
        var navigationMenu = document.getElementById('navigation-menu');

        // Event listener for the menu button click
        menuButton.addEventListener('click', function() {
            // Toggle the 'hidden' class to show/hide the navigation menu
            navigationMenu.classList.toggle('hidden');
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>