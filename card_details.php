<?php
session_start();

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

// Initialize variables
$cardDetails = null;

// Check if card ID is set
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $cardId = $_GET['id'];

    // Fetch the card details
    $stmt = $conn->prepare("SELECT * FROM cards WHERE card_id = ?");
    $stmt->bind_param("i", $cardId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cardDetails = $result->fetch_assoc();
    } else {
        $errorMsg = "Card not found.";
    }

    $stmt->close();
    $conn->close();
} else {
    $errorMsg = "No card ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Card Details</title>
    <link rel="stylesheet" href="card_details_style.css">
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
    </header>

    <div class="card-details-container">
        <?php if ($cardDetails): ?>
            <div class="card-image">
                <?php $cardTypeClass = "card-" . strtolower($cardDetails['type']); ?>
                <div class='card <?php echo $cardTypeClass; ?>'>
                <img src="<?php echo htmlspecialchars($cardDetails['image_url']); ?>" alt="<?php echo htmlspecialchars($cardDetails['name']); ?>">
                </div>
            </div>
            <div class="card-info">
                <h1><?php echo htmlspecialchars($cardDetails['name']); ?></h1>
                <p>Type: <?php echo htmlspecialchars($cardDetails['type']); ?></p>
                <p>HP: <?php echo htmlspecialchars($cardDetails['hp']); ?></p>
                <p>Attack: <?php echo htmlspecialchars($cardDetails['attack']); ?></p>
                <p>Defense: <?php echo htmlspecialchars($cardDetails['defense']); ?></p>
                <p>Price: <?php echo htmlspecialchars($cardDetails['price']); ?></p>
                <p>Description: <?php echo htmlspecialchars($cardDetails['description']); ?></p>
            </div>
        <?php else: ?>
            <p><?php echo $errorMsg; ?></p>
        <?php endif; ?>
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
