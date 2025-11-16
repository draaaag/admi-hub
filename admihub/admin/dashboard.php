<?php
session_start();
require_once '../includes/db.php';

// Redirect if not admin or moderator
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'moderator'])) {
    header("Location: ../Public/login.php");
    exit;
}

// Map role to friendly name
$roleName = '';
if ($_SESSION['role'] === 'admin') {
    $roleName = 'Admin';
} elseif ($_SESSION['role'] === 'moderator') {
    $roleName = 'Moderator';
}

// Count data
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$projectCount = $pdo->query("SELECT COUNT(*) FROM projects WHERE approved = 1")->fetchColumn();
$pendingCount = $pdo->query("SELECT COUNT(*) FROM projects WHERE approved = 0")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f4f6f8;
      color: #333;
    }

    header {
      background-color: #222;
      color: #fff;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    
    header h1 { 
      font-family: Times, serif; 
      margin: 0; 
      font-size: 1.6rem; 
    }


    header a {
      color: #fff;
      text-decoration: none;
      margin-left: 20px;
    }

    h1 {
      text-align: center;
      margin-top: 40px;
      font-size: 2rem;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      padding: 40px 20px;
      max-width: 1000px;
      margin: auto;
    }

    .card {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      text-align: center;
      transition: transform 0.2s ease, box-shadow 0.3s ease;
      text-decoration: none;
      color: #333;
    }

    .card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      background-color: #f0f0f0;
    }

    .card h2 {
      margin-bottom: 10px;
      font-size: 1.5rem;
      color: #1a1a1a;
    }

    .card p {
      margin-bottom: 15px;
      font-size: 1rem;
      color: #555;
    }

    footer {
      text-align: center;
      color: #888;
      padding: 20px;
      font-size: 0.9rem;
      margin-top: 40px;
    }
  </style>
</head>
<body>

<header>
  <div style="display: flex; align-items: center;">
    <a href="../Public/index.php">
      <img src="../Public/assets/images/admi-logo.png" alt="ADMI Logo" style="height: 60px; margin-right: 10px;">
    </a>
    <h1 style="margin: 0; font-size: 1.5rem;">ADMI Hub</h1>
  </div>
  <nav>
    <a href="../Public/index.php">Home</a>
    <a href="../Public/highlight.php">Highlights</a>
    <a href="../Public/profile.php">Profile</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="../Public/logout.php">Logout</a>
  </nav>
</header>

<h1>Welcome, <?= htmlspecialchars($roleName) ?></h1>

<div class="dashboard-grid">
  <a href="manage_users.php" class="card">
    <h2>Users</h2>
    <p><?= $userCount ?> registered</p>
    <strong>Manage Users </strong>
  </a>

  <a href="approve_projects.php" class="card">
    <h2>Projects</h2>
    <p><?= $projectCount ?> approved<br><?= $pendingCount ?> pending</p>
    <strong>Review Projects </strong>
  </a>

  <a href="analytics.php" class="card">
    <h2>Analytics</h2>
    <p>Submission analytics</p>
    <strong>Tracking Review</strong>
  </a>
</div>

<footer>
  &copy; <?= date('Y') ?> ADMI Hub. Dashboard.
</footer>

</body>
</html>
