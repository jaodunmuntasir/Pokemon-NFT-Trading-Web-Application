<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    
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

    // Get user input
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare the INSERT statement
    $sql = "INSERT INTO users (user_name, email, password, isAdmin) VALUES (?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);

    if (false === $stmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind the parameters to the SQL query
    $bind = $stmt->bind_param("sss", $user_name, $email, $hashed_password);

    if (false === $bind) {
        die('Bind param failed: ' . htmlspecialchars($stmt->error));
    }

    // Execute the prepared statement
    $exec = $stmt->execute();

    if (false === $exec) {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    } else {
        echo "Registration successful!";
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();
}
?>