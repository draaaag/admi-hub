<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "<p style='color:red;'>Email already registered.</p>";
        header("Location: register.php");
        exit();
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$first_name, $last_name, $email, $password])) {
            // Log the user in immediately
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
    
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['message'] = "<p style='color:red;'>Registration failed. Try again.</p>";
            header("Location: register.php");
            exit();
        }
    }    
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - ADMI Hub</title>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body class="centered">
    <div class="form-container">
        <h2>Register</h2>

        <!-- Registration form -->
        <form method="POST" action="register.php">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required><br>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <button type="submit">Register</button>
        </form>
                    <!-- login-->
            <p style="text-align: center;">
                Already have an account? <a href="login.php">login here</a>
            </p>
        

        <!-- Display any message -->
        <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
        ?>
    </div>
</body>
</html>
