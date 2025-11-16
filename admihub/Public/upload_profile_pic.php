<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'upload') {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_pic'];
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($ext, $allowed)) {
            $upload_dir = "../uploads/profile_pics/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $new_name = uniqid() . "_" . $filename;
            $target_path = $upload_dir . $new_name;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Remove old pic if exists
                $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old_pic = $stmt->fetchColumn();

                if ($old_pic && file_exists($upload_dir . $old_pic)) {
                    unlink($upload_dir . $old_pic);
                }

                // Save new filename
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->execute([$new_name, $user_id]);
            }
        }
    }
} elseif ($action === 'delete') {
    // Get old pic
    $stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $old_pic = $stmt->fetchColumn();

    if ($old_pic && file_exists("../uploads/profile_pics/" . $old_pic)) {
        unlink("../uploads/profile_pics/" . $old_pic);
    }

    // Reset profile_pic field
    $stmt = $pdo->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stmt->execute([$user_id]);
}

header("Location: profile.php?updated=1");
exit();

