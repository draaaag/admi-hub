<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Public/login.php");
    exit;
}

include '../includes/db.php';

$errors = [];
$success = "";

// Handle form submission to add user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (!$first_name || !$last_name || !$email || !$password || !$role) {
        $errors[] = "All fields are required.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$first_name, $last_name, $email, $hashedPassword, $role])) {
            $success = "User added successfully.";
        } else {
            $errors[] = "Failed to add user.";
        }
    }
}

// Fetch all users
$users = $pdo->query("SELECT id, first_name, last_name, email, role FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Users</title>
<style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f4f4f4; color: #333; }
    header { background: #222; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
    header h1 { font-family: Times, serif; margin: 0; font-size: 1.6rem; }
    header a { color: #fff; text-decoration: none; margin-left: 20px; }
    main { max-width: 1000px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; }
    h2 { margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
    tr:hover { background-color: #f9f9f9; }
    form { margin-bottom: 30px; }
    label { display: block; margin: 10px 0 5px; }
    input[type="text"], input[type="email"], input[type="password"], select { width: 95%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; }
    button { background-color: #198754; color: white; padding: 10px 18px; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background-color: #157347; }
    .error { color: red; margin-bottom: 10px; }
    .success { color: green; margin-bottom: 10px; }
    a.delete { color: #dc3545; text-decoration: none; }
    a.delete:hover { text-decoration: underline; }
</style>
</head>
<body>

<header>
    <div style="display: flex; align-items: center;">
        <a href="/admihub/Public/index.php">
            <img src="../Public/assets/images/admi-logo.png" alt="ADMI Logo" style="height: 60px; margin-right: 10px;">
        </a>
        <h1>ADMI Hub</h1>
    </div>
    <nav>
        <a href="/admihub/Public/index.php">Home</a>
        <a href="/admihub/Public/highlight.php">Highlights</a>
        <a href="/admihub/Public/profile.php">Profile</a>
        <a href="/admihub/admin/dashboard.php">Dashboard</a>
        <a href="/admihub/Public/logout.php">Logout</a>
    </nav>
</header>

<main>
<h1 style="text-align: center;">Manage Users</h1>

<h2>Add New User</h2>

<?php if (!empty($errors)): ?>
    <div class="error"><?= implode('<br>', $errors) ?></div>
<?php elseif ($success): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<form method="POST">
    <label>First Name:</label>
    <input type="text" name="first_name" required>

    <label>Last Name:</label>
    <input type="text" name="last_name" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Password:</label>
    <input type="password" name="password" required>

    <label>Role:</label>
    <select name="role" required>
        <option value="">Select role</option>
        <option value="admin">Admin</option>
        <option value="student">Student</option>
    </select>

    <button type="submit">Add User</button>
</form>

<h2>Existing Users</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= htmlspecialchars($user['id']) ?></td>
        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= htmlspecialchars($user['role']) ?></td>
        <td>
            <a href="delete_user.php?id=<?= $user['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</main>

</body>
</html>

