<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$servername = "localhost";
$username = db_username;
$password = db_password;
$dbname = db_name;
$userId = $_SESSION['user_id']; // The user's ID stored in the session

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user_sql = "SELECT user_name, email, balance FROM users WHERE user_id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$user_result = $stmt->get_result();
$user_row = $user_result->fetch_assoc();

$username = $user_row['user_name'] ?? 'N/A';
$email = $user_row['email'] ?? 'N/A';
$balance = $user_row['balance'] ?? '0';

// Fetch user's cards based on ownership
$stmt = $conn->prepare("
    SELECT c.* 
    FROM cards AS c
    JOIN ownership AS o ON c.card_id = o.card_id
    WHERE o.owner_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cards_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Details</title>
    <link rel="stylesheet" href="user_details_styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <button id="menu-button" class="menu-button">☰ Menu</button>

            <!-- Navigation Menu -->
            <nav id="navigation-menu" class="navigation-menu hidden">
                <ul>
                    <li><a href="index.php">HomePage</a></li>
                    <li><a href="user_details.php">Profile</a></li>
                    <li><a href="admin.php">Admin</a></li>
                </ul>
            </nav>

            <h1>User Details</h1>

        </header>
        
        <div class="user-info">
            <p><strong>User Name:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Balance:</strong> <?php echo htmlspecialchars($balance); ?></p>
        </div>

        <section class="cards-section">
            <h2>Your Cards</h2>
            <div class="cards-container">
                <?php if ($cards_result->num_rows > 0): ?>
                    <?php while($card_row = $cards_result->fetch_assoc()): ?>
                        <div class='card'>
                            <?php $cardTypeClass = "card-" . strtolower($card_row["type"]); ?>
                            <div class='card <?php echo $cardTypeClass; ?>'>
                            <img src='<?php echo htmlspecialchars($card_row["image_url"]); ?>' alt='<?php echo htmlspecialchars($card_row["name"]); ?>'>
                            </div>
                            <div class='card-info'>
                                <h2><?php echo htmlspecialchars($card_row["name"]); ?></h2>
                                <p class='type'><span class='tag-icon'></span><?php echo htmlspecialchars($card_row["type"]); ?></p>
                                <div class='stats'>
                                    <span class='hp'><span class='heart-icon'></span><?php echo htmlspecialchars($card_row["hp"]); ?></span>
                                    <span class='attack'><span class='swords-icon'></span><?php echo htmlspecialchars($card_row["attack"]); ?></span>
                                    <span class='defense'><span class='shield-icon'></span><?php echo htmlspecialchars($card_row["defense"]); ?></span>
                                </div>
                                <div class='actions'>
                                    <a href='card_details.php?id=<?php echo $card_row["card_id"]; ?>' class='details-button'>View Details</a>
                                    <form method='post' action='sell_card.php'>
                                        <input type='hidden' name='card_id' value='<?php echo $card_row["card_id"]; ?>'>
                                        <input type='hidden' name='user_id' value='<?php echo $userId; ?>'>
                                        <input type='submit' value='Sell Back at 90%'>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>You do not own any cards.</p>
                <?php endif; ?>
            </div>
        </section>
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
