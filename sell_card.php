<?php
session_start();

// Redirect if not logged in or if card_id or user_id is not set in POST request
if (!isset($_SESSION['loggedin']) || !isset($_POST['card_id']) || !isset($_POST['user_id'])) {
    header('Location: login.php');
    exit;
}

$cardId = $_POST['card_id'];
$userId = $_POST['user_id'];
$adminId = 2;

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

// Begin transaction
$conn->begin_transaction();

try {
    // Fetch card price
    $price_stmt = $conn->prepare("SELECT price FROM cards WHERE card_id = ?");
    $price_stmt->bind_param("i", $cardId);
    $price_stmt->execute();
    $price_result = $price_stmt->get_result();
    if ($price_result->num_rows === 0) {
        throw new Exception("Card not found.");
    }

    $card_row = $price_result->fetch_assoc();
    $salePrice = $card_row['price'] * 0.9; // Calculate sale price at 90%

    // Update user's balance
    $balance_stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
    $balance_stmt->bind_param("di", $salePrice, $userId);
    $balance_stmt->execute();
    if ($balance_stmt->affected_rows === 0) {
        throw new Exception("Failed to update user's balance.");
    }

    // Insert the transaction record
    $transaction_stmt = $conn->prepare("INSERT INTO transactions (seller_id, buyer_id, card_id, transaction_date, amount) VALUES (?, ?, ?, NOW(), ?)");
    $transaction_stmt->bind_param("iiid", $userId, $adminId, $cardId, $salePrice);
    $transaction_stmt->execute();
    if ($transaction_stmt->affected_rows === 0) {
        throw new Exception("Failed to record the transaction.");
    }

    // Update ownership to admin
    $ownership_stmt = $conn->prepare("UPDATE ownership SET owner_id = ? WHERE card_id = ? AND owner_id = ?");
    $ownership_stmt->bind_param("iii", $adminId, $cardId, $userId);
    $ownership_stmt->execute();
    if ($ownership_stmt->affected_rows === 0) {
        throw new Exception("Failed to update card ownership.");
    }

    // Commit transaction
    $conn->commit();
    $_SESSION['message'] = "Card sold successfully!";
    header('Location: user_details.php');
    exit;
} catch (Exception $e) {
    // Rollback transaction if any step fails
    $conn->rollback();
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: user_details.php');
    exit;
}

$conn->close();
?>
