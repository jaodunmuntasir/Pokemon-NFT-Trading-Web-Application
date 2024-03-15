<?php
session_start();

if (!isset($_SESSION['loggedin'], $_GET['card_id'])) {
    echo "Unauthorized access.";
    exit;
}

$userId = $_SESSION['user_id'];
$cardId = $_GET['card_id'];

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

// Start transaction
$conn->begin_transaction();

try {
    // Function to check if user has reached the purchase limit
    function userCanPurchase($userId, $conn, $purchase_limit) {
        $sql = "SELECT COUNT(*) as card_count FROM transactions WHERE buyer_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['card_count'] < $purchase_limit;
    }

    // Fetch the purchase limit from settings
    $limit_sql = "SELECT value FROM settings WHERE name='card_purchase_limit'";
    $limit_result = $conn->query($limit_sql);
    $purchase_limit_row = $limit_result->fetch_assoc();
    $purchase_limit = $purchase_limit_row['value'];

    // Check if user can purchase
    if (!userCanPurchase($userId, $conn, $purchase_limit)) {
        throw new Exception("Purchase limit reached.");
    }

    // Fetch the current owner (seller) id
    $ownership_sql = "SELECT owner_id FROM ownership WHERE card_id = ? AND owner_id <> ?";
    $ownership_stmt = $conn->prepare($ownership_sql);
    $ownership_stmt->bind_param("ii", $cardId, $userId);
    $ownership_stmt->execute();
    $ownership_result = $ownership_stmt->get_result();
    if ($ownership_result->num_rows == 0) {
        throw new Exception("This card is not available for purchase.");
    }
    $ownership_row = $ownership_result->fetch_assoc();
    $sellerId = $ownership_row['owner_id'];

    // Fetch the card price
    $price_sql = "SELECT price FROM cards WHERE card_id = ?";
    $price_stmt = $conn->prepare($price_sql);
    $price_stmt->bind_param("i", $cardId);
    $price_stmt->execute();
    $price_result = $price_stmt->get_result();
    if ($price_result->num_rows == 0) {
        throw new Exception("Card not found.");
    }
    $price_row = $price_result->fetch_assoc();
    $cardPrice = $price_row['price'];

    // Insert the transaction
    $transaction_sql = "INSERT INTO transactions (buyer_id, seller_id, card_id, transaction_date, amount) VALUES (?, ?, ?, NOW(), ?)";
    $transaction_stmt = $conn->prepare($transaction_sql);
    $transaction_stmt->bind_param("iiid", $userId, $sellerId, $cardId, $cardPrice);
    $transaction_stmt->execute();

    // Delete the old ownership record
    $delete_ownership_sql = "DELETE FROM ownership WHERE card_id = ? AND owner_id = ?";
    $delete_ownership_stmt = $conn->prepare($delete_ownership_sql);
    $delete_ownership_stmt->bind_param("ii", $cardId, $sellerId);
    $delete_ownership_stmt->execute();

    // Insert the new ownership record
    $new_ownership_sql = "INSERT INTO ownership (owner_id, card_id, acquired_date) VALUES (?, ?, NOW())";
    $new_ownership_stmt = $conn->prepare($new_ownership_sql);
    $new_ownership_stmt->bind_param("ii", $userId, $cardId);
    $new_ownership_stmt->execute();

    // Commit transaction
    $conn->commit();

    echo "Purchase successful!";
} catch (mysqli_sql_exception $exception) {
    // Rollback transaction
    $conn->rollback();
    echo "Error during purchase: " . $exception->getMessage();
}

$conn->close();
?>
