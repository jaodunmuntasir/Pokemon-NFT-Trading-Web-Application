<?php
session_start();

$cardsPerPage = 9;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $cardsPerPage;

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

// Fetch card purchase limit set by admin
$limit_sql = "SELECT value FROM settings WHERE name='card_purchase_limit'";
$limit_result = $conn->query($limit_sql);
$purchase_limit = $limit_result->fetch_assoc()['value'];

// Function to check if user has reached the purchase limit
function userCanPurchase($userId, $conn, $purchase_limit) {
    $sql = "SELECT COUNT(*) as card_count FROM transactions WHERE buyer_id = '$userId'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['card_count'] < $purchase_limit;
    }
    return true; // If no purchases are found, allow purchase
}

// Fetch unique card types
$type_sql = "SELECT DISTINCT type FROM cards";
$type_result = $conn->query($type_sql);
$types = [];
while ($type_row = $type_result->fetch_assoc()) {
    $types[] = $type_row['type'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Cards</title>
    <link rel="stylesheet" href="main_page_styles.css">
</head>
<body>
    <header>
        <!-- Menu Button -->
        <button id="menu-button" class="menu-button">☰ Menu</button>

        <!-- Navigation Menu -->
        <nav id="navigation-menu" class="navigation-menu hidden">
            <ul>
                <li><a href="index.php">HomePage</a></li>
                <li><a href="user_details.php">Profile</a></li>
                <li><a href="admin.php">Admin</a></li>
            </ul>
        </nav>

        <div class="title-container">
            <h1>Welcome to Pokémon Card Trader</h1>
            <p>Explore our vast collection of Pokémon cards and trade with others!</p>
        </div>

        <div class="login-container">
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
                echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . "</p>";
                echo "<a href='logout.php' class='logout-button'>Logout</a>";
            } else {
                echo "<a href='login.php' class='login-button'>Login</a>";
            }
            ?>
        </div>
    </header>

    <div class="sidebar">
        <h3>Filter by Type</h3>
        <?php foreach ($types as $type): ?>
            <div>
                <input type="checkbox" class="type-filter" value="<?php echo htmlspecialchars($type); ?>" id="type-<?php echo htmlspecialchars($type); ?>">
                <label for="type-<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></label>
            </div>
        <?php endforeach; ?>

        <!-- Button to Buy a Random Card -->
        <div class="random-card-button-container">
            <button onclick="buyRandomCard()">Buy a Random Card</button>
        </div>
    </div>
    
    <main>

    <section class="cards-container">
        <?php
        // $sql = "SELECT * FROM cards";
        $sql = "SELECT * FROM cards LIMIT $cardsPerPage OFFSET $offset";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $cardTypeClass = "" . strtolower($row["type"]);
                echo "<div class='card'>";
                echo "<div class='$cardTypeClass'>";
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
                echo "<a href='card_details.php?id=" . $row["card_id"] . "' class='details-button'>View Details</a>";
                
                // Show Buy button if user is logged in and hasn't reached the purchase limit
                if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && userCanPurchase($_SESSION['user_id'], $conn, $purchase_limit)) {
                    echo "<button class='buy-button' onclick='buyCard(" . $row["card_id"] . ")'><img src='https://clipart-library.com/images/rcnKKqndi.png' alt='Buy Icon'>" . $row["price"] . "</button>";
                }

                echo "</div>"; // Close .actions div
                echo "</div>"; // Close .card-info div
                echo "</div>"; // Close .card div
            }
        } else {
            echo "<p>No cards found.</p>";
        }

        // Pagination Links
        // Calculate total number of pages
        $countSql = "SELECT COUNT(*) FROM cards";
        $countResult = $conn->query($countSql);
        $totalCountRow = $countResult->fetch_row();
        $totalCards = $totalCountRow[0];
        $totalPages = ceil($totalCards / $cardsPerPage);

        // Display pagination links
        echo "<div class='pagination'>";
        for ($i = 1; $i <= $totalPages; $i++) {
            echo "<a href='index.php?page=$i'>" . $i . "</a> ";
        }
        echo "</div>";

        $conn->close();
        ?>
    </section>
    </main>

    <script src="index.js"></script>

</body>
</html>
