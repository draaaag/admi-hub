<?php
session_start();
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'] ?? null;

$stmt = $pdo->query("SELECT p.*, u.username, u.first_name, u.last_name, c.name AS course_name 
                     FROM projects p 
                     JOIN users u ON p.user_id = u.id 
                     JOIN courses c ON p.course_id = c.id 
                     WHERE p.is_highlight = 1 AND p.approved = 1 
                     ORDER BY p.id DESC");


$highlights = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Highlights - ADMI Hub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { 
      font-family: Arial, sans-serif; 
      margin: 0; 
      padding: 0; 
      background: #f9f9f9; 
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
      font-family: Arial, sans-serif;
    }

    .container { 
      padding: 20px; 
    }

    .highlight-grid {
      display: grid; 
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .highlight-card {
      background: white; 
      border-radius: 10px; 
      padding: 15px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .highlight-card h3 { 
      margin-top: 0; 
    }

    .highlight-card p { 
      margin: 6px 0; 
      font-size: 0.9em; 
    }

    .highlight-card a {
      display: inline-block; margin-top: 6px;
      color: #1987fc;  
      text-decoration: none;
    }

    .highlight-card a:hover {
       text-decoration: underline; 
      }

    .media-preview img, .media-preview video {
      width: 100%;
      max-height: 200px;
      object-fit: cover;
      cursor: pointer;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }

    .media-preview img:hover, .media-preview video:hover {
      transform: scale(1.02);
    }

    /* Modal */
    .media-modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background-color: rgba(0,0,0,0.9);
      justify-content: center;
      align-items: center;
    }

    .media-modal.active {
      display: flex;
    }

    .media-modal-content {
      max-width: 90%;
      max-height: 90%;
      border-radius: 0px;
      overflow: hidden;
    }

    .media-modal video, .media-modal img {
      width: 100%;
      height: auto;
      max-height: 90vh;
    }

    .media-modal-close {
      position: absolute;
      top: 20px; right: 30px;
      font-size: 2.5rem;
      color: white;
      cursor: pointer;
      z-index: 1001;
    }

    footer {
      margin-top: 60px;
      text-align: center;
      color: #777;
      padding: 20px;
    }
  </style>
</head>
<body>

<header style="background-color: #222; padding: 10px 20px; color: white; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center;">
       <a href="/admihub/Public/index.php">
         <img src="assets/images/admi-logo.png" alt="ADMI Logo" style="height: 60px; margin-right: 10px;">
       </a>
        <h1 style="margin-right:0px; font-size: 1.5rem; color: white;">ADMI Hub</h1>
    </div>
    <nav>
  <a href="/admihub/Public/index.php">Home</a> 
  <a href="/admihub/Public/highlight.php">Highlights</a>
  <a href="/admihub/Public/profile.php">Profile</a>

  <?php if (isset($_SESSION['user_id'])): ?>
    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'moderator'])): ?>
      <a href="/admihub/admin/dashboard.php">Dashboard</a>
    <?php endif; ?>
    <a href="/admihub/Public/logout.php">Logout</a>
  <?php else: ?>
    <a href="/admihub/Public/login.php">Login</a>
  <?php endif; ?>
</nav>

  </header>

<div class="container">
  <h2>Project Highlights</h2>
  <?php if (count($highlights) > 0): ?>
    <div class="highlight-grid">
      <?php foreach ($highlights as $proj): ?>
        <div class="highlight-card">
        <h4><?= htmlspecialchars($proj['title']) ?></h4>
        <p><strong>By:</strong> <a href="/admihub/Public/view_profile.php?id=<?= $proj['user_id'] ?>">
        <?= htmlspecialchars($proj['first_name'] . ' ' . $proj['last_name']) ?></a></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($proj['course_name']) ?></p>
        
        <div class="media-preview">
          <?php if ($proj['file_path'] && preg_match('/\.(jpg|jpeg|png|gif)$/i', $proj['file_path'])): ?>
            <img src="/admihub/uploads/<?= htmlspecialchars($proj['file_path']) ?>" alt="Project Image">
          <?php elseif ($proj['file_path'] && preg_match('/\.(mp4|webm|ogg)$/i', $proj['file_path'])): ?>
            <video src="/admihub/uploads/<?= htmlspecialchars($proj['file_path']) ?>"></video>
          <?php endif; ?>
        </div>

        <?php if (!empty($proj['description'])): ?>
          <p><strong>Description:</strong> <?= htmlspecialchars($proj['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($proj['file_path'])): ?>
          <a href="/admihub/uploads/<?= htmlspecialchars($proj['file_path']) ?>" download>Download Project</a><br>
        <?php endif; ?>
        
      </div>
    <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p>No highlights have been added yet.</p>
  <?php endif; ?>
</div>


<!-- Modal View -->
<div class="media-modal" id="mediaModal">
  <span class="media-modal-close" onclick="closeModal()">&times;</span>
  <div class="media-modal-content" id="modalContent"></div>
</div>

<footer>
  &copy; <?= date('Y') ?> ADMI Hub. All rights reserved.
</footer>

<script>
  const modal = document.getElementById("mediaModal");
  const modalContent = document.getElementById("modalContent");

  function openModal(media) {
    modalContent.innerHTML = "";
    const clone = media.cloneNode(true);
    clone.style.width = "95%";
    clone.style.height = "auto";
    clone.removeAttribute("controls");
    if (media.tagName === 'VIDEO') clone.setAttribute("controls", true);
    modalContent.appendChild(clone);
    modal.classList.add("active");
  }

  function closeModal() {
    modal.classList.remove("active");
    modalContent.innerHTML = "";
  }

  document.querySelectorAll('.media-preview img, .media-preview video').forEach(el => {
    el.addEventListener('click', () => openModal(el));
  });

  window.addEventListener('click', e => {
    if (e.target === modal) closeModal();
  });
</script>

</body>
</html>
