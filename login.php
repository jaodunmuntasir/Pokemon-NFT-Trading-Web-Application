<?php
session_start();
ob_start(); // Start output buffering

// If the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    
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
    $input_username = mysqli_real_escape_string($conn, $_POST['username']);
    $input_password = mysqli_real_escape_string($conn, $_POST['password']);

    // SQL query to check the user
    $sql = "SELECT * FROM users WHERE user_name = '$input_username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($input_password, $row['password'])) {
            // After successful password verification
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['user_name'];
            $_SESSION['is_admin'] = (int)$row['isAdmin'] === 1; // Cast to int for strict comparison
            $_SESSION['user_id'] = $row['user_id'];

            header("location: index.php");
            exit;
        } else {
            $login_error = "Invalid password.";
        }
    } else {
        $login_error = "Invalid username.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login_styles.css">
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="card-header">
                <h2>Login to your Ikemon Account</h2>
            </div>
            <div class="card-body">
                <!-- Login Form -->
                <form action="login.php" method="post" class="login-form">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="login" class="btn-login">Login</button>
                        <div class="links">
                            <a href="#" onclick="showPopup('register-popup')">Register</a> |
                            <a href="#" onclick="showPopup('forgot-password-popup')">Forgot Password?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="register-popup" class="popup">
            <div class="popup-content">
                <span class="close" onclick="closePopup('register-popup')">&times;</span>
                <div class="form-container">
                <h2>User Registration</h2>
                    <form id="registration-form" action="register.php" method="post" class="form-container">
                        <div class="form-group">
                            <input type="text" id="reg-username" name="user_name" required placeholder="Username">
                        </div>
                        <div class="form-group">
                            <input type="email" id="reg-email" name="email" required placeholder="Email">
                        </div>
                        <div class="form-group">
                            <input type="password" id="reg-password" name="password" required placeholder="Password">
                        </div>
                        <div class="form-group">
                            <input type="password" id="reg-confirm-password" name="confirm_password" required placeholder="Confirm Password">
                        </div>
                        <button type="submit" name="register" class="btn btn-primary">Register</button>
                    </form>            
                </div>
            </div>
        </div>

            <div id="forgot-password-popup" class="popup">
                <div class="popup-content">
                    <span class="close" onclick="closePopup('forgot-password-popup')">&times;</span>
                    <h2>Forgot Password</h2>
                    <form id="forgot-password-form" action="forgot_password.php" method="post" class="form-container">
                        <div class="form-group">
                            <input type="email" id="fp-email" name="email" required placeholder="Email">
                        </div>
                        <button type="submit" name="reset_password" class="btn btn-primary">Reset Password</button>
                    </form>            
                </div>
            </div>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>
