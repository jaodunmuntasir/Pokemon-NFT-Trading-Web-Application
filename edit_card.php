<?php
session_start();

// Redirect if not logged in or not an admin
if (!isset($_SESSION['loggedin']) || !$_SESSION['is_admin']) {
    exit('Unauthorized access.');
}

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

// Check if a card ID is set
if (isset($_GET['id']) || isset($_POST['card_id'])) {
    $cardId = isset($_GET['id']) ? $_GET['id'] : $_POST['card_id'];

    // If form is submitted, update the card
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Prepare and bind
        $stmt = $conn->prepare("UPDATE cards SET name=?, type=?, hp=?, attack=?, defense=?, price=?, description=?, image_url=? WHERE card_id=?");
        $stmt->bind_param("ssiiidssi", $_POST['name'], $_POST['type'], $_POST['hp'], $_POST['attack'], $_POST['defense'], $_POST['price'], $_POST['description'], $_POST['image_url'], $cardId);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Card updated successfully!";
            header("Location: admin.php");
            exit;
        } else {
            echo "Error updating card: " . $stmt->error;
        }
        $stmt->close();
    }

    // Fetch the current card details for GET request
    $stmt = $conn->prepare("SELECT * FROM cards WHERE card_id = ?");
    $stmt->bind_param("i", $cardId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
?>
        <div style='background: #fff; max-width: 400px; margin: 20px auto; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
            <form id='edit-card-form' method='post' action='edit_card.php' style='display: flex; flex-direction: column; gap: 10px;'>
                <input type='hidden' name='card_id' value='<?php echo htmlspecialchars($row['card_id']); ?>'>
                <label for='name'>Name:</label>
                <input type='text' id='name' name='name' value='<?php echo htmlspecialchars($row['name']); ?>' required>
                <label for='type'>Type:</label>
                <input type='text' id='type' name='type' value='<?php echo htmlspecialchars($row['type']); ?>' required>
                <label for='hp'>HP:</label>
                <input type='number' id='hp' name='hp' value='<?php echo htmlspecialchars($row['hp']); ?>' required>
                <label for='attack'>Attack:</label>
                <input type='number' id='attack' name='attack' value='<?php echo htmlspecialchars($row['attack']); ?>' required>
                <label for='defense'>Defense:</label>
                <input type='number' id='defense' name='defense' value='<?php echo htmlspecialchars($row['defense']); ?>' required>
                <label for='price'>Price:</label>
                <input type='number' id='price' name='price' value='<?php echo htmlspecialchars($row['price']); ?>' required>
                <label for='description'>Description:</label>
                <textarea id='description' name='description' required><?php echo htmlspecialchars($row['description']); ?></textarea>
                <label for='image_url'>Image URL:</label>
                <input type='text' id='image_url' name='image_url' value='<?php echo htmlspecialchars($row['image_url']); ?>' required>
                <input type='submit' value='Save Changes' style='padding: 10px; border: none; border-radius: 4px; background-color: #007bff; color: white; cursor: pointer;'>
            </form>
        </div>
<?php
    } else {
        echo "Card not found.";
    }
    $stmt->close();
} else {
    echo "No card ID provided.";
}

$conn->close();
?>
