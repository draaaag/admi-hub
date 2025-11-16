<?php
session_start();
require_once '../includes/db.php'; // Adjust path if needed

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user from DB
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Save role in session
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($user['role'] === 'moderator') {
            header("Location: ../moderator/dashboard.php");
        } else {
            header("Location: index.php"); // regular student
        }
        exit;
        
    } else {
        $_SESSION['message'] = "<p style='color: red; text-align: center;'>Invalid email or password.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - ADMI Hub</title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body class="centered">
    <div class="form-container">
        <h2>Login</h2>
        <!-- Login form -->
        <form method="POST" action="">
            <label for="loginEmail">Email:</label>
            <input type="email" id="loginEmail" name="email" required><br>
            <label for="loginPassword">Password:</label>
            <input type="password" id="loginPassword" name="password" required><br>
            <button type="submit">Login</button>
            <!-- Register -->
            <p style="text-align: center;">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
        </form>
        <div id="loginMessage">
            <?php
            if (isset($_SESSION['message'])) {
                echo $_SESSION['message'];
                unset($_SESSION['message']);
            }
            ?>
        </div>
    </div>
</body>
</html>
