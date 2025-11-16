<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../Public/login.php");
    exit;
}

include '../includes/db.php';

// Check if 'id' is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_users.php");
    exit;
}

$userId = (int)$_GET['id'];

// Prevent admin from deleting themselves
if ($_SESSION['user_id'] == $userId) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: manage_users.php");
    exit;
}

// Delete user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$userId])) {
    $_SESSION['success'] = "User deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete user.";
}

header("Location: manage_users.php");
exit;
