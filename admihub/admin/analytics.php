<?php
session_start();
require_once '../includes/db.php';

if (!in_array($_SESSION['role'], ['admin', 'moderator'])) {
    die("Access denied.");
}

$stmt = $pdo->query("
    SELECT 
        project_title,
        course_name,
        submitter_name,
        approved_rejected_by,
        action,
        deleted_by,
        DATE_FORMAT(submitted_at, '%d/%m/%y %h:%i %p') AS submitted_at,
        DATE_FORMAT(deleted_at, '%d/%m/%y %h:%i %p') AS deleted_at
    FROM project_logs
    ORDER BY submitted_at DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Project Analytics</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0; padding: 0;
        background: #f4f4f4;
    }
    header {
        background-color: #222;
        color: #fff;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    header h1 { font-family: Times, serif; margin: 0; font-size: 1.6rem; }
    header a { color: #fff; text-decoration: none; margin-left: 20px; }
    table {
        width: 95%;
        margin: 20px auto;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    th { 
            background: #222; 
            color: white; 
        }
    tr:nth-child(even) { background: #f9f9f9; }
</style>
</head>
<body>

<header>
  <div style="display: flex; align-items: center;">
    <a href="/admihub/Public/index.php">
      <img src="../Public/assets/images/admi-logo.png" style="height: 60px; margin-right: 10px;">
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

<h2 style="text-align:center; margin-top:20px;">Project Submission Analytics</h2>

<table>
    <thead>
        <tr>
            <th>Project Title</th>
            <th>Course</th>
            <th>Submitted By</th>
            <th>Approved/Rejected By</th>
            <th>Action</th>
            <th>Deleted By</th>
            <th>Submitted At</th>
            <th>Deleted At</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($logs)): ?>
            <tr><td colspan="8" style="text-align:center;">No records found.</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['project_title']) ?></td>
                    <td><?= htmlspecialchars($log['course_name']) ?></td>
                    <td><?= htmlspecialchars($log['submitter_name']) ?></td>
                    <td><?= htmlspecialchars($log['approved_rejected_by']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($log['action'])) ?></td>
                    <td><?= htmlspecialchars($log['deleted_by'] ?? '') ?></td>
                    <td><?= htmlspecialchars($log['submitted_at']) ?></td>
                    <td><?= $log['deleted_at'] ? htmlspecialchars($log['deleted_at']) : '' ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
