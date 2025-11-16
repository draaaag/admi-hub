<?php
session_start();
require_once '../includes/db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// Optional check to ensure request comes from approve_projects.php
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'approve_projects.php') === false) {
  echo "Unauthorized access. You can only delete from Approve Projects page.";
  exit;
}

// Validate project ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Invalid project ID.";
  exit;
}

$project_id = intval($_GET['id']);

// Confirm project belongs to the logged-in user
$stmt = $pdo->prepare("SELECT file_path FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch();

if (!$project) {
  echo "Project not found or you do not have permission.";
  exit;
}

// Delete the file (optional)
$file_path = "../uploads/" . $project['file_path'];
if (file_exists($file_path)) {
  unlink($file_path);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);

// Redirect back to approve_projects.php
header("Location:approve_projects.php");
exit;


