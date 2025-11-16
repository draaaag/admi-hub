<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$upload_dir = "../uploads/profile_pics/";

// Get current profile picture
$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_pic = $stmt->fetchColumn();

// Delete the old file if it exists and is not the default
if ($current_pic && $current_pic !== "../assets/images/default-user.png" && file_exists($upload_dir . $current_pic)) {
    unlink($upload_dir . $current_pic);
}

// Update the DB to set the default profile picture
$stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
$stmt->execute(["default.png", $user_id]);

header("Location: profile.php?success=Profile picture removed");
exit;
