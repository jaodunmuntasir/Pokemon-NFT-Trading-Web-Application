<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
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
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Email exists, send reset link
        echo "A password reset link has been sent to your email.";
        // Actual email sending functionality not implemented as it requires actual cloud server with email connections etc.
    } else {
        // Email does not exist
        echo "No account found with that email.";
    }

    $conn->close();
}
?>
